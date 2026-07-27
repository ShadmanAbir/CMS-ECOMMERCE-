<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\PricingService;

beforeEach(function () {
    $this->service = app(PricingService::class);

    $this->category = Category::create([
        'name' => 'Pricing Category',
        'slug' => 'pricing-category',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Pricing Product',
        'slug' => 'pricing-product',
        'description' => 'Test',
        'price' => 1000,
        'discount' => 0,
        'stock' => 50,
        'sku' => 'PRC-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);
});

/** Override pricing config for the active site and rebuild the ThemeService singleton. */
function setSitePricing(array $overrides): void
{
    $slug = theme('slug', 'default');
    foreach ($overrides as $key => $value) {
        config(["themes.themes.{$slug}.pricing.{$key}" => $value]);
    }
    // ThemeService is a singleton that caches config at construction — force rebuild
    app()->forgetInstance(\App\Services\ThemeService::class);
}

// ─── Product Price ──────────────────────────────────────────────

test('product price returned as-is without discount', function () {
    expect($this->service->getProductPrice($this->product))->toBe(1000.0);
});

test('product price applies percentage discount', function () {
    $this->product->update(['discount' => 10]);

    expect($this->service->getProductPrice($this->product))->toBe(900.0);
});

test('product price rounds to two decimals', function () {
    $this->product->update(['price' => 999, 'discount' => 15]);

    // 999 - 149.85 = 849.15
    expect($this->service->getProductPrice($this->product))->toBe(849.15);
});

// ─── Variant Price ──────────────────────────────────────────────

test('variant price uses variant own price', function () {
    $variant = ProductVariant::create([
        'product_id' => $this->product->id,
        'name' => 'Deluxe',
        'attributes' => ['tier' => 'deluxe'],
        'price' => 1500,
        'stock' => 10,
        'is_active' => true,
    ]);

    expect($this->service->getVariantPrice($variant))->toBe(1500.0);
});

test('variant price falls back to product price when null', function () {
    $variant = ProductVariant::create([
        'product_id' => $this->product->id,
        'name' => 'Base',
        'attributes' => ['tier' => 'base'],
        'price' => null,
        'stock' => 10,
        'is_active' => true,
    ]);

    expect($this->service->getVariantPrice($variant))->toBe(1000.0);
});

test('variant price applies product discount', function () {
    $this->product->update(['discount' => 20]);

    $variant = ProductVariant::create([
        'product_id' => $this->product->id,
        'name' => 'Deluxe',
        'attributes' => ['tier' => 'deluxe'],
        'price' => 500,
        'stock' => 10,
        'is_active' => true,
    ]);

    expect($this->service->getVariantPrice($variant))->toBe(400.0);
});

test('item price delegates to variant when provided', function () {
    $variant = ProductVariant::create([
        'product_id' => $this->product->id,
        'name' => 'Deluxe',
        'attributes' => ['tier' => 'deluxe'],
        'price' => 2000,
        'stock' => 10,
        'is_active' => true,
    ]);

    expect($this->service->getItemPrice($this->product, $variant))->toBe(2000.0)
        ->and($this->service->getItemPrice($this->product))->toBe(1000.0);
});

// ─── Totals, Tax & Shipping ─────────────────────────────────────

test('calculate totals sums items with zero tax and free shipping by default', function () {
    setSitePricing(['tax_rate' => 0, 'shipping_cost' => 0, 'free_shipping_threshold' => null]);

    $user = User::create(['name' => 'P', 'email' => 'p@t.com', 'password' => bcrypt('x')]);
    $item = Cart::create([
        'user_id' => $user->id,
        'product_id' => $this->product->id,
        'quantity' => 3,
        'price' => 1000,
    ]);

    $totals = $this->service->calculateTotals(collect([$item]));

    expect($totals['subtotal'])->toBe(3000.0)
        ->and($totals['vat_amount'])->toBe(0.0)
        ->and($totals['shipping_amount'])->toBe(0.0)
        ->and($totals['payable_amount'])->toBe(3000.0);
});

test('calculate totals applies configured tax rate', function () {
    setSitePricing(['tax_rate' => 0.05, 'shipping_cost' => 0, 'free_shipping_threshold' => null]);

    $user = User::create(['name' => 'P', 'email' => 'p@t.com', 'password' => bcrypt('x')]);
    $item = Cart::create([
        'user_id' => $user->id,
        'product_id' => $this->product->id,
        'quantity' => 2,
        'price' => 1000,
    ]);

    $totals = $this->service->calculateTotals(collect([$item]));

    // subtotal 2000, tax 5% = 100
    expect($totals['vat_amount'])->toBe(100.0)
        ->and($totals['payable_amount'])->toBe(2100.0);
});

test('calculate totals adds flat shipping cost', function () {
    setSitePricing(['tax_rate' => 0, 'shipping_cost' => 60, 'free_shipping_threshold' => null]);

    $user = User::create(['name' => 'P', 'email' => 'p@t.com', 'password' => bcrypt('x')]);
    $item = Cart::create([
        'user_id' => $user->id,
        'product_id' => $this->product->id,
        'quantity' => 1,
        'price' => 1000,
    ]);

    $totals = $this->service->calculateTotals(collect([$item]));

    expect($totals['shipping_amount'])->toBe(60.0)
        ->and($totals['payable_amount'])->toBe(1060.0);
});

test('shipping is free above configured threshold', function () {
    setSitePricing(['tax_rate' => 0, 'shipping_cost' => 60, 'free_shipping_threshold' => 500]);

    expect($this->service->getShippingCost(600))->toBe(0.0)
        ->and($this->service->getShippingCost(400))->toBe(60.0);
});

test('tax rate reads from site config', function () {
    setSitePricing(['tax_rate' => 0.075]);

    expect($this->service->getTaxRate())->toBe(0.075);
});
