<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\StockAlert;
use App\Models\User;
use App\Services\StockAlertService;

beforeEach(function () {
    $this->service = app(StockAlertService::class);

    $this->category = Category::create([
        'name' => 'Alert Category',
        'slug' => 'alert-category',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Alert Product',
        'slug' => 'alert-product',
        'price' => 100,
        'stock' => 3,
        'sku' => 'ALERT-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);
});

test('record creates a stock alert for a product', function () {
    $alert = $this->service->record($this->product, 5);

    expect($alert)->toBeInstanceOf(StockAlert::class);
    $this->assertDatabaseHas('stock_alerts', [
        'product_id' => $this->product->id,
        'stock_at_alert' => 3,
        'threshold' => 5,
        'is_resolved' => false,
    ]);
});

test('record refreshes existing unresolved alert instead of duplicating', function () {
    $first = $this->service->record($this->product, 5);

    $this->product->update(['stock' => 1]);
    $second = $this->service->record($this->product->fresh(), 5);

    expect($second->id)->toBe($first->id);
    expect(StockAlert::count())->toBe(1);
    expect($second->fresh()->stock_at_alert)->toBe(1);
});

test('record creates a new alert once the previous one is resolved', function () {
    $first = $this->service->record($this->product, 5);
    $this->service->resolve($first);

    $second = $this->service->record($this->product->fresh(), 5);

    expect($second->id)->not->toBe($first->id);
    expect(StockAlert::count())->toBe(2);
});

test('resolve marks alert resolved with timestamp', function () {
    $alert = $this->service->record($this->product, 5);

    $this->service->resolve($alert);

    expect($alert->fresh()->is_resolved)->toBeTrue();
    expect($alert->fresh()->resolved_at)->not->toBeNull();
});

test('counts returns unresolved resolved and all totals', function () {
    $open = $this->service->record($this->product, 5);

    $other = Product::create([
        'name' => 'Second Product',
        'slug' => 'second-product',
        'price' => 50,
        'stock' => 2,
        'sku' => 'ALERT-002',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);
    $resolved = $this->service->record($other, 5);
    $this->service->resolve($resolved);

    $counts = $this->service->counts();

    expect($counts['unresolved'])->toBe(1);
    expect($counts['resolved'])->toBe(1);
    expect($counts['all'])->toBe(2);
});

test('unresolvedCount returns only open alerts', function () {
    $this->service->record($this->product, 5);

    expect($this->service->unresolvedCount())->toBe(1);
});

test('list filters by unresolved by default', function () {
    $open = $this->service->record($this->product, 5);

    $other = Product::create([
        'name' => 'Resolved Product',
        'slug' => 'resolved-product',
        'price' => 50,
        'stock' => 2,
        'sku' => 'ALERT-003',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);
    $this->service->resolve($this->service->record($other, 5));

    $results = $this->service->list('unresolved');

    expect($results->total())->toBe(1);
    expect($results->first()->id)->toBe($open->id);
});

test('resolveAll resolves every open alert and returns the count', function () {
    $this->service->record($this->product, 5);

    $other = Product::create([
        'name' => 'Bulk Product',
        'slug' => 'bulk-product',
        'price' => 50,
        'stock' => 1,
        'sku' => 'ALERT-004',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);
    $this->service->record($other, 5);

    $resolved = $this->service->resolveAll();

    expect($resolved)->toBe(2);
    expect($this->service->unresolvedCount())->toBe(0);
});

test('resolveIfRestocked resolves the open alert once stock climbs above threshold', function () {
    $this->service->record($this->product, 5);

    $this->product->update(['stock' => 20]);

    $resolved = $this->service->resolveIfRestocked($this->product->fresh());

    expect($resolved)->not->toBeNull();
    expect($resolved->is_resolved)->toBeTrue();
    expect($this->service->unresolvedCount())->toBe(0);
});

test('resolveIfRestocked leaves the alert open when stock is still at or below threshold', function () {
    $this->service->record($this->product, 5);

    // Bump to exactly the threshold — still considered low (fires at <= 5).
    $this->product->update(['stock' => 5]);

    $resolved = $this->service->resolveIfRestocked($this->product->fresh());

    expect($resolved)->toBeNull();
    expect($this->service->unresolvedCount())->toBe(1);
});

test('resolveIfRestocked returns null when there is no open alert', function () {
    $this->product->update(['stock' => 50]);

    expect($this->service->resolveIfRestocked($this->product->fresh()))->toBeNull();
});

test('openAlerts returns only unresolved alerts with their product, newest first', function () {
    $older = $this->service->record($this->product, 5);

    $second = Product::create([
        'name' => 'Second Product',
        'slug' => 'second-product',
        'price' => 20,
        'stock' => 1,
        'sku' => 'ALERT-DIGEST',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);
    $newer = $this->service->record($second, 5);

    // A resolved alert must be excluded.
    $this->service->resolve($older);
    $reopened = $this->service->record($this->product, 5); // re-opens for $this->product

    $open = $this->service->openAlerts();

    expect($open)->toHaveCount(2);
    expect($open->first()->relationLoaded('product'))->toBeTrue();
    expect($open->pluck('id')->all())->not->toContain($older->id);
});

test('digestRecipients returns every admin email', function () {
    User::create(['name' => 'Admin One', 'email' => 'a1@test.com', 'password' => bcrypt('x'), 'role' => 'admin']);
    User::create(['name' => 'Admin Two', 'email' => 'a2@test.com', 'password' => bcrypt('x'), 'role' => 'admin']);
    User::create(['name' => 'Shopper', 'email' => 'shopper@test.com', 'password' => bcrypt('x'), 'role' => 'customer']);

    $recipients = $this->service->digestRecipients();

    expect($recipients)->toContain('a1@test.com', 'a2@test.com');
    expect($recipients)->not->toContain('shopper@test.com');
});
