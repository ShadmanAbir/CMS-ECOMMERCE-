<?php

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Services\CouponService;
use App\Exceptions\CouponException;

beforeEach(function () {
    $this->service = app(CouponService::class);
});

function makeCoupon(array $overrides = []): Coupon
{
    return Coupon::create(array_merge([
        'code' => 'TEST' . uniqid(),
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ], $overrides));
}

// ─── Discount Calculation ────────────────────────────────

test('calculates percentage discount', function () {
    $coupon = makeCoupon(['type' => 'percentage', 'value' => 10]);
    expect($this->service->calculateDiscount($coupon, 1000))->toBe(100.0);
});

test('caps percentage discount at max amount', function () {
    $coupon = makeCoupon(['type' => 'percentage', 'value' => 50, 'max_discount_amount' => 200]);
    expect($this->service->calculateDiscount($coupon, 1000))->toBe(200.0);
});

test('calculates fixed discount', function () {
    $coupon = makeCoupon(['type' => 'fixed', 'value' => 150]);
    expect($this->service->calculateDiscount($coupon, 1000))->toBe(150.0);
});

test('discount never exceeds subtotal', function () {
    $coupon = makeCoupon(['type' => 'fixed', 'value' => 500]);
    expect($this->service->calculateDiscount($coupon, 300))->toBe(300.0);
});

// ─── Validation ──────────────────────────────────────────

test('validate rejects unknown code', function () {
    $this->service->validate('NOPE', 1000);
})->throws(CouponException::class);

test('validate rejects inactive coupon', function () {
    $coupon = makeCoupon(['is_active' => false]);
    $this->service->validate($coupon->code, 1000);
})->throws(CouponException::class);

test('validate rejects expired coupon', function () {
    $coupon = makeCoupon(['expires_at' => now()->subDay()]);
    $this->service->validate($coupon->code, 1000);
})->throws(CouponException::class);

test('validate rejects coupon that has not started', function () {
    $coupon = makeCoupon(['starts_at' => now()->addDay()]);
    $this->service->validate($coupon->code, 1000);
})->throws(CouponException::class);

test('validate rejects below minimum order', function () {
    $coupon = makeCoupon(['min_order_amount' => 500]);
    $this->service->validate($coupon->code, 300);
})->throws(CouponException::class);

test('validate rejects when usage limit reached', function () {
    $coupon = makeCoupon(['usage_limit' => 5, 'usage_count' => 5]);
    $this->service->validate($coupon->code, 1000);
})->throws(CouponException::class);

test('validate rejects when per-user limit reached', function () {
    $user = User::create(['name' => 'U', 'email' => 'u@t.com', 'password' => bcrypt('p')]);
    $coupon = makeCoupon(['per_user_limit' => 1]);

    Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-CPN-1',
        'coupon_code' => $coupon->code,
        'status' => 'delivered',
        'subtotal' => 100,
        'total_amount' => 100,
    ]);

    $this->service->validate($coupon->code, 1000, $user->id);
})->throws(CouponException::class);

test('validate accepts a valid coupon and normalizes case', function () {
    $coupon = makeCoupon(['code' => 'VALID10']);
    $result = $this->service->validate('valid10', 1000);
    expect($result->id)->toBe($coupon->id);
});

// ─── Session ─────────────────────────────────────────────

test('apply stores coupon code in session', function () {
    $coupon = makeCoupon(['code' => 'SAVE10']);
    $this->service->apply('SAVE10', 1000);

    expect(session('coupon_code'))->toBe('SAVE10')
        ->and($this->service->getAppliedCoupon()->id)->toBe($coupon->id);
});

test('remove clears the session coupon', function () {
    makeCoupon(['code' => 'SAVE10']);
    $this->service->apply('SAVE10', 1000);
    $this->service->remove();

    expect($this->service->getAppliedCode())->toBeNull();
});

test('record usage increments the counter', function () {
    $coupon = makeCoupon(['usage_count' => 0]);
    $this->service->recordUsage($coupon);

    expect($coupon->fresh()->usage_count)->toBe(1);
});
