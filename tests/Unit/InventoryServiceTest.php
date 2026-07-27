<?php

use App\Events\StockLow;
use App\Exceptions\InsufficientStockException;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\ThemeService;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->service = app(InventoryService::class);

    $this->user = User::create([
        'name' => 'Inventory Tester',
        'email' => 'inventory@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->category = Category::create([
        'name' => 'Stock Category',
        'slug' => 'stock-category',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Stock Product',
        'slug' => 'stock-product',
        'description' => 'Test',
        'price' => 300,
        'stock' => 20,
        'sku' => 'STK-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);
});

function makeCartItem(Product $product, int $quantity, array $extra = []): Cart
{
    return Cart::create(array_merge([
        'user_id' => 1,
        'product_id' => $product->id,
        'quantity' => $quantity,
        'price' => $product->price,
    ], $extra));
}

// ─── Available Stock Resolution ─────────────────────────────────

test('available stock uses base product stock when no variants or batches', function () {
    expect($this->service->getAvailableStock($this->product))->toBe(20);
});

test('available stock sums variant stock when variants exist', function () {
    ProductVariant::create([
        'product_id' => $this->product->id,
        'name' => 'Small',
        'attributes' => ['size' => 'S'],
        'price' => 300,
        'stock' => 7,
        'is_active' => true,
    ]);
    ProductVariant::create([
        'product_id' => $this->product->id,
        'name' => 'Large',
        'attributes' => ['size' => 'L'],
        'price' => 350,
        'stock' => 3,
        'is_active' => true,
    ]);

    expect($this->service->getAvailableStock($this->product))->toBe(10);
});

test('available stock uses batch stock when no variants but batches exist', function () {
    $this->product->update(['stock' => 0]);

    ProductStock::create([
        'product_id' => $this->product->id,
        'batch_code' => 'B-100',
        'quantity' => 15,
        'purchased_at' => now(),
        'expire_at' => now()->addYear(),
    ]);

    expect($this->service->getAvailableStock($this->product))->toBe(15);
});

test('is in stock returns false when stock is zero', function () {
    $this->product->update(['stock' => 0]);

    expect($this->service->isInStock($this->product))->toBeFalse();
});

test('is in stock returns true when any variant has stock', function () {
    ProductVariant::create([
        'product_id' => $this->product->id,
        'name' => 'Empty',
        'attributes' => ['size' => 'S'],
        'price' => 300,
        'stock' => 0,
        'is_active' => true,
    ]);
    ProductVariant::create([
        'product_id' => $this->product->id,
        'name' => 'Full',
        'attributes' => ['size' => 'L'],
        'price' => 300,
        'stock' => 5,
        'is_active' => true,
    ]);

    expect($this->service->isInStock($this->product))->toBeTrue();
});

// ─── Validation ─────────────────────────────────────────────────

test('validate stock passes when sufficient', function () {
    $item = makeCartItem($this->product, 5);

    $this->service->validateStock($item);

    expect(true)->toBeTrue();
});

test('validate stock throws insufficient stock exception', function () {
    $item = makeCartItem($this->product, 25);

    $this->service->validateStock($item);
})->throws(InsufficientStockException::class);

test('validate stock checks variant stock for variant items', function () {
    $variant = ProductVariant::create([
        'product_id' => $this->product->id,
        'name' => 'Small',
        'attributes' => ['size' => 'S'],
        'price' => 300,
        'stock' => 2,
        'is_active' => true,
    ]);

    $item = makeCartItem($this->product, 5, ['product_variant_id' => $variant->id]);

    $this->service->validateStock($item);
})->throws(InsufficientStockException::class);

test('validate cart stock validates all items', function () {
    $okItem = makeCartItem($this->product, 2);
    $badItem = makeCartItem($this->product, 99);

    $this->service->validateCartStock(collect([$okItem, $badItem]));
})->throws(InsufficientStockException::class);

// ─── Decrement & Restore ────────────────────────────────────────

test('decrement stock reduces product stock', function () {
    $item = makeCartItem($this->product, 4);

    $this->service->decrementStock($item);

    expect($this->product->fresh()->stock)->toBe(16);
});

test('decrement stock reduces variant stock for variant items', function () {
    $variant = ProductVariant::create([
        'product_id' => $this->product->id,
        'name' => 'Small',
        'attributes' => ['size' => 'S'],
        'price' => 300,
        'stock' => 10,
        'is_active' => true,
    ]);

    $item = makeCartItem($this->product, 3, ['product_variant_id' => $variant->id]);

    $this->service->decrementStock($item);

    expect($variant->fresh()->stock)->toBe(7);
});

test('decrement stock fires stock low event at threshold', function () {
    Event::fake([StockLow::class]);

    $this->product->update(['stock' => 6]);
    $item = makeCartItem($this->product, 2); // 6 - 2 = 4 <= threshold 5

    $this->service->decrementStock($item);

    Event::assertDispatched(StockLow::class);
});

test('decrement stock does not fire event above threshold', function () {
    Event::fake([StockLow::class]);

    $item = makeCartItem($this->product, 2); // 20 - 2 = 18 > threshold 5

    $this->service->decrementStock($item);

    Event::assertNotDispatched(StockLow::class);
});

test('restore stock adds quantity back to product', function () {
    $this->product->update(['stock' => 10]);

    $orderItem = new \App\Models\OrderItem([
        'product_id' => $this->product->id,
        'quantity' => 5,
    ]);

    $this->service->restoreStock($orderItem);

    expect($this->product->fresh()->stock)->toBe(15);
});

test('lowStockThreshold reads from the site theme config', function () {
    $active = \App\Services\ThemeService::getActiveThemeSlug();
    config()->set("themes.themes.{$active}.inventory.low_stock_threshold", 12);
    app()->singleton(ThemeService::class, fn () => new ThemeService());

    expect($this->service->lowStockThreshold())->toBe(12);
});

test('lowStockThreshold defaults to 5 when not configured', function () {
    $active = \App\Services\ThemeService::getActiveThemeSlug();
    config()->set("themes.themes.{$active}.inventory", null);
    app()->singleton(ThemeService::class, fn () => new ThemeService());

    expect($this->service->lowStockThreshold())->toBe(5);
});

test('StockLow event respects the configured threshold', function () {
    Event::fake([StockLow::class]);

    // Raise threshold to 10 — stock dropping to 10 should now fire.
    $active = \App\Services\ThemeService::getActiveThemeSlug();
    config()->set("themes.themes.{$active}.inventory.low_stock_threshold", 10);
    app()->singleton(ThemeService::class, fn () => new ThemeService());

    $this->product->update(['stock' => 12]);

    $item = Cart::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'quantity' => 2,
        'price' => $this->product->price,
    ]);
    $item->load('product');

    $this->service->decrementStock($item);

    expect($this->product->fresh()->stock)->toBe(10);
    Event::assertDispatched(StockLow::class);
});
