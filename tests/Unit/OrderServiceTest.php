<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\ProductStock;
use App\Models\Cart;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\CartService;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Test Product',
        'slug' => 'test-product',
        'description' => 'Test',
        'price' => 500,
        'discount' => 10,
        'stock' => 100,
        'sku' => 'TP-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    $this->orderService = app(OrderService::class);
    $this->actingAs($this->user);
});

function createTestOrder($orderService, $extraOrderData = [])
{
    $cartItems = Cart::where('user_id', auth()->id())->with(['product.images', 'variant', 'productStock'])->get();
    $cartService = app(CartService::class);
    $totals = $cartService->calculateTotals($cartItems);

    return $orderService->createOrder(
        array_merge(['payment_method' => 'cod'], $extraOrderData),
        $cartItems,
        $totals
    );
}

test('create order from cart', function () {
    Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'quantity' => 2,
        'price' => 450,
    ]);

    $order = createTestOrder($this->orderService);

    expect($order)->toBeInstanceOf(Order::class);
    expect($order->status)->toBe('pending');
    expect($order->items->count())->toBe(1);
    expect($order->items->first()->quantity)->toBe(2);
});

test('stock decremented after order for plain product', function () {
    Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'quantity' => 5,
        'price' => 450,
    ]);

    createTestOrder($this->orderService);

    expect($this->product->fresh()->stock)->toBe(95);
});

test('stock decremented for variant product', function () {
    $variant = ProductVariant::create([
        'product_id' => $this->product->id,
        'name' => '100ml',
        'attributes' => ['size' => '100ml'],
        'price' => 600,
        'stock' => 50,
        'is_active' => true,
    ]);

    Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3,
        'price' => 540,
    ]);

    createTestOrder($this->orderService);

    expect($variant->fresh()->stock)->toBe(47);
});

test('stock decremented for batch stock product', function () {
    $batch = ProductStock::create([
        'product_id' => $this->product->id,
        'batch_code' => 'BATCH-001',
        'quantity' => 30,
        'purchased_at' => now(),
        'expire_at' => now()->addYear(),
    ]);

    Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'product_stock_id' => $batch->id,
        'quantity' => 4,
        'price' => 450,
    ]);

    createTestOrder($this->orderService);

    expect($batch->fresh()->quantity)->toBe(26);
});

test('cart cleared after order', function () {
    Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'quantity' => 2,
        'price' => 450,
    ]);

    createTestOrder($this->orderService);

    expect(Cart::where('user_id', $this->user->id)->count())->toBe(0);
});

test('order number generated', function () {
    Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'quantity' => 1,
        'price' => 450,
    ]);

    $order = createTestOrder($this->orderService);

    expect($order->order_number)->toBeString();
    expect(strlen($order->order_number))->toBeGreaterThan(5);
});

test('cancel order restores stock for plain product', function () {
    Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'quantity' => 5,
        'price' => 450,
    ]);

    $order = createTestOrder($this->orderService);
    expect($this->product->fresh()->stock)->toBe(95);

    $cancelled = $this->orderService->cancelOrder($order, $this->user->id);

    expect($cancelled->status)->toBe('cancelled');
    expect($this->product->fresh()->stock)->toBe(100);
});

test('cancel order restores variant stock', function () {
    $variant = ProductVariant::create([
        'product_id' => $this->product->id,
        'name' => '100ml',
        'attributes' => ['size' => '100ml'],
        'price' => 600,
        'stock' => 50,
        'is_active' => true,
    ]);

    Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3,
        'price' => 540,
    ]);

    $order = createTestOrder($this->orderService);
    expect($variant->fresh()->stock)->toBe(47);

    $this->orderService->cancelOrder($order, $this->user->id);
    expect($variant->fresh()->stock)->toBe(50);
});

test('cancel order restores batch stock', function () {
    $batch = ProductStock::create([
        'product_id' => $this->product->id,
        'batch_code' => 'BATCH-002',
        'quantity' => 30,
        'purchased_at' => now(),
        'expire_at' => now()->addYear(),
    ]);

    Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'product_stock_id' => $batch->id,
        'quantity' => 4,
        'price' => 450,
    ]);

    $order = createTestOrder($this->orderService);
    expect($batch->fresh()->quantity)->toBe(26);

    $this->orderService->cancelOrder($order, $this->user->id);
    expect($batch->fresh()->quantity)->toBe(30);
});

test('cancel order fails for wrong user', function () {
    Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'quantity' => 1,
        'price' => 450,
    ]);

    $order = createTestOrder($this->orderService);

    $otherUser = User::create([
        'name' => 'Other', 'email' => 'other@test.com', 'password' => bcrypt('password'),
    ]);

    expect(fn () => $this->orderService->cancelOrder($order, $otherUser->id))
        ->toThrow(\App\Exceptions\OrderCancellationException::class, 'not authorized');
});

