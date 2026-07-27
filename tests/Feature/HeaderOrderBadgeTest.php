<?php

use App\Models\Order;
use App\Models\User;

function makeHeaderOrder(User $user): Order
{
    return Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-HDR-' . uniqid(),
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => 'cod',
        'subtotal' => 500,
        'discount_amount' => 0,
        'vat_amount' => 0,
        'shipping_amount' => 0,
        'total_amount' => 500,
    ]);
}

test('the header shows an order count badge for an authenticated customer with orders', function () {
    $user = User::create([
        'name' => 'Badge Customer',
        'email' => 'badgecustomer@test.com',
        'password' => bcrypt('password'),
    ]);
    makeHeaderOrder($user);
    makeHeaderOrder($user);

    $this->actingAs($user);

    $html = view('layouts.header', [
        'cartCount' => 0,
        'wishlistCount' => 0,
        'orderCount' => $user->orders()->count(),
        'navCategories' => collect(),
    ])->render();

    expect($html)->toContain('My Orders');
    expect($html)->toContain('>2<');
});

test('the header hides the order badge when the customer has no orders', function () {
    $user = User::create([
        'name' => 'Empty Customer',
        'email' => 'emptycustomer@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($user);

    $html = view('layouts.header', [
        'cartCount' => 0,
        'wishlistCount' => 0,
        'orderCount' => $user->orders()->count(),
        'navCategories' => collect(),
    ])->render();

    expect($html)->toContain('My Orders');
    expect($html)->not->toContain('>0<');
});

test('the header composer provides the authenticated user order count', function () {
    $user = User::create([
        'name' => 'Composer Customer',
        'email' => 'composercustomer@test.com',
        'password' => bcrypt('password'),
    ]);
    makeHeaderOrder($user);
    makeHeaderOrder($user);
    makeHeaderOrder($user);

    $this->actingAs($user);

    $view = view('layouts.header', ['navCategories' => collect()]);
    app(\App\Http\ViewComposers\HeaderComposer::class)->compose($view);

    expect($view->getData()['orderCount'])->toBe(3);
});
