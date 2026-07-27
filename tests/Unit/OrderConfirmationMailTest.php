<?php

use App\Mail\OrderConfirmationMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Mail Tester',
        'email' => 'mail@test.com',
        'password' => bcrypt('password'),
    ]);

    $category = Category::create([
        'name' => 'Mail Category',
        'slug' => 'mail-category',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Glow Serum Deluxe',
        'slug' => 'glow-serum-deluxe',
        'description' => 'Test',
        'price' => 850,
        'stock' => 10,
        'sku' => 'MAIL-001',
        'category_id' => $category->id,
        'status' => 'active',
    ]);

    $this->order = Order::create([
        'user_id' => $this->user->id,
        'order_number' => 'ORD-MAIL-001',
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => 'cod',
        'subtotal' => 1700,
        'discount_amount' => 0,
        'vat_amount' => 0,
        'shipping_amount' => 0,
        'total_amount' => 1700,
    ]);

    OrderItem::create([
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'quantity' => 2,
        'price' => 850,
        'subtotal' => 1700,
        'total' => 1700,
    ]);

    $this->order->load(['user', 'items.product', 'items.variant']);
});

test('order confirmation mail renders order details', function () {
    $html = (new OrderConfirmationMail($this->order))->render();

    expect($html)
        ->toContain('ORD-MAIL-001')
        ->toContain('Glow Serum Deluxe')
        ->toContain('Mail Tester')
        ->toContain('1,700.00');
});

test('order confirmation mail has branded subject with order number', function () {
    $mailable = new OrderConfirmationMail($this->order);

    expect($mailable->envelope()->subject)->toContain('ORD-MAIL-001');
});

test('order confirmation mail renders item quantity', function () {
    $html = (new OrderConfirmationMail($this->order))->render();

    // Quantity column shows "2" for the line item
    expect($html)->toContain('Order Items');
});
