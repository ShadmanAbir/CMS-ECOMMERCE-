<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;

class CartService
{
    public function __construct(
        protected PricingService $pricing
    ) {}

    public function getCart()
    {
        $user = auth()->user();

        if (!$user) {
            return collect([]);
        }

        return Cart::with(['product.images', 'variant', 'productStock'])
            ->where('user_id', $user->id)
            ->get();
    }

    public function addToCart($productId, $quantity = 1, $size = null, $color = null, $stockId = null, $variantId = null)
    {
        $product = Product::findOrFail($productId);

        if (!auth()->check()) {
            throw new \Exception('Please login to add items to cart');
        }

        // Determine price via PricingService
        $variant = null;
        if ($variantId) {
            $variant = ProductVariant::where('id', $variantId)
                ->where('product_id', $productId)
                ->where('is_active', true)
                ->first();
        }

        $price = $this->pricing->getItemPrice($product, $variant);

        $data = [
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'product_stock_id' => $stockId,
            'quantity' => $quantity,
            'size' => $size,
            'color' => $color,
            'user_id' => auth()->id(),
            'price' => $price,
        ];

        // Check for existing cart item with same product + variant + size + color
        $cart = Cart::where([
            'user_id' => auth()->id(),
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'product_stock_id' => $stockId,
            'size' => $size,
            'color' => $color,
        ])->first();

        if ($cart) {
            $cart->increment('quantity', $quantity);
            return $cart;
        }

        return Cart::create($data);
    }

    public function updateCart($cartId, $quantity)
    {
        $cart = Cart::where('id', $cartId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $cart->update(['quantity' => $quantity]);
        return $cart;
    }

    public function removeFromCart($cartId)
    {
        $cart = Cart::where('id', $cartId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $cart->delete();
        return true;
    }

    public function calculateTotals($cartItems, ?Coupon $coupon = null)
    {
        return $this->pricing->calculateTotals($cartItems, $coupon);
    }

    public function getCartCount(): int
    {
        $user = auth()->user();
        if (!$user) {
            return 0;
        }

        return Cart::where('user_id', $user->id)->sum('quantity');
    }
}
