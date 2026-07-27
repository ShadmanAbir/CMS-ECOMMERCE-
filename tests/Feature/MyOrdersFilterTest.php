<?php

use App\Models\Order;
use App\Models\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Filter Customer',
        'email' => 'filtercustomer@test.com',
        'password' => bcrypt('password'),
    ]);
});

function makeMyOrder(User $user, string $status): Order
{
    return Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-MOF-' . strtoupper($status) . '-' . uniqid(),
        'status' => $status,
        'payment_status' => 'pending',
        'payment_method' => 'cod',
        'subtotal' => 500,
        'discount_amount' => 0,
        'vat_amount' => 0,
        'shipping_amount' => 0,
        'total_amount' => 500,
    ]);
}

test('my orders page renders the status filter tabs with counts', function () {
    makeMyOrder($this->user, 'delivered');
    makeMyOrder($this->user, 'shipped');

    $this->actingAs($this->user)
        ->get(route('user.orders'))
        ->assertOk()
        ->assertSee('All (2)')
        ->assertSee('Delivered (1)')
        ->assertSee('Shipped (1)');
});

test('filtering by status shows only matching orders', function () {
    $delivered = makeMyOrder($this->user, 'delivered');
    $cancelled = makeMyOrder($this->user, 'cancelled');

    $this->actingAs($this->user)
        ->get(route('user.orders', ['status' => 'delivered']))
        ->assertOk()
        ->assertSee($delivered->order_number)
        ->assertDontSee($cancelled->order_number);
});

test('filtering by a status with no orders shows the filtered empty state', function () {
    makeMyOrder($this->user, 'delivered');

    $this->actingAs($this->user)
        ->get(route('user.orders', ['status' => 'cancelled']))
        ->assertOk()
        ->assertSee('No cancelled orders')
        ->assertSee('View All Orders');
});

test('a customer only sees their own orders in the counts', function () {
    $other = User::create([
        'name' => 'Someone Else',
        'email' => 'someoneelse@test.com',
        'password' => bcrypt('password'),
    ]);

    makeMyOrder($this->user, 'delivered');
    makeMyOrder($other, 'delivered');
    makeMyOrder($other, 'shipped');

    $this->actingAs($this->user)
        ->get(route('user.orders'))
        ->assertOk()
        ->assertSee('All (1)');
});
