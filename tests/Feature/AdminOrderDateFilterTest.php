<?php

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Date Filter Admin',
        'email' => 'dateadmin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->customer = User::create([
        'name' => 'Date Customer',
        'email' => 'datecustomer@test.com',
        'password' => bcrypt('password'),
        'role' => 'customer',
    ]);
});

function makeDatedOrder(User $customer, string $orderNumber, string $date): Order
{
    $order = Order::create([
        'user_id' => $customer->id,
        'order_number' => $orderNumber,
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => 'cod',
        'subtotal' => 500,
        'discount_amount' => 0,
        'vat_amount' => 0,
        'shipping_amount' => 60,
        'total_amount' => 560,
    ]);

    // Override Eloquent's automatic timestamps
    $order->timestamps = false;
    $order->created_at = Carbon::parse($date);
    $order->updated_at = Carbon::parse($date);
    $order->save();

    return $order;
}

test('admin can filter orders by from date', function () {
    makeDatedOrder($this->customer, 'ORD-OLD', '2025-01-10');
    makeDatedOrder($this->customer, 'ORD-NEW', '2025-06-15');

    $this->actingAs($this->admin)
        ->get(route('admin.orders.index', ['from' => '2025-06-01']))
        ->assertOk()
        ->assertSee('ORD-NEW')
        ->assertDontSee('ORD-OLD');
});

test('admin can filter orders by to date', function () {
    makeDatedOrder($this->customer, 'ORD-OLD', '2025-01-10');
    makeDatedOrder($this->customer, 'ORD-NEW', '2025-06-15');

    $this->actingAs($this->admin)
        ->get(route('admin.orders.index', ['to' => '2025-03-01']))
        ->assertOk()
        ->assertSee('ORD-OLD')
        ->assertDontSee('ORD-NEW');
});

test('admin can filter orders by date range', function () {
    makeDatedOrder($this->customer, 'ORD-JAN', '2025-01-10');
    makeDatedOrder($this->customer, 'ORD-MAR', '2025-03-15');
    makeDatedOrder($this->customer, 'ORD-JUN', '2025-06-20');

    $this->actingAs($this->admin)
        ->get(route('admin.orders.index', ['from' => '2025-02-01', 'to' => '2025-04-30']))
        ->assertOk()
        ->assertSee('ORD-MAR')
        ->assertDontSee('ORD-JAN')
        ->assertDontSee('ORD-JUN');
});

test('date filter is preserved in status tabs', function () {
    makeDatedOrder($this->customer, 'ORD-001', '2025-06-15');

    $this->actingAs($this->admin)
        ->get(route('admin.orders.index', ['from' => '2025-06-01', 'to' => '2025-06-30']))
        ->assertOk()
        ->assertSee('from=2025-06-01')
        ->assertSee('to=2025-06-30');
});
