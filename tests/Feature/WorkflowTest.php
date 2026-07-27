<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CustomerAddress;

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
        'description' => 'Test product description',
        'price' => 500,
        'discount' => 10,
        'stock' => 100,
        'sku' => 'TP-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);
});

// ─── Auth Workflow ───────────────────────────────────────────────

test('user can register', function () {
    $response = $this->post('/register', [
        'fullName' => 'New User',
        'identifier' => 'newuser@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', ['email' => 'newuser@test.com']);
});

test('user can login', function () {
    $response = $this->post('/login', [
        'identifier' => 'test@test.com',
        'password' => 'password',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticatedAs($this->user);
});

test('user can logout', function () {
    $this->actingAs($this->user);
    $response = $this->post('/logout');
    $response->assertRedirect();
    $this->assertGuest();
});

test('login fails with wrong password', function () {
    $this->post('/login', [
        'identifier' => 'test@test.com',
        'password' => 'wrongpassword',
    ]);
    $this->assertGuest();
});

// ─── Shop Pages ──────────────────────────────────────────────────

test('homepage loads', function () {
    $this->get('/')->assertOk();
});

test('product details page loads', function () {
    $this->get('/product/' . $this->product->slug)->assertOk();
    $this->get('/product/' . $this->product->slug)->assertSee($this->product->name);
});

test('product details increments view count', function () {
    $initialViews = $this->product->views_count ?? 0;
    $this->get('/product/' . $this->product->slug);
    expect($this->product->fresh()->views_count)->toBe($initialViews + 1);
});

test('search page loads', function () {
    $this->get('/search')->assertOk();
});

test('search with keyword', function () {
    $this->get('/search?q=Test')->assertOk()->assertSee('Test Product');
});

test('search with category filter', function () {
    $this->get('/search?category=' . $this->category->id)->assertOk();
});

test('search with price range', function () {
    $this->get('/search?min_price=100&max_price=600')->assertOk()->assertSee('Test Product');
});

test('search with sort', function () {
    $this->get('/search?sort=price_low')->assertOk();
});

test('category page loads', function () {
    $this->get('/category/' . $this->category->slug)->assertOk();
});

test('live search returns JSON', function () {
    $this->get('/live-search?q=Test')->assertOk()->assertJsonStructure(['products']);
});

test('live search empty for short query', function () {
    $this->get('/live-search?q=T')->assertOk()->assertJson(['products' => []]);
});

// ─── Cart Workflow (JSON API) ────────────────────────────────────

test('cart page loads without auth', function () {
    // Cart page renders view, doesn't require auth middleware
    $this->get('/cart')->assertOk();
});

test('cart page loads for authenticated user', function () {
    $this->actingAs($this->user);
    $this->get('/cart')->assertOk();
});

test('add to cart returns JSON', function () {
    $this->actingAs($this->user);

    $response = $this->post('/cart/add', [
        'product_id' => $this->product->id,
        'quantity' => 2,
    ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);
    $this->assertDatabaseHas('carts', [
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'quantity' => 2,
    ]);
});

test('update cart returns JSON', function () {
    $this->actingAs($this->user);

    $cart = Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'quantity' => 1,
        'price' => 450,
    ]);

    $response = $this->put('/cart/update/' . $cart->id, ['quantity' => 5]);

    $response->assertOk();
    $response->assertJson(['success' => true]);
    expect(Cart::find($cart->id)->quantity)->toBe(5);
});

test('remove from cart returns JSON', function () {
    $this->actingAs($this->user);

    $cart = Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'quantity' => 1,
        'price' => 450,
    ]);

    $response = $this->delete('/cart/remove/' . $cart->id);

    $response->assertOk();
    $response->assertJson(['success' => true]);
    $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
});

// ─── Checkout Workflow (JSON API) ────────────────────────────────

test('checkout page loads', function () {
    $this->actingAs($this->user);

    Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'quantity' => 1,
        'price' => 450,
    ]);

    $this->get('/checkout')->assertOk();
});

test('checkout redirects when cart empty', function () {
    $this->actingAs($this->user);
    $this->get('/checkout')->assertRedirect(route('cart.index'));
});

