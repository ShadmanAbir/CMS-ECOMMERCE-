<?php

use App\Models\Order;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Search Admin',
        'email' => 'searchadmin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->customer = User::create([
        'name' => 'Jane Buyer',
        'email' => 'jane@buyer.com',
        'password' => bcrypt('password'),
    ]);
});

function makeAdminOrder(?User $user, string $orderNumber, string $status = 'pending'): Order
{
    return Order::create([
        'user_id' => $user?->id,
        'order_number' => $orderNumber,
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

test('admin can search orders by order number', function () {
    makeAdminOrder($this->customer, 'ORD-ALPHA-100');
    makeAdminOrder($this->customer, 'ORD-BETA-200');

    $this->actingAs($this->admin)
        ->get(route('admin.orders.index', ['q' => 'ALPHA']))
        ->assertOk()
        ->assertSee('ORD-ALPHA-100')
        ->assertDontSee('ORD-BETA-200');
});

test('admin can search orders by customer name', function () {
    makeAdminOrder($this->customer, 'ORD-100');

    $other = User::create(['name' => 'Bob Shopper', 'email' => 'bob@test.com', 'password' => bcrypt('x')]);
    makeAdminOrder($other, 'ORD-200');

    $this->actingAs($this->admin)
        ->get(route('admin.orders.index', ['q' => 'Jane']))
        ->assertOk()
        ->assertSee('ORD-100')
        ->assertDontSee('ORD-200');
});

test('admin can search orders by customer email', function () {
    makeAdminOrder($this->customer, 'ORD-100');

    $other = User::create(['name' => 'Bob Shopper', 'email' => 'bob@test.com', 'password' => bcrypt('x')]);
    makeAdminOrder($other, 'ORD-200');

    $this->actingAs($this->admin)
        ->get(route('admin.orders.index', ['q' => 'jane@buyer']))
        ->assertOk()
        ->assertSee('ORD-100')
        ->assertDontSee('ORD-200');
});

test('admin search with no results shows the empty state with the query', function () {
    makeAdminOrder($this->customer, 'ORD-100');

    $this->actingAs($this->admin)
        ->get(route('admin.orders.index', ['q' => 'NOMATCH']))
        ->assertOk()
        ->assertSee('No orders found for "NOMATCH"');
});

test('status filter tabs preserve the search query', function () {
    makeAdminOrder($this->customer, 'ORD-ALPHA-100', 'shipped');

    $this->actingAs($this->admin)
        ->get(route('admin.orders.index', ['q' => 'ALPHA']))
        ->assertOk()
        ->assertSee('q=ALPHA');
});
