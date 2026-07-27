<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\Cart;
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
        'description' => 'A test product',
        'price' => 500,
        'discount' => 10,
        'stock' => 100,
        'sku' => 'TP-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    $this->cartService = app(CartService::class);
});

test('add product to cart', function () {
    $this->actingAs($this->user);

    $cart = $this->cartService->addToCart($this->product->id, 2);

    expect($cart)->toBeInstanceOf(Cart::class);
    expect($cart->product_id)->toBe($this->product->id);
    expect($cart->quantity)->toBe(2);
    expect($cart->user_id)->toBe($this->user->id);
});

test('add same product twice increments quantity', function () {
    $this->actingAs($this->user);

    $this->cartService->addToCart($this->product->id, 2);
    $cart = $this->cartService->addToCart($this->product->id, 3);

    expect($cart->quantity)->toBe(5);
    expect(Cart::where('user_id', $this->user->id)->count())->toBe(1);
});

test('add variant to cart', function () {
    $this->actingAs($this->user);

    $variant = ProductVariant::create([
        'product_id' => $this->product->id,
        'name' => '100ml',
        'attributes' => ['size' => '100ml'],
        'price' => 600,
        'stock' => 50,
        'is_active' => true,
    ]);

    $cart = $this->cartService->addToCart($this->product->id, 1, null, null, null, $variant->id);

    expect($cart->product_variant_id)->toBe($variant->id);
    expect($cart->price)->toEqual(540.00); // 600 - 10% discount
});

test('get cart returns user items', function () {
    $this->actingAs($this->user);

    $this->cartService->addToCart($this->product->id, 2);

    $cart = $this->cartService->getCart();

    expect($cart->count())->toBe(1);
    expect($cart->first()->product->name)->toBe('Test Product');
});

test('get cart returns empty for guest', function () {
    $cart = $this->cartService->getCart();
    expect($cart->count())->toBe(0);
});

test('update cart quantity', function () {
    $this->actingAs($this->user);

    $cart = $this->cartService->addToCart($this->product->id, 2);
    $updated = $this->cartService->updateCart($cart->id, 5);

    expect($updated->quantity)->toBe(5);
});

test('update cart fails for other user', function () {
    $this->actingAs($this->user);
    $cart = $this->cartService->addToCart($this->product->id, 2);

    $otherUser = User::create([
        'name' => 'Other',
        'email' => 'other@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($otherUser);

    expect(fn () => $this->cartService->updateCart($cart->id, 10))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

test('remove from cart', function () {
    $this->actingAs($this->user);

    $cart = $this->cartService->addToCart($this->product->id, 2);
    $this->cartService->removeFromCart($cart->id);

    expect(Cart::where('user_id', $this->user->id)->count())->toBe(0);
});

test('calculate totals with discount', function () {
    $this->actingAs($this->user);

    $this->cartService->addToCart($this->product->id, 2);
    $cartItems = $this->cartService->getCart();
    $totals = $this->cartService->calculateTotals($cartItems);

    // Price: 500, discount: 10% => 450 each, 2 items = 900
    expect($totals['subtotal'])->toBe(900.0);
    expect($totals['vat_amount'])->toBe(round(900 * 0.05));
    expect($totals['payable_amount'])->toBe(round(900 + 900 * 0.05));
});

test('get cart count', function () {
    $this->actingAs($this->user);

    $this->cartService->addToCart($this->product->id, 3);

    expect($this->cartService->getCartCount())->toBe(3);
});

test('cart count returns 0 for guest', function () {
    expect($this->cartService->getCartCount())->toBe(0);
});
