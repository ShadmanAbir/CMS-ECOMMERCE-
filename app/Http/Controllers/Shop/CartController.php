<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\CouponService;
use App\Exceptions\CouponException;
use App\ViewModels\CartViewModel;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CouponService $couponService
    ) {}

    /**
     * Display cart
     */
    public function index()
    {
        $cartItems = $this->cartService->getCart();
        $coupon = $this->couponService->getAppliedCoupon();
        $totals = $this->cartService->calculateTotals($cartItems, $coupon);

        $viewModel = CartViewModel::fromCart($cartItems, $totals, $coupon?->code);

        return view('shop.cart', compact('viewModel'));
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'size' => 'nullable|string',
            'color' => 'nullable|string',
            'product_stock_id' => 'nullable|exists:product_stocks,id',
        ]);

        try {
            $cart = $this->cartService->addToCart(
                $validated['product_id'],
                $validated['quantity'],
                $validated['size'] ?? null,
                $validated['color'] ?? null,
                $validated['product_stock_id'] ?? null,
                $validated['product_variant_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart',
                'cart_count' => $this->cartService->getCartCount()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update cart item
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $this->cartService->updateCart($id, $validated['quantity']);

            $cartItems = $this->cartService->getCart();
            $totals = $this->cartService->calculateTotals($cartItems, $this->couponService->getAppliedCoupon());

            return response()->json([
                'success' => true,
                'message' => 'Cart updated',
                'totals' => $totals,
                'cart_count' => $cartItems->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove item from cart
     */
    public function remove($id)
    {
        try {
            $this->cartService->removeFromCart($id);

            $cartItems = $this->cartService->getCart();
            $totals = $this->cartService->calculateTotals($cartItems, $this->couponService->getAppliedCoupon());

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'totals' => $totals,
                'cart_count' => $cartItems->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Apply a coupon code to the cart.
     */
    public function applyCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
        ]);

        try {
            $cartItems = $this->cartService->getCart();
            $baseTotals = $this->cartService->calculateTotals($cartItems);

            $coupon = $this->couponService->apply(
                $validated['code'],
                (float) $baseTotals['subtotal'],
                auth()->id()
            );

            $totals = $this->cartService->calculateTotals($cartItems, $coupon);

            return response()->json([
                'success' => true,
                'message' => "Coupon {$coupon->code} applied!",
                'coupon_code' => $coupon->code,
                'totals' => $totals,
            ]);
        } catch (CouponException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the applied coupon from the cart.
     */
    public function removeCoupon()
    {
        $this->couponService->remove();

        $cartItems = $this->cartService->getCart();
        $totals = $this->cartService->calculateTotals($cartItems);

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed',
            'coupon_code' => null,
            'totals' => $totals,
        ]);
    }
}
