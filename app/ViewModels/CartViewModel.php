<?php

namespace App\ViewModels;

use App\Models\Cart;
use Illuminate\Support\Collection;

class CartViewModel
{
    public function __construct(
        public readonly Collection|array $cartItems,
        public readonly float|string $subtotal,
        public readonly float|string $discountAmount,
        public readonly float|string $vatAmount,
        public readonly float|string $shippingAmount,
        public readonly float|string $payableAmount,
        public readonly int $cartCount,
        public readonly ?string $couponCode = null
    ) {}

    public static function fromCart(Collection $cartItems, array $totals, ?string $couponCode = null): self
    {
        return new self(
            $cartItems,
            $totals['subtotal'],
            $totals['discount_amount'],
            $totals['vat_amount'],
            $totals['shipping_amount'] ?? 0,
            $totals['payable_amount'],
            $cartItems->count(),
            $couponCode
        );
    }
}
