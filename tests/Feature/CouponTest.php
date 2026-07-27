<?php

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Coupon User',
        'email' => 'coupon@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->category = Category::create([
        'name' => 'Coupon Category',
        'slug' => 'coupon-category',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Coupon Product',
        'slug' => 'coupon-product',
        'description' => 'Coupon product description',
        'price' => 500,
        'discount' => 0,
        'stock' => 100,
        'sku' => 'CPN-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);
});

function makeCouponAdmin(): User
{
    return User::create([
        'name' => 'Coupon Admin',
        'email' => 'couponadmin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
}

function seedCart(User $user, Product $product, int $quantity = 2): void
{
    Cart::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => $quantity,
        'price' => $product->price,
    ]);
}

// ─── Storefront: Apply / Remove Coupon ───────────────────────────

test('user can apply a valid coupon on the cart', function () {
    $this->actingAs($this->user);
    seedCart($this->user, $this->product);

    Coupon::create([
        'code' => 'SAVE10',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ]);

    $response = $this->post('/cart/coupon/apply', ['code' => 'SAVE10']);

    $response->assertOk();
    expect($response->json('success'))->toBeTrue()
        ->and($response->json('coupon_code'))->toBe('SAVE10');
});

test('applying an invalid coupon returns 422', function () {
    $this->actingAs($this->user);
    seedCart($this->user, $this->product);

    $response = $this->post('/cart/coupon/apply', ['code' => 'NOPE']);

    $response->assertStatus(422);
    expect($response->json('success'))->toBeFalse();
});

test('user can remove an applied coupon', function () {
    $this->actingAs($this->user);
    seedCart($this->user, $this->product);

    Coupon::create([
        'code' => 'SAVE10',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ]);

    $this->post('/cart/coupon/apply', ['code' => 'SAVE10']);

    $response = $this->delete('/cart/coupon/remove');

    $response->assertOk();
    expect($response->json('success'))->toBeTrue()
        ->and($response->json('coupon_code'))->toBeNull();
});

// ─── Storefront: Checkout with Coupon ────────────────────────────

test('placing an order with a coupon stores code and records usage', function () {
    $this->actingAs($this->user);
    seedCart($this->user, $this->product, 2);

    $coupon = Coupon::create([
        'code' => 'SAVE10',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ]);

    $this->post('/cart/coupon/apply', ['code' => 'SAVE10']);

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
    expect($response->json('success'))->toBeTrue();

    $this->assertDatabaseHas('orders', [
        'user_id' => $this->user->id,
        'coupon_code' => 'SAVE10',
    ]);

    expect($coupon->fresh()->usage_count)->toBe(1);
});

test('coupon is cleared from session after a successful order', function () {
    $this->actingAs($this->user);
    seedCart($this->user, $this->product, 1);

    Coupon::create([
        'code' => 'SAVE10',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ]);

    $this->post('/cart/coupon/apply', ['code' => 'SAVE10']);

    $this->post('/checkout', [
        'payment_method' => 'cod',
        'address' => '123 Test St',
        'city' => 'Dhaka',
        'division' => 'Dhaka',
        'district' => 'Dhaka',
        'thana' => 'Gulshan',
    ]);

    expect(session('coupon_code'))->toBeNull();
});

// ─── Admin: Coupon Management ────────────────────────────────────

test('admin coupons index requires admin role', function () {
    $this->actingAs($this->user);
    $this->get('/admin/coupons')->assertStatus(403);
});

test('admin coupons index loads for admin user', function () {
    $this->actingAs(makeCouponAdmin());

    Coupon::create([
        'code' => 'WELCOME20',
        'type' => 'percentage',
        'value' => 20,
        'is_active' => true,
    ]);

    $this->get('/admin/coupons')
        ->assertOk()
        ->assertSee('WELCOME20');
});

test('admin can create a coupon', function () {
    $this->actingAs(makeCouponAdmin());

    $response = $this->post('/admin/coupons', [
        'code' => 'newyear25',
        'type' => 'percentage',
        'value' => 25,
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.coupons.index'));
    $this->assertDatabaseHas('coupons', [
        'code' => 'NEWYEAR25',
        'type' => 'percentage',
    ]);
});

test('admin can update a coupon', function () {
    $this->actingAs(makeCouponAdmin());

    $coupon = Coupon::create([
        'code' => 'OLD10',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ]);

    $this->put("/admin/coupons/{$coupon->id}", [
        'code' => 'OLD10',
        'type' => 'fixed',
        'value' => 100,
        'is_active' => 1,
    ]);

    expect($coupon->fresh()->type)->toBe('fixed')
        ->and((float) $coupon->fresh()->value)->toBe(100.0);
});

test('admin can toggle coupon status', function () {
    $this->actingAs(makeCouponAdmin());

    $coupon = Coupon::create([
        'code' => 'TOGGLE10',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ]);

    $this->post("/admin/coupons/{$coupon->id}/toggle-status");

    expect($coupon->fresh()->is_active)->toBeFalse();
});

test('admin can delete a coupon', function () {
    $this->actingAs(makeCouponAdmin());

    $coupon = Coupon::create([
        'code' => 'DELETE10',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ]);

    $this->delete("/admin/coupons/{$coupon->id}");

    $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
});
