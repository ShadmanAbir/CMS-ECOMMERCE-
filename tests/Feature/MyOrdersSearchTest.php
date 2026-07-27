<?php

use App\Models\Order;
use App\Models\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Search Customer',
        'email' => 'searchcustomer@test.com',
        'password' => bcrypt('password'),
    ]);
});

function makeSearchOrder(User $user, string $orderNumber, string $status = 'pending'): Order
{
    return Order::create([
        'user_id' => $user->id,
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

test('my orders page renders the order number search box', function () {
    $this->actingAs($this->user)
        ->get(route('user.orders'))
        ->assertOk()
        ->assertSee('Search by order number');
});

test('searching by order number shows only matching orders', function () {
    makeSearchOrder($this->user, 'ORD-SEARCH-111');
    makeSearchOrder($this->user, 'ORD-OTHER-222');

    $this->actingAs($this->user)
        ->get(route('user.orders', ['search' => 'SEARCH']))
        ->assertOk()
        ->assertSee('ORD-SEARCH-111')
        ->assertDontSee('ORD-OTHER-222');
});

test('a search with no matches shows the search empty state', function () {
    makeSearchOrder($this->user, 'ORD-SEARCH-111');

    $this->actingAs($this->user)
        ->get(route('user.orders', ['search' => 'NOMATCH']))
        ->assertOk()
        ->assertSee('No matching orders')
        ->assertSee('Clear Search');
});

test('search combines with the status filter', function () {
    makeSearchOrder($this->user, 'ORD-SEARCH-111', 'delivered');
    makeSearchOrder($this->user, 'ORD-SEARCH-222', 'cancelled');

    $this->actingAs($this->user)
        ->get(route('user.orders', ['search' => 'SEARCH', 'status' => 'delivered']))
        ->assertOk()
        ->assertSee('ORD-SEARCH-111')
        ->assertDontSee('ORD-SEARCH-222');
});