test('place order with COD returns JSON', function () {
    $this->actingAs($this->user);

    Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'quantity' => 2,
        'price' => 450,
    ]);

    $response = $this->post('/checkout', [
        'payment_method' => 'cod',
        'address' => '123 Test St',
        'city' => 'Dhaka',
        'division' => 'Dhaka',
        'district' => 'Dhaka',
        'thana' => 'Gulshan',
        'postal_code' => '1000',
    ]);

    $response->assertOk();
    $json = $response->json();
    expect($json['success'])->toBeTrue();
    $this->assertDatabaseHas('orders', [
        'user_id' => $this->user->id,
        'status' => 'pending',
        'payment_method' => 'cod',
    ]);
});

test('place order decrements stock', function () {
    $this->actingAs($this->user);

    Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'quantity' => 5,
        'price' => 450,
    ]);

    $this->post('/checkout', [
        'payment_method' => 'cod',
        'address' => '123 Test St',
        'city' => 'Dhaka',
        'division' => 'Dhaka',
        'district' => 'Dhaka',
        'thana' => 'Gulshan',
    ]);

    expect($this->product->fresh()->stock)->toBe(95);
});

test('place order fails with empty cart', function () {
    $this->actingAs($this->user);

    $response = $this->post('/checkout', [
        'payment_method' => 'cod',
        'address' => '123 Test St',
        'city' => 'Dhaka',
        'division' => 'Dhaka',
        'district' => 'Dhaka',
        'thana' => 'Gulshan',
    ]);

    $response->assertStatus(400);
});

// ─── User Dashboard ──────────────────────────────────────────────

test('user can view orders', function () {
    $this->actingAs($this->user);
    $this->get('/my-orders')->assertOk();
});

