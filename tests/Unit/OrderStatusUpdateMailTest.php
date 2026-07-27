<?php

use App\Mail\OrderStatusUpdateMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Status Mail Tester',
        'email' => 'statusmail@test.com',
        'password' => bcrypt('password'),
    ]);

    $category = Category::create([
        'name' => 'Status Mail Category',
        'slug' => 'status-mail-category',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Radiance Cream',
        'slug' => 'radiance-cream',
        'description' => 'Test',
        'price' => 600,
        'stock' => 10,
        'sku' => 'SMAIL-001',
        'category_id' => $category->id,
        'status' => 'active',
    ]);

    $this->order = Order::create([
        'user_id' => $this->user->id,
        'order_number' => 'ORD-SMAIL-001',
        'status' => 'shipped',
        'payment_status' => 'paid',
        'payment_method' => 'cod',
        'subtotal' => 1200,
        'discount_amount' => 0,
        'vat_amount' => 0,
        'shipping_amount' => 0,
        'total_amount' => 1200,
    ]);

    OrderItem::create([
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'quantity' => 2,
        'price' => 600,
        'subtotal' => 1200,
        'total' => 1200,
    ]);

    $this->order->load(['user', 'items.product', 'items.variant']);
});

test('shipped status mail renders headline, order number and items', function () {
    $html = (new OrderStatusUpdateMail($this->order, 'shipped'))->render();

    expect($html)
        ->toContain('ORD-SMAIL-001')
        ->toContain('Radiance Cream')
        ->toContain('on its way')
        ->toContain('Shipped');
});

test('status mail subject reflects the shipped status and order number', function () {
    $mailable = new OrderStatusUpdateMail($this->order, 'shipped');

    expect($mailable->envelope()->subject)
        ->toContain('shipped')
        ->toContain('ORD-SMAIL-001');
});

test('delivered status mail renders the delivered headline', function () {
    $html = (new OrderStatusUpdateMail($this->order, 'delivered'))->render();

    expect($html)->toContain('delivered');
});

test('cancelled status mail renders the cancelled copy', function () {
    $html = (new OrderStatusUpdateMail($this->order, 'cancelled'))->render();

    expect($html)->toContain('cancelled');
});

test('unknown status falls back to a generic order update subject', function () {
    $mailable = new OrderStatusUpdateMail($this->order, 'archived');

    expect($mailable->envelope()->subject)->toContain('Order update');
});

test('status mail renders an admin note when one is provided', function () {
    $html = (new OrderStatusUpdateMail($this->order, 'shipped', 'Tracking #BD98765'))->render();

    expect($html)
        ->toContain('A note from us')
        ->toContain('Tracking #BD98765');
});

test('status mail omits the note block when no note is provided', function () {
    $html = (new OrderStatusUpdateMail($this->order, 'shipped'))->render();

    expect($html)->not->toContain('A note from us');
});
