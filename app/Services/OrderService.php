<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Exceptions\OrderCancellationException;
use App\Exceptions\OrderStatusException;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Allowed order status transitions (from => [to, ...]).
     * Terminal states (delivered, cancelled) have no outgoing transitions.
     */
    public const STATUS_TRANSITIONS = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    public function __construct(
        protected CartService $cartService,
        protected PricingService $pricing,
        protected InventoryService $inventory,
        protected CouponService $coupons
    ) {}

    public function createOrder($orderData, $cartItems, $totals)
    {
        if ($cartItems->isEmpty()) {
            throw new \Exception('Your cart is empty');
        }

        $order = DB::transaction(function () use ($orderData, $cartItems, $totals) {
            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => 'ORD-' . time() . '-' . auth()->id(),
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'] ?? 0,
                'vat_amount' => $totals['vat_amount'] ?? 0,
                'shipping_amount' => $totals['shipping_amount'] ?? 0,
                'total_amount' => $totals['payable_amount'],
                'payment_method' => $orderData['payment_method'],
                'payment_status' => 'pending',
                'status' => 'pending',
                'shipping_address_id' => $orderData['shipping_address_id'] ?? null,
                'coupon_code' => $orderData['coupon_code'] ?? null,
                'notes' => $orderData['notes'] ?? null,
            ]);

            // Seed the tracking timeline with the initial state.
            $this->recordStatusChange($order, 'pending', 'Order placed');

            // Create order items
            foreach ($cartItems as $cartItem) {
                $price = $this->pricing->getItemPrice($cartItem->product, $cartItem->variant);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_variant_id' => $cartItem->product_variant_id ?? null,
                    'product_stock_id' => $cartItem->product_stock_id ?? null,
                    'quantity' => $cartItem->quantity,
                    'size' => $cartItem->size,
                    'color' => $cartItem->color,
                    'price' => $price,
                    'subtotal' => $price * $cartItem->quantity,
                    'total' => $price * $cartItem->quantity,
                ]);

                // Update stock
                $this->inventory->decrementStock($cartItem);
            }

            // Clear cart
            if (auth()->check()) {
                Cart::where('user_id', auth()->id())->delete();
            }

            return $order;
        });

        // Dispatch event after transaction commits
        event(new OrderPlaced($order));

        // Record coupon usage now that the order is committed.
        if (!empty($orderData['coupon_code'])) {
            $coupon = Coupon::where('code', $orderData['coupon_code'])->first();
            if ($coupon) {
                $this->coupons->recordUsage($coupon);
            }
        }

        return $order;
    }

    public function getUserOrders($userId, ?string $status = null, ?string $search = null)
    {
        $search = trim((string) $search);

        return Order::with(['items.product.images', 'items.variant', 'payment', 'shippingAddress'])
            ->where('user_id', $userId)
            ->when($status && array_key_exists($status, self::STATUS_TRANSITIONS), fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where('order_number', 'like', '%' . $search . '%'))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Per-status order counts for a customer, plus an 'all' total.
     * Used to render the "my orders" filter tabs.
     *
     * @return array<string,int>
     */
    public function userOrderStatusCounts($userId): array
    {
        $counts = Order::where('user_id', $userId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $result = ['all' => (int) $counts->sum()];

        foreach (array_keys(self::STATUS_TRANSITIONS) as $status) {
            $result[$status] = (int) ($counts[$status] ?? 0);
        }

        return $result;
    }

    public function getOrderById($orderId)
    {
        return Order::with(['items.product.images', 'items.variant', 'payment', 'shippingAddress', 'user'])
            ->findOrFail($orderId);
    }

    /**
     * Get the list of statuses an order can transition to from its current state.
     */
    public function getAllowedTransitions(Order $order): array
    {
        return self::STATUS_TRANSITIONS[$order->status] ?? [];
    }

    /**
     * Update an order's status (admin action).
     * Enforces the transition map and restores stock on cancellation.
     */
    public function updateOrderStatus(Order $order, string $newStatus, ?string $note = null): Order
    {
        if (!array_key_exists($newStatus, self::STATUS_TRANSITIONS)) {
            throw OrderStatusException::unknownStatus($newStatus);
        }

        $allowed = self::STATUS_TRANSITIONS[$order->status] ?? [];
        if (!in_array($newStatus, $allowed, true)) {
            throw OrderStatusException::invalidTransition($order->status, $newStatus);
        }

        $updated = DB::transaction(function () use ($order, $newStatus, $note) {
            // Cancelling releases the reserved stock back to inventory.
            if ($newStatus === 'cancelled') {
                $this->inventory->restoreOrderStock($order);
                $order->payment_status = $order->payment_status === 'paid' ? 'refunded' : $order->payment_status;
            }

            $order->status = $newStatus;
            $order->save();

            $this->recordStatusChange($order, $newStatus, $note);

            return $order->fresh();
        });

        // Notify the customer of the new status once the change is committed.
        event(new OrderStatusChanged($updated, $newStatus, $note));

        return $updated;
    }

    /**
     * Cancel an order and restore stock
     */
    public function cancelOrder(Order $order, int $userId)
    {
        // Verify ownership
        if ($order->user_id !== $userId) {
            throw OrderCancellationException::unauthorized();
        }

        // Only pending/processing orders can be cancelled
        if (!in_array($order->status, ['pending', 'processing'])) {
            throw OrderCancellationException::invalidStatus($order->status);
        }

        return DB::transaction(function () use ($order) {
            // Restore stock for each item
            $this->inventory->restoreOrderStock($order);

            // Update order status
            $order->update([
                'status' => 'cancelled',
                'payment_status' => $order->payment_status === 'paid' ? 'refunded' : $order->payment_status,
            ]);

            $this->recordStatusChange($order, 'cancelled', 'Cancelled by customer');

            return $order->fresh();
        });
    }

    /**
     * Append an entry to an order's status tracking timeline.
     */
    protected function recordStatusChange(Order $order, string $status, ?string $note = null): void
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $status,
            'note' => $note,
        ]);
    }
}