test('cancel order fails for shipped order', function () {
    Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'quantity' => 1,
        'price' => 450,
    ]);

    $order = createTestOrder($this->orderService);
    $order->update(['status' => 'shipped']);

    expect(fn () => $this->orderService->cancelOrder($order, $this->user->id))
        ->toThrow(\Exception::class, 'Only pending or processing orders can be cancelled');
});

test('cancel order marks paid order as refunded', function () {
    Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'quantity' => 1,
        'price' => 450,
    ]);

    $order = createTestOrder($this->orderService, ['payment_method' => 'bkash']);
    $order->update(['payment_status' => 'paid']);

    $cancelled = $this->orderService->cancelOrder($order, $this->user->id);
    expect($cancelled->payment_status)->toBe('refunded');
});

test('empty cart throws exception', function () {
    $cartItems = collect([]);
    $totals = ['subtotal' => 0, 'discount_amount' => 0, 'vat_amount' => 0, 'shipping_amount' => 0, 'payable_amount' => 0];

    expect(fn () => $this->orderService->createOrder(['payment_method' => 'cod'], $cartItems, $totals))
        ->toThrow(\Exception::class, 'Your cart is empty');
});

// ─── Customer Order Filtering ───────────────────────────────────

function seedUserOrder(User $user, string $status): Order
{
    return Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-UF-' . uniqid(),
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

test('getUserOrders filters by status when provided', function () {
    seedUserOrder($this->user, 'delivered');
    seedUserOrder($this->user, 'delivered');
    seedUserOrder($this->user, 'cancelled');

    $delivered = $this->orderService->getUserOrders($this->user->id, 'delivered');

    expect($delivered)->toHaveCount(2);
    expect($delivered->pluck('status')->unique()->all())->toBe(['delivered']);
});

test('getUserOrders returns all orders when status is null', function () {
    seedUserOrder($this->user, 'delivered');
    seedUserOrder($this->user, 'cancelled');

    expect($this->orderService->getUserOrders($this->user->id))->toHaveCount(2);
});

test('getUserOrders ignores an unknown status filter', function () {
    seedUserOrder($this->user, 'delivered');
    seedUserOrder($this->user, 'cancelled');

    // Unknown status should not filter anything out.
    expect($this->orderService->getUserOrders($this->user->id, 'archived'))->toHaveCount(2);
});

function seedSearchableOrder(User $user, string $orderNumber, string $status = 'pending'): Order
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

test('getUserOrders filters by a partial order number search', function () {
    seedSearchableOrder($this->user, 'ORD-ABC-100');
    seedSearchableOrder($this->user, 'ORD-XYZ-200');

    $results = $this->orderService->getUserOrders($this->user->id, null, 'ABC');

    expect($results)->toHaveCount(1);
    expect($results->first()->order_number)->toBe('ORD-ABC-100');
});

test('getUserOrders combines status and search filters', function () {
    seedSearchableOrder($this->user, 'ORD-ABC-100', 'delivered');
    seedSearchableOrder($this->user, 'ORD-ABC-200', 'cancelled');

    $results = $this->orderService->getUserOrders($this->user->id, 'delivered', 'ABC');

    expect($results)->toHaveCount(1);
    expect($results->first()->order_number)->toBe('ORD-ABC-100');
});

test('getUserOrders ignores a whitespace-only search', function () {
    seedSearchableOrder($this->user, 'ORD-ABC-100');
    seedSearchableOrder($this->user, 'ORD-XYZ-200');

    expect($this->orderService->getUserOrders($this->user->id, null, '   '))->toHaveCount(2);
});

test('userOrderStatusCounts returns per-status totals plus all', function () {
    seedUserOrder($this->user, 'delivered');
    seedUserOrder($this->user, 'delivered');
    seedUserOrder($this->user, 'shipped');

    $counts = $this->orderService->userOrderStatusCounts($this->user->id);

    expect($counts['all'])->toBe(3);
    expect($counts['delivered'])->toBe(2);
    expect($counts['shipped'])->toBe(1);
    expect($counts['pending'])->toBe(0);
});

test('userOrderStatusCounts only counts the given user', function () {
    $other = User::create([
        'name' => 'Other Buyer',
        'email' => 'otherbuyer@test.com',
        'password' => bcrypt('password'),
    ]);

    seedUserOrder($this->user, 'delivered');
    seedUserOrder($other, 'delivered');

    expect($this->orderService->userOrderStatusCounts($this->user->id)['all'])->toBe(1);
});
