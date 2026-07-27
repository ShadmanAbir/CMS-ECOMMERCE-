<?php

namespace App\Services;

use App\Exceptions\CouponException;
use App\Models\Coupon;
use App\Models\Order;

class CouponService
{
    /**
     * Session key holding the currently applied coupon code.
     */
    protected string $sessionKey = 'coupon_code';

    /**
     * Normalize a raw coupon code input.
     */
    public function normalize(string $code): string
    {
        return strtoupper(trim($code));
    }

    /**
     * Validate a coupon against the current subtotal and (optionally) user.
     * Returns the coupon on success, throws CouponException otherwise.
     */
    public function validate(string $code, float $subtotal, ?int $userId = null): Coupon
    {
        $coupon = Coupon::where('code', $this->normalize($code))->first();

        if (!$coupon) {
            throw CouponException::notFound();
        }

        if (!$coupon->is_active) {
            throw CouponException::inactive();
        }

        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            throw CouponException::notStarted();
        }

        if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
            throw CouponException::expired();
        }

        if ($coupon->min_order_amount !== null && $subtotal < (float) $coupon->min_order_amount) {
            throw CouponException::minOrder((float) $coupon->min_order_amount);
        }

        if ($coupon->usage_limit !== null && $coupon->usage_count >= $coupon->usage_limit) {
            throw CouponException::limitReached();
        }

        if ($userId && $coupon->per_user_limit !== null) {
            $userUsage = Order::where('coupon_code', $coupon->code)
                ->where('user_id', $userId)
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($userUsage >= $coupon->per_user_limit) {
                throw CouponException::userLimitReached();
            }
        }

        return $coupon;
    }

    /**
     * Calculate the discount amount a coupon yields for a given subtotal.
     * Never exceeds the subtotal itself.
     */
    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        if ($coupon->type === 'percentage') {
            $discount = $subtotal * ((float) $coupon->value / 100);

            if ($coupon->max_discount_amount !== null) {
                $discount = min($discount, (float) $coupon->max_discount_amount);
            }
        } else {
            $discount = (float) $coupon->value;
        }

        return round(min($discount, $subtotal), 2);
    }

    /**
     * Get the currently applied coupon code from the session.
     */
    public function getAppliedCode(): ?string
    {
        return session($this->sessionKey);
    }

    /**
     * Get the currently applied Coupon model (if any).
     */
    public function getAppliedCoupon(): ?Coupon
    {
        $code = $this->getAppliedCode();

        return $code ? Coupon::where('code', $code)->first() : null;
    }

    /**
     * Validate and persist a coupon code into the session.
     */
    public function apply(string $code, float $subtotal, ?int $userId = null): Coupon
    {
        $coupon = $this->validate($code, $subtotal, $userId);

        session([$this->sessionKey => $coupon->code]);

        return $coupon;
    }

    /**
     * Remove any applied coupon from the session.
     */
    public function remove(): void
    {
        session()->forget($this->sessionKey);
    }

    /**
     * Increment the coupon's usage counter (called after a successful order).
     */
    public function recordUsage(Coupon $coupon): void
    {
        $coupon->increment('usage_count');
    }
}
