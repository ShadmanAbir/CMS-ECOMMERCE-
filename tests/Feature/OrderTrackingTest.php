<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use App\Services\OrderService;
use App\Services\CartService;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Track User',
        'email' => 'track@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->category = Category::create([
        'name' => 'Track Category',
        'slug' => 'track-category',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Track Product',
        'slug' => 'track-product',
        'price' => 500,
        'stock' => 100,
        'sku' => 'TRK-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    $this->orderService = app(OrderService::class);
    $this->actingAs($this->user);
});

function placeTrackingOrder($orderService)
{
    $cartItems = Cart::where('user_id', auth()->id())
        ->with(['product.images', 'variant', 'productStock'])
        ->get();
    $totals = app(CartService::class)->calculateTotals($cartItems);

    return $orderService->createOrder(['payment_method' => 'cod'], $cartItems, $totals);
}

function seedTrackingCart($user, $product)
{
    Cart::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 500,
    ]);
}

// ─── History Recording ──────────────────────────────────────────

test('placing an order seeds the pending status history', function () {
    seedTrackingCart($this->user, $this->product);

    $order = placeTrackingOrder($this->orderService);

    expect($order->statusHistory()->count())->toBe(1);
    $first = $order->statusHistory()->first();
    expect($first->status)->toBe('pending');
    expect($first->note)->toBe('Order placed');
});

test('each admin status transition appends a history entry', function () {
    seedTrackingCart($this->user, $this->product);
    $order = placeTrackingOrder($this->orderService);

    $this->orderService->updateOrderStatus($order, 'processing');
    $this->orderService->updateOrderStatus($order->fresh(), 'shipped');
    $this->orderService->updateOrderStatus($order->fresh(), 'delivered');

    $statuses = $order->statusHistory()->pluck('status')->all();

    expect($statuses)->toBe(['pending', 'processing', 'shipped', 'delivered']);
});

test('cancelling an order records a cancelled history entry', function () {
    seedTrackingCart($this->user, $this->product);
    $order = placeTrackingOrder($this->orderService);

    $this->orderService->cancelOrder($order, $this->user->id);

    $statuses = $order->statusHistory()->pluck('status')->all();

    expect($statuses)->toBe(['pending', 'cancelled']);
});

test('deleting an order cascades to its status history', function () {
    seedTrackingCart($this->user, $this->product);
    $order = placeTrackingOrder($this->orderService);

    $order->delete();

    $this->assertDatabaseMissing('order_status_histories', ['order_id' => $order->id]);
});

// ─── Customer-facing Timeline ───────────────────────────────────

test('customer order details page shows the delivery tracking timeline', function () {
    seedTrackingCart($this->user, $this->product);
    $order = placeTrackingOrder($this->orderService);
    $this->orderService->updateOrderStatus($order, 'processing');
    $this->orderService->updateOrderStatus($order->fresh(), 'shipped');

    $this->get(route('user.order.details', $order->order_number))
        ->assertOk()
        ->assertSee('Delivery Tracking')
        ->assertSee('Order Placed')
        ->assertSee('Shipped');
});

test('cancelled order shows a cancelled tracking state', function () {
    seedTrackingCart($this->user, $this->product);
    $order = placeTrackingOrder($this->orderService);
    $this->orderService->cancelOrder($order, $this->user->id);

    $this->get(route('user.order.details', $order->order_number))
        ->assertOk()
        ->assertSee('Order Cancelled');
});

// ─── Admin Timeline ─────────────────────────────────────────────

test('admin order page shows the status history audit trail', function () {
    seedTrackingCart($this->user, $this->product);
    $order = placeTrackingOrder($this->orderService);
    $this->orderService->updateOrderStatus($order, 'processing');
    $this->orderService->updateOrderStatus($order->fresh(), 'shipped');

    $admin = User::create([
        'name' => 'Track Admin',
        'email' => 'trackadmin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order->id))
        ->assertOk()
        ->assertSee('Status History')
        ->assertSee('Order placed')
        ->assertSee('Shipped');
});

// ─── Admin Note on Status Change ────────────────────────────────

test('service stores an admin note on the status history entry', function () {
    seedTrackingCart($this->user, $this->product);
    $order = placeTrackingOrder($this->orderService);

    $this->orderService->updateOrderStatus($order, 'processing', 'Packed and ready');

    $entry = $order->statusHistory()->where('status', 'processing')->first();
    expect($entry->note)->toBe('Packed and ready');
});

test('admin can submit a note when changing status via the controller', function () {
    seedTrackingCart($this->user, $this->product);
    $order = placeTrackingOrder($this->orderService);
    $this->orderService->updateOrderStatus($order, 'processing');

    $admin = User::create([
        'name' => 'Note Admin',
        'email' => 'noteadmin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.update-status', $order->id), [
            'status' => 'shipped',
            'note' => 'Tracking #BD12345',
        ])
        ->assertRedirect(route('admin.orders.show', $order->id));

    $this->assertDatabaseHas('order_status_histories', [
        'order_id' => $order->id,
        'status' => 'shipped',
        'note' => 'Tracking #BD12345',
    ]);
});

test('a submitted note appears in the admin timeline', function () {
    seedTrackingCart($this->user, $this->product);
    $order = placeTrackingOrder($this->orderService);
    $this->orderService->updateOrderStatus($order, 'processing', 'Verified payment');

    $admin = User::create([
        'name' => 'Note View Admin',
        'email' => 'noteviewadmin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order->id))
        ->assertOk()
        ->assertSee('Verified payment');
});

test('note over 255 characters is rejected', function () {
    seedTrackingCart($this->user, $this->product);
    $order = placeTrackingOrder($this->orderService);

    $admin = User::create([
        'name' => 'Long Note Admin',
        'email' => 'longnoteadmin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.update-status', $order->id), [
            'status' => 'processing',
            'note' => str_repeat('x', 256),
        ])
        ->assertSessionHasErrors('note');

    expect($order->fresh()->status)->toBe('pending');
});
