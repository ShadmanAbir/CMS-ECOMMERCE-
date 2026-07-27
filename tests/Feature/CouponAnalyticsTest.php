<?php

use App\Models\Order;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Coupon Analytics Admin',
        'email' => 'couponanalytics@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->customer = User::create([
        'name' => 'Coupon Analytics Customer',
        'email' => 'couponcustomer@test.com',
        'password' => bcrypt('password'),
        'role' => 'customer',
    ]);
});

function placeCouponOrder(User $user, ?string $code, float $discount): Order
{
    return Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-CA-' . uniqid(),
        'status' => 'delivered',
        'payment_method' => 'cod',
        'subtotal' => 1000,
        'discount_amount' => $discount,
        'total_amount' => 1000 - $discount,
        'coupon_code' => $code,
    ]);
}

test('dashboard renders the coupon performance section', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('COUPON', false)
        ->assertSee('PERFORMANCE', false)
        ->assertSee('Total Redemptions');
});

test('dashboard lists a redeemed coupon in the top coupons panel', function () {
    placeCouponOrder($this->customer, 'SUMMER25', 250);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('SUMMER25')
        ->assertSee('Most Used Coupons');
});
