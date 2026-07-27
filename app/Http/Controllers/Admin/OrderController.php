<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Exceptions\OrderStatusException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * List orders with status filtering, search and pagination.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('q');
        $from = $request->query('from');
        $to = $request->query('to');

        $query = Order::with(['user', 'items'])
            ->withCount('items')
            ->latest();

        if ($status && array_key_exists($status, OrderService::STATUS_TRANSITIONS)) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('user', fn ($uq) => $uq->where('email', 'like', "%{$search}%"));
            });
        }

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $orders = $query->paginate(20)->withQueryString();

        // Counts per status for the filter tabs.
        $statusCounts = Order::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.orders.index', compact('orders', 'statusCounts', 'status', 'search', 'from', 'to'));
    }

    /**
     * Show a single order with its items, customer, shipping and payment details.
     */
    public function show(Order $order)
    {
        $order->load(['items.product.images', 'items.variant', 'user', 'shippingAddress', 'payment', 'statusHistory']);

        $allowedTransitions = $this->orderService->getAllowedTransitions($order);

        return view('admin.orders.show', compact('order', 'allowedTransitions'));
    }

    /**
     * Transition an order to a new status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', array_keys(OrderService::STATUS_TRANSITIONS))],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->orderService->updateOrderStatus($order, $validated['status'], $validated['note'] ?? null);

            return redirect()
                ->route('admin.orders.show', $order)
                ->with('success', "Order {$order->order_number} marked as {$validated['status']}.");
        } catch (OrderStatusException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::error("Failed to update status for order {$order->order_number}: " . $e->getMessage());
            return back()->withErrors(['status' => 'Failed to update order status.']);
        }
    }
}
