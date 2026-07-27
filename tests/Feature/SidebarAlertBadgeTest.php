<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\StockAlert;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Badge Admin',
        'email' => 'badgeadmin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $category = Category::create([
        'name' => 'Badge Category',
        'slug' => 'badge-category',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Badge Product',
        'slug' => 'badge-product',
        'description' => 'Test',
        'price' => 300,
        'stock' => 2,
        'sku' => 'BADGE-001',
        'category_id' => $category->id,
        'status' => 'active',
    ]);
});

function seedStockAlert(Product $product, bool $resolved = false): StockAlert
{
    return StockAlert::create([
        'product_id' => $product->id,
        'stock_at_alert' => 2,
        'threshold' => 5,
        'is_resolved' => $resolved,
        'resolved_at' => $resolved ? now() : null,
    ]);
}

test('admin sidebar shows the unresolved alert count badge', function () {
    seedStockAlert($this->product);
    seedStockAlert($this->product);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('bg-red-500', false)
        ->assertSee('>2<', false);
});

test('admin sidebar hides the badge when there are no unresolved alerts', function () {
    seedStockAlert($this->product, resolved: true);

    $response = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();

    // The red badge markup should not be emitted for the alerts nav link.
    expect($response->getContent())->not->toContain('rounded-full bg-red-500');
});
