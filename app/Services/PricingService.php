<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;

class PricingService
{
    public function __construct(
        protected CouponService $coupons
    ) {}

    /**
     * Calculate the effective (discounted) price for a product.
     */
    public function getProductPrice(Product $product): float
    {
        $price = (float) $product->price;
        $discount = (float) ($product->discount ?? 0);

        if ($discount > 0) {
            return round($price - ($price * $discount / 100), 2);
        }

        return $price;
    }

    /**
     * Calculate the effective (discounted) price for a variant.
     * Falls back to product price if variant has no price set.
     */
    public function getVariantPrice(ProductVariant $variant): float
    {
        $basePrice = $variant->price ?? $variant->product->price;
        $discount = (float) ($variant->product->discount ?? 0);

        if ($discount > 0) {
            return round($basePrice - ($basePrice * $discount / 100), 2);
        }

        return (float) $basePrice;
    }

    /**
     * Get the effective price for a cart item (variant-aware).
     */
    public function getItemPrice(Product $product, ?ProductVariant $variant = null): float
    {
        if ($variant) {
            return $this->getVariantPrice($variant);
        }

        return $this->getProductPrice($product);
    }

    /**
     * Calculate order totals from a collection of cart items.
     * Tax rate is read from site config (theme('pricing.tax_rate')).
     * An optional coupon applies an additional order-level discount.
     */
    public function calculateTotals($cartItems, ?Coupon $coupon = null): array
    {
        $subtotal = 0;

        foreach ($cartItems as $item) {
            $itemPrice = $this->getItemPrice($item->product, $item->variant);
            $subtotal += $itemPrice * $item->quantity;
        }

        $discountAmount = $coupon
            ? $this->coupons->calculateDiscount($coupon, $subtotal)
            : 0;

        $afterDiscount = $subtotal - $discountAmount;
        $taxRate = $this->getTaxRate();
        $taxAmount = round($afterDiscount * $taxRate);
        $shipping = $this->getShippingCost($afterDiscount);
        $payable = round($afterDiscount + $taxAmount + $shipping);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'vat_amount' => $taxAmount,
            'shipping_amount' => $shipping,
            'payable_amount' => $payable,
        ];
    }

    /**
     * Get the configured tax rate (decimal, e.g. 0.05 = 5%).
     * Read from site config: theme('pricing.tax_rate')
     */
    public function getTaxRate(): float
    {
        return (float) theme('pricing.tax_rate', 0);
    }

    /**
     * Get shipping cost. Can be extended with rules later.
     */
    public function getShippingCost(float $subtotal): float
    {
        $freeThreshold = theme('pricing.free_shipping_threshold', null);

        if ($freeThreshold && $subtotal >= $freeThreshold) {
            return 0;
        }

        return (float) theme('pricing.shipping_cost', 0);
    }
}
