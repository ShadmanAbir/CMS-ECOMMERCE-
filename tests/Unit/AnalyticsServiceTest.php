<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\User;
use App\Services\AnalyticsService;

beforeEach(function () {
    $this->service = app(AnalyticsService::class);

    $this->user = User::create([
        'name' => 'Analytics User',
        'email' => 'analytics@test.com',
        'password' => bcrypt('password'),
        'role' => 'customer',
    ]);

    $this->category = Category::create([
        'name' => 'Analytics Cat',
        'slug' => 'analytics-cat',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Analytics Product',
        'slug' => 'analytics-product',
        'price' => 500,
        'stock' => 50,
        'sku' => 'ANL-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);
});

function makeAnalyticsOrder(User $user, float $total, string $status = 'delivered', ?string $date = null): Order
{
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-ANL-' . uniqid(),
        'status' => $status,
        'payment_method' => 'cod',
        'subtotal' => $total,
        'total_amount' => $total,
    ]);

    if ($date) {
        $order->created_at = $date;
        $order->save();
    }

    return $order;
}

// ─── Revenue Summary ─────────────────────────────────────────────

test('revenue summary returns correct structure', function () {
    $result = $this->service->revenueSummary();

    expect($result)->toHaveKeys(['today', 'this_week', 'this_month', 'all_time'])
        ->and($result['today'])->toHaveKeys(['amount', 'growth'])
        ->and($result['all_time']['growth'])->toBeNull();
});

test('revenue summary calculates today revenue', function () {
    makeAnalyticsOrder($this->user, 1000, 'delivered');
    makeAnalyticsOrder($this->user, 500, 'pending');

    $result = $this->service->revenueSummary();

    expect($result['today']['amount'])->toBe(1500.0);
});

test('revenue summary excludes cancelled orders', function () {
    makeAnalyticsOrder($this->user, 1000, 'delivered');
    makeAnalyticsOrder($this->user, 500, 'cancelled');

    $result = $this->service->revenueSummary();

    expect($result['today']['amount'])->toBe(1000.0);
});

test('revenue summary calculates growth percent', function () {
    makeAnalyticsOrder($this->user, 1000, 'delivered'); // today
    makeAnalyticsOrder($this->user, 500, 'delivered', now()->subDay()->format('Y-m-d 12:00:00')); // yesterday

    $result = $this->service->revenueSummary();

    expect($result['today']['growth'])->toBe(100.0); // (1000-500)/500 * 100
});

// ─── Revenue Chart ───────────────────────────────────────────────

test('revenue chart returns labels and data arrays', function () {
    $result = $this->service->revenueChart(7);

    expect($result)->toHaveKeys(['labels', 'revenue', 'orders'])
        ->and($result['labels'])->toHaveCount(7)
        ->and($result['revenue'])->toHaveCount(7)
        ->and($result['orders'])->toHaveCount(7);
});

test('revenue chart includes today data', function () {
    makeAnalyticsOrder($this->user, 800, 'delivered');

    $result = $this->service->revenueChart(7);

    // Last element is today
    expect(end($result['revenue']))->toBe(800.0)
        ->and(end($result['orders']))->toBe(1);
});

test('revenue chart excludes cancelled orders', function () {
    makeAnalyticsOrder($this->user, 800, 'cancelled');

    $result = $this->service->revenueChart(7);

    expect(end($result['revenue']))->toBe(0.0);
});

// ─── Order Status Breakdown ──────────────────────────────────────

test('order status breakdown counts by status', function () {
    makeAnalyticsOrder($this->user, 100, 'pending');
    makeAnalyticsOrder($this->user, 200, 'pending');
    makeAnalyticsOrder($this->user, 300, 'delivered');

    $result = $this->service->orderStatusBreakdown();

    expect($result['pending'])->toBe(2)
        ->and($result['delivered'])->toBe(1)
        ->and($result['shipped'])->toBe(0)
        ->and($result['cancelled'])->toBe(0);
});

// ─── Top Products ────────────────────────────────────────────────