test('user can view order details', function () {
    $this->actingAs($this->user);

    $order = Order::create([
        'user_id' => $this->user->id,
        'order_number' => 'ORD-TEST-001',
        'payment_method' => 'cod',
        'subtotal' => 900,
        'vat_amount' => 45,
        'total_amount' => 945,
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $this->get('/my-orders/' . $order->order_number)->assertOk();
});

test('user can cancel pending order', function () {
    $this->actingAs($this->user);

    $order = Order::create([
        'user_id' => $this->user->id,
        'order_number' => 'ORD-CANCEL-001',
        'payment_method' => 'cod',
        'subtotal' => 450,
        'vat_amount' => 23,
        'total_amount' => 473,
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $this->product->id,
        'quantity' => 1,
        'price' => 450,
        'subtotal' => 450,
        'total' => 450,
    ]);

    $this->post('/my-orders/' . $order->order_number . '/cancel')->assertRedirect();
    expect($order->fresh()->status)->toBe('cancelled');
});

test('user cannot cancel shipped order', function () {
    $this->actingAs($this->user);

    $order = Order::create([
        'user_id' => $this->user->id,
        'order_number' => 'ORD-SHIP-001',
        'payment_method' => 'cod',
        'subtotal' => 450,
        'vat_amount' => 23,
        'total_amount' => 473,
        'status' => 'shipped',
        'payment_status' => 'pending',
    ]);

    $this->post('/my-orders/' . $order->order_number . '/cancel');
    expect($order->fresh()->status)->toBe('shipped');
});

// ─── Review Workflow ─────────────────────────────────────────────

test('submit review returns JSON', function () {
    $this->actingAs($this->user);

    $response = $this->post('/product/' . $this->product->id . '/reviews', [
        'rating' => 5,
        'title' => 'Great product!',
        'body' => 'Really love it.',
    ]);

    $response->assertOk();
    expect($response->json('success'))->toBeTrue();
    $this->assertDatabaseHas('product_reviews', [
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'rating' => 5,
    ]);
});

test('get reviews returns JSON', function () {
    $this->get('/product/' . $this->product->id . '/reviews')
        ->assertOk()
        ->assertJsonStructure(['reviews', 'average_rating', 'reviews_count']);
});

// ─── Admin Workflow ──────────────────────────────────────────────

test('admin dashboard requires admin role', function () {
    $this->actingAs($this->user);
    $this->get('/admin/dashboard')->assertStatus(403);
});

test('admin dashboard loads for admin user', function () {
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin);
    $this->get('/admin/dashboard')->assertOk();
});

// ─── Admin Order Management ────────────────────────────────────

function makeAdmin(): User
{
    return User::create([
        'name' => 'Order Admin',
        'email' => 'orderadmin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
}

function makeOrder(User $user, string $status = 'pending'): Order
{
    return Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-TEST-' . uniqid(),
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

test('admin orders index requires admin role', function () {
    $this->actingAs($this->user);
    $this->get('/admin/orders')->assertStatus(403);
});

test('admin orders index loads for admin user', function () {
    $this->actingAs(makeAdmin());
    makeOrder($this->user);

    $this->get('/admin/orders')
        ->assertOk()
        ->assertSee('ORDER');
});

test('admin can view a single order', function () {
    $this->actingAs(makeAdmin());
    $order = makeOrder($this->user);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $this->product->id,
        'quantity' => 2,
        'price' => 250,
        'subtotal' => 500,
        'total' => 500,
    ]);

    $this->get("/admin/orders/{$order->id}")
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee('Test Product');
});

test('admin can transition order from pending to processing', function () {
    $this->actingAs(makeAdmin());
    $order = makeOrder($this->user, 'pending');

    $this->patch("/admin/orders/{$order->id}/status", ['status' => 'processing'])
        ->assertRedirect("/admin/orders/{$order->id}");

    expect($order->fresh()->status)->toBe('processing');
});

test('admin cannot perform an invalid status transition', function () {
    $this->actingAs(makeAdmin());
    $order = makeOrder($this->user, 'pending');

    // pending -> delivered is not allowed
    $this->patch("/admin/orders/{$order->id}/status", ['status' => 'delivered']);

    expect($order->fresh()->status)->toBe('pending');
});

test('admin can follow the full fulfilment flow', function () {
    $this->actingAs(makeAdmin());
    $order = makeOrder($this->user, 'pending');

    foreach (['processing', 'shipped', 'delivered'] as $next) {
        $this->patch("/admin/orders/{$order->id}/status", ['status' => $next]);
        expect($order->fresh()->status)->toBe($next);
    }
});

test('cancelling an order restores product stock', function () {
    $this->actingAs(makeAdmin());

    $this->product->update(['stock' => 50]);
    $order = makeOrder($this->user, 'pending');
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $this->product->id,
        'quantity' => 3,
        'price' => 250,
        'subtotal' => 750,
        'total' => 750,
    ]);

    $this->patch("/admin/orders/{$order->id}/status", ['status' => 'cancelled']);

    expect($order->fresh()->status)->toBe('cancelled')
        ->and($this->product->fresh()->stock)->toBe(53);
});

test('orders index can be filtered by status', function () {
    $this->actingAs(makeAdmin());
    $pending = makeOrder($this->user, 'pending');
    $delivered = makeOrder($this->user, 'delivered');

    $this->get('/admin/orders?status=pending')
        ->assertOk()
        ->assertSee($pending->order_number)
        ->assertDontSee($delivered->order_number);
});

// ─── Wishlist Workflow ───────────────────────────────────────────

test('wishlist toggle requires authentication', function () {
    $this->postJson('/wishlist/toggle', ['product_id' => $this->product->id])
        ->assertStatus(401);
});

test('user can add product to wishlist', function () {
    $this->actingAs($this->user);

    $this->postJson('/wishlist/toggle', ['product_id' => $this->product->id])
        ->assertOk()
        ->assertJson(['success' => true, 'added' => true, 'count' => 1]);

    $this->assertDatabaseHas('wishlists', [
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
    ]);
});

test('user can remove product from wishlist by toggling again', function () {
    $this->actingAs($this->user);

    $this->postJson('/wishlist/toggle', ['product_id' => $this->product->id]);
    $this->postJson('/wishlist/toggle', ['product_id' => $this->product->id])
        ->assertOk()
        ->assertJson(['success' => true, 'added' => false, 'count' => 0]);

    $this->assertDatabaseMissing('wishlists', [
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
    ]);
});

test('wishlist page loads for authenticated user', function () {
    $this->actingAs($this->user);
    $this->get('/wishlist')->assertOk();
});
