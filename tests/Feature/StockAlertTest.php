<?php

use App\Events\StockLow;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockAlert;
use App\Models\User;
use App\Services\ThemeService;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Alert User',
        'email' => 'alertuser@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->admin = User::create([
        'name' => 'Alert Admin',
        'email' => 'alertadmin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->category = Category::create([
        'name' => 'Alert Category',
        'slug' => 'alert-category',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Alert Product',
        'slug' => 'alert-product',
        'price' => 100,
        'stock' => 2,
        'sku' => 'ALERT-100',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);
});

function makeAlert(Product $product, bool $resolved = false): StockAlert
{
    return StockAlert::create([
        'product_id' => $product->id,
        'stock_at_alert' => $product->stock,
        'threshold' => 5,
        'is_resolved' => $resolved,
        'resolved_at' => $resolved ? now() : null,
    ]);
}

// ─── Event Integration ───────────────────────────────────────────

test('StockLow event persists a stock alert via the listener', function () {
    event(new StockLow($this->product));

    $this->assertDatabaseHas('stock_alerts', [
        'product_id' => $this->product->id,
        'is_resolved' => false,
    ]);
});

test('StockLow listener records the configured threshold from theme config', function () {
    $active = ThemeService::getActiveThemeSlug();
    config()->set("themes.themes.{$active}.inventory.low_stock_threshold", 8);
    app()->singleton(ThemeService::class, fn () => new ThemeService());

    event(new StockLow($this->product));

    $this->assertDatabaseHas('stock_alerts', [
        'product_id' => $this->product->id,
        'threshold' => 8,
        'is_resolved' => false,
    ]);
});

// ─── Access Control ──────────────────────────────────────────────

test('admin alerts index requires admin role', function () {
    $this->actingAs($this->user);
    $this->get('/admin/alerts')->assertStatus(403);
});

test('admin alerts index loads for admin user', function () {
    $this->actingAs($this->admin);
    makeAlert($this->product);

    $this->get('/admin/alerts')
        ->assertOk()
        ->assertSee('Alert Product');
});

// ─── Filtering ───────────────────────────────────────────────────

test('alerts default filter shows open only', function () {
    $this->actingAs($this->admin);
    makeAlert($this->product, false);
    makeAlert($this->product, true);

    $this->get('/admin/alerts')
        ->assertOk()
        ->assertSee('Open (1)')
        ->assertSee('Resolved (1)');
});

test('alerts can be filtered by resolved', function () {
    $this->actingAs($this->admin);
    makeAlert($this->product, true);

    $this->get('/admin/alerts?filter=resolved')
        ->assertOk()
        ->assertSee('Alert Product');
});

// ─── Actions ─────────────────────────────────────────────────────

test('admin can resolve an open alert', function () {
    $this->actingAs($this->admin);
    $alert = makeAlert($this->product, false);

    $this->post("/admin/alerts/{$alert->id}/resolve");

    expect($alert->fresh()->is_resolved)->toBeTrue();
    expect($alert->fresh()->resolved_at)->not->toBeNull();
});

test('admin can delete an alert', function () {
    $this->actingAs($this->admin);
    $alert = makeAlert($this->product);

    $this->delete("/admin/alerts/{$alert->id}");

    $this->assertDatabaseMissing('stock_alerts', ['id' => $alert->id]);
});

test('deleting a product cascades to its alerts', function () {
    $alert = makeAlert($this->product);

    $this->product->delete();

    $this->assertDatabaseMissing('stock_alerts', ['id' => $alert->id]);
});

// ─── Bulk Resolve ────────────────────────────────────────

test('admin can resolve all open alerts at once', function () {
    $this->actingAs($this->admin);

    $second = Product::create([
        'name' => 'Second Alert Product',
        'slug' => 'second-alert-product',
        'price' => 100,
        'stock' => 1,
        'sku' => 'ALERT-200',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    makeAlert($this->product, false);
    makeAlert($second, false);
    makeAlert($this->product, true); // already resolved

    $this->post('/admin/alerts/resolve-all');

    expect(StockAlert::unresolved()->count())->toBe(0);
    expect(StockAlert::resolved()->count())->toBe(3);
});

test('resolve all button shows only when open alerts exist', function () {
    $this->actingAs($this->admin);
    makeAlert($this->product, false);

    $this->get('/admin/alerts')
        ->assertOk()
        ->assertSee('Resolve All');
});

test('resolve all button is hidden when there are no open alerts', function () {
    $this->actingAs($this->admin);
    makeAlert($this->product, true);

    $this->get('/admin/alerts?filter=all')
        ->assertOk()
        ->assertDontSee('Resolve All');
});

test('restocking a product through the admin update auto-resolves its open alert', function () {
    $this->actingAs($this->admin);
    makeAlert($this->product); // open alert, threshold 5

    expect(StockAlert::unresolved()->where('product_id', $this->product->id)->count())->toBe(1);

    $this->put(route('admin.products.update', $this->product), [
        'name' => $this->product->name,
        'description' => 'Restocked and ready.',
        'price' => $this->product->price,
        'stock' => 40,
        'sku' => $this->product->sku,
        'status' => 'active',
        'category_id' => $this->category->id,
    ])->assertRedirect(route('admin.products.index'));

    expect($this->product->fresh()->stock)->toBe(40);
    expect(StockAlert::unresolved()->where('product_id', $this->product->id)->count())->toBe(0);
});

test('a restock that stays at or below threshold leaves the alert open', function () {
    $this->actingAs($this->admin);
    makeAlert($this->product); // open alert, threshold 5

    $this->put(route('admin.products.update', $this->product), [
        'name' => $this->product->name,
        'description' => 'Still low.',
        'price' => $this->product->price,
        'stock' => 4,
        'sku' => $this->product->sku,
        'status' => 'active',
        'category_id' => $this->category->id,
    ])->assertRedirect(route('admin.products.index'));

    expect(StockAlert::unresolved()->where('product_id', $this->product->id)->count())->toBe(1);
});