test('top products returns best sellers by revenue', function () {
    $product2 = Product::create([
        'name' => 'Second Product',
        'slug' => 'second-product',
        'price' => 200,
        'stock' => 50,
        'sku' => 'ANL-002',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    $order = makeAnalyticsOrder($this->user, 1500);
    OrderItem::create(['order_id' => $order->id, 'product_id' => $this->product->id, 'quantity' => 2, 'price' => 500, 'subtotal' => 1000, 'total' => 1000]);
    OrderItem::create(['order_id' => $order->id, 'product_id' => $product2->id, 'quantity' => 1, 'price' => 200, 'subtotal' => 200, 'total' => 200]);

    $result = $this->service->topProducts(5);

    expect($result)->toHaveCount(2)
        ->and($result->first()->name)->toBe('Analytics Product')
        ->and((float) $result->first()->total_revenue)->toBe(1000.0)
        ->and((int) $result->first()->total_quantity)->toBe(2);
});

test('top products excludes cancelled orders', function () {
    $order = makeAnalyticsOrder($this->user, 500, 'cancelled');
    OrderItem::create(['order_id' => $order->id, 'product_id' => $this->product->id, 'quantity' => 1, 'price' => 500, 'subtotal' => 500, 'total' => 500]);

    $result = $this->service->topProducts(5);

    expect($result)->toHaveCount(0);
});

// ─── Order Stats ─────────────────────────────────────────────────

test('order stats returns correct counts', function () {
    makeAnalyticsOrder($this->user, 100, 'pending');
    makeAnalyticsOrder($this->user, 200, 'delivered');

    $result = $this->service->orderStats();

    expect($result['today_orders'])->toBe(2)
        ->and($result['total_orders'])->toBe(2)
        ->and($result['pending_orders'])->toBe(1)
        ->and($result['total_customers'])->toBe(1);
});

// ─── Low Stock ───────────────────────────────────────────────────

test('low stock returns products below threshold', function () {
    $this->product->update(['stock' => 3]);

    Product::create([
        'name' => 'Well Stocked',
        'slug' => 'well-stocked',
        'price' => 100,
        'stock' => 100,
        'sku' => 'ANL-003',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    $result = $this->service->lowStockProducts(5, 5);

    expect($result)->toHaveCount(1)
        ->and($result->first()->name)->toBe('Analytics Product');
});

test('low stock excludes inactive products', function () {
    $this->product->update(['stock' => 0, 'status' => 'inactive']);

    $result = $this->service->lowStockProducts(5, 5);

    expect($result)->toHaveCount(0);
});

// ─── Coupon Stats ────────────────────────────────────────────────

function makeCouponOrder(User $user, ?string $code, float $discount, string $status = 'delivered'): Order
{
    return Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-CPN-' . uniqid(),
        'status' => $status,
        'payment_method' => 'cod',
        'subtotal' => 1000,
        'discount_amount' => $discount,
        'total_amount' => 1000 - $discount,
        'coupon_code' => $code,
    ]);
}

test('coupon stats returns the expected structure', function () {
    $result = $this->service->couponStats();

    expect($result)->toHaveKeys(['total_redemptions', 'total_discount', 'active_coupons', 'top']);
});

test('coupon stats totals redemptions and discount from valid orders', function () {
    makeCouponOrder($this->user, 'SAVE10', 100);
    makeCouponOrder($this->user, 'SAVE10', 150);
    makeCouponOrder($this->user, 'WELCOME', 50);

    $result = $this->service->couponStats();

    expect($result['total_redemptions'])->toBe(3)
        ->and($result['total_discount'])->toBe(300.0);
});

test('coupon stats ignores orders without a coupon and cancelled orders', function () {
    makeCouponOrder($this->user, 'SAVE10', 100);
    makeCouponOrder($this->user, null, 0);            // no coupon
    makeCouponOrder($this->user, 'SAVE10', 100, 'cancelled'); // cancelled

    $result = $this->service->couponStats();

    expect($result['total_redemptions'])->toBe(1)
        ->and($result['total_discount'])->toBe(100.0);
});

test('coupon stats ranks the most used coupons first', function () {
    makeCouponOrder($this->user, 'SAVE10', 100);
    makeCouponOrder($this->user, 'SAVE10', 100);
    makeCouponOrder($this->user, 'WELCOME', 500);

    $result = $this->service->couponStats();
    $top = $result['top'];

    expect($top->first()->coupon_code)->toBe('SAVE10')
        ->and((int) $top->first()->redemptions)->toBe(2)
        ->and((float) $top->first()->total_discount)->toBe(200.0);
});

test('coupon stats counts active coupons', function () {
    Coupon::create(['code' => 'ACTIVE1', 'type' => 'fixed', 'value' => 100, 'is_active' => true]);
    Coupon::create(['code' => 'INACTIVE1', 'type' => 'fixed', 'value' => 100, 'is_active' => false]);

    $result = $this->service->couponStats();

    expect($result['active_coupons'])->toBe(1);
});
