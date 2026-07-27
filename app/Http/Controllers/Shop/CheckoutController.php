<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCheckoutRequest;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\InventoryService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Exceptions\CouponException;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService,
        protected PaymentService $paymentService,
        protected InventoryService $inventory,
        protected CouponService $couponService
    ) {}

    /**
     * Display checkout page
     */
    public function index()
    {
        // User must be logged in to checkout
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to proceed with checkout');
        }
        
        $cartItems = $this->cartService->getCart();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty');
        }

        $coupon = $this->couponService->getAppliedCoupon();
        $totals = $this->cartService->calculateTotals($cartItems, $coupon);
        
        $addresses = auth()->user()->addresses()->orderBy('is_default', 'desc')->get();
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

        return view('shop.checkout', compact('cartItems', 'totals', 'addresses', 'defaultAddress', 'coupon'));
    }

    /**
     * Process checkout
     */
    public function store(StoreCheckoutRequest $request)
    {
        $validated = $request->validated();

        try {
            // Get or create address
            $address = null;
            if (!empty($validated['address_id'])) {
                $address = CustomerAddress::where('id', $validated['address_id'])
                    ->where('user_id', auth()->id())
                    ->firstOrFail();
            } else {
                $address = CustomerAddress::create([
                    'user_id' => auth()->id(),
                    'address' => $validated['address'],
                    'city' => $validated['city'],
                    'division' => $validated['division'],
                    'district' => $validated['district'],
                    'thana' => $validated['thana'],
                    'postal_code' => $validated['postal_code'] ?? null,
                    'country' => 'Bangladesh',
                    'is_default' => true,
                ]);
            }

            // Get cart items
            $cartItems = $this->cartService->getCart();
            
            // Validate stock availability
            $this->inventory->validateCartStock($cartItems);

            // Re-validate any applied coupon against the live subtotal.
            $coupon = $this->couponService->getAppliedCoupon();
            if ($coupon) {
                try {
                    $baseTotals = $this->cartService->calculateTotals($cartItems);
                    $coupon = $this->couponService->validate($coupon->code, (float) $baseTotals['subtotal'], auth()->id());
                } catch (CouponException $e) {
                    $this->couponService->remove();
                    $coupon = null;
                }
            }

            $totals = $this->cartService->calculateTotals($cartItems, $coupon);

            // Create order
            $orderData = [
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'shipping_address_id' => $address->id,
                'coupon_code' => $coupon?->code,
            ];

            $order = $this->orderService->createOrder($orderData, $cartItems, $totals);

            // The coupon has been consumed by this order.
            $this->couponService->remove();

            // Process payment
            $paymentResult = $this->paymentService->processPayment($order, $validated['payment_method']);

            // If payment requires redirect (bKash, Nagad, SSLCommerz)
            if (isset($paymentResult['url'])) {
                return response()->json([
                    'success' => true,
                    'redirect' => $paymentResult['url'],
                    'order_number' => $order->order_number,
                ]);
            }

            // For COD, redirect to success page
            return response()->json([
                'success' => true,
                'redirect' => route('payment.success', $order->order_number),
                'order_number' => $order->order_number,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Handle payment gateway callback
     */
    public function paymentCallback(Request $request, $method)
    {
        try {
            $result = $this->paymentService->handlePaymentCallback($request->all(), $method);

            if ($result['success']) {
                return redirect()->route('payment.success', $request->get('order_number'))
                    ->with('success', $result['message'] ?? 'Payment successful');
            }

            return redirect()->route('payment.cancel', $request->get('order_number'))
                ->with('error', $result['error'] ?? 'Payment failed');

        } catch (\Exception $e) {
            return redirect()->route('home')
                ->with('error', 'Payment processing error: ' . $e->getMessage());
        }
    }

    /**
     * Payment success page
     */
    public function paymentSuccess($orderNumber)
    {
        $order = \App\Models\Order::where('order_number', $orderNumber)
            ->with(['items.product.images', 'payment'])
            ->firstOrFail();

        return view('shop.payment-success', compact('order'));
    }

    /**
     * Payment cancel page
     */
    public function paymentCancel($orderNumber)
    {
        $order = \App\Models\Order::where('order_number', $orderNumber)->firstOrFail();

        return view('shop.payment-cancel', compact('order'));
    }
}
