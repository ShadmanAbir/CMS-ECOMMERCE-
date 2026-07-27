<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductReview;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Review User',
        'email' => 'reviewer@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->admin = User::create([
        'name' => 'Review Admin',
        'email' => 'reviewadmin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->category = Category::create([
        'name' => 'Review Category',
        'slug' => 'review-category',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Review Product',
        'slug' => 'review-product',
        'price' => 500,
        'stock' => 50,
        'sku' => 'REV-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);
});

function makeReview(Product $product, User $user, bool $approved = false): ProductReview
{
    return ProductReview::create([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'rating' => 4,
        'title' => 'Great product',
        'body' => 'Really enjoyed using this product.',
        'is_verified_purchase' => true,
        'is_approved' => $approved,
    ]);
}

// ─── Access Control ──────────────────────────────────────────────

test('admin reviews index requires admin role', function () {
    $this->actingAs($this->user);
    $this->get('/admin/reviews')->assertStatus(403);
});

test('admin reviews index loads for admin user', function () {
    $this->actingAs($this->admin);
    makeReview($this->product, $this->user);

    $this->get('/admin/reviews')
        ->assertOk()
        ->assertSee('Review Product')
        ->assertSee('Great product');
});

// ─── Filtering ───────────────────────────────────────────────────

test('reviews default filter shows pending only', function () {
    $this->actingAs($this->admin);
    makeReview($this->product, $this->user, false); // pending
    makeReview($this->product, $this->user, true);  // approved

    $response = $this->get('/admin/reviews');

    $response->assertOk();
    // Pending count should be 1
    $response->assertSee('Pending (1)');
    $response->assertSee('Approved (1)');
});

test('reviews can be filtered by approved', function () {
    $this->actingAs($this->admin);
    makeReview($this->product, $this->user, true);

    $this->get('/admin/reviews?filter=approved')
        ->assertOk()
        ->assertSee('Great product');
});

// ─── Moderation Actions ─────────────────────────────────────────

test('admin can approve a pending review', function () {
    $this->actingAs($this->admin);
    $review = makeReview($this->product, $this->user, false);

    $this->post("/admin/reviews/{$review->id}/approve");

    expect($review->fresh()->is_approved)->toBeTrue();
});

test('admin can reject an approved review', function () {
    $this->actingAs($this->admin);
    $review = makeReview($this->product, $this->user, true);

    $this->post("/admin/reviews/{$review->id}/reject");

    expect($review->fresh()->is_approved)->toBeFalse();
});

test('admin can delete a review', function () {
    $this->actingAs($this->admin);
    $review = makeReview($this->product, $this->user);

    $this->delete("/admin/reviews/{$review->id}");

    $this->assertDatabaseMissing('product_reviews', ['id' => $review->id]);
});

// ─── Storefront Integration ─────────────────────────────────────

test('approved reviews appear in reviews endpoint', function () {
    makeReview($this->product, $this->user, true);

    $response = $this->getJson('/product/' . $this->product->id . '/reviews');

    $response->assertOk()
        ->assertJsonPath('reviews_count', 1);

    expect($response->json('reviews.0.title'))->toBe('Great product');
});

test('pending reviews do not appear in reviews endpoint', function () {
    makeReview($this->product, $this->user, false);

    $response = $this->getJson('/product/' . $this->product->id . '/reviews');

    $response->assertOk()
        ->assertJsonPath('reviews_count', 0);

    expect($response->json('reviews'))->toHaveCount(0);
});
