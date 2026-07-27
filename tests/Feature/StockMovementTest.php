<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\InventoryService;

beforeEach(function () {
    $this->inventory = app(InventoryService::class);

    $this->admin = User::create([
        'name' => 'Stock Admin',
        'email' => 'stockadmin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->category = Category::create([
        'name' => 'Stock Category',
        'slug' => 'stock-category',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Tracked Product',
        'slug' => 'tracked-product',
        'price' => 300,
        'stock' => 10,
        'sku' => 'TRK-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);
});

// ─── Unit: InventoryService::logMovement ────────────────────────────

test('logMovement records a stock movement with all fields', function () {
    $movement = $this->inventory->logMovement(
        $this->product,
        'restock',
        20,
        10,
        30,
        $this->admin->id,
        'Admin restock',
    );

    expect($movement)->toBeInstanceOf(StockMovement::class)
        ->and($movement->product_id)->toBe($this->product->id)
        ->and($movement->user_id)->toBe($this->admin->id)
        ->and($movement->type)->toBe('restock')
        ->and($movement->quantity)->toBe(20)
        ->and($movement->stock_before)->toBe(10)
        ->and($movement->stock_after)->toBe(30)
        ->and($movement->note)->toBe('Admin restock');
});

test('logMovement allows null user and note', function () {
    $movement = $this->inventory->logMovement(
        $this->product,
        'sale',
        -2,
        10,
        8,
    );

    expect($movement->user_id)->toBeNull()
        ->and($movement->note)->toBeNull()
        ->and($movement->quantity)->toBe(-2);
});

test('movementsFor returns history for a product newest first', function () {
    $this->inventory->logMovement($this->product, 'restock', 5, 10, 15, $this->admin->id);
    $this->inventory->logMovement($this->product, 'sale', -3, 15, 12);

    $movements = $this->inventory->movementsFor($this->product);

    expect($movements)->toHaveCount(2)
        ->and($movements->first()->type)->toBe('sale')
        ->and($movements->last()->type)->toBe('restock');
});

// ─── Feature: Admin restock creates audit trail ─────────────────────

test('updating product stock creates a restock movement', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.products.update', $this->product), [
            'name' => 'Tracked Product',
            'description' => 'A test product',
            'price' => 300,
            'stock' => 25,
            'sku' => 'TRK-001',
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $this->product->id,
        'user_id' => $this->admin->id,
        'type' => 'restock',
        'quantity' => 15,
        'stock_before' => 10,
        'stock_after' => 25,
    ]);
});

test('decreasing product stock creates an adjustment movement', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.products.update', $this->product), [
            'name' => 'Tracked Product',
            'description' => 'A test product',
            'price' => 300,
            'stock' => 4,
            'sku' => 'TRK-001',
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $this->product->id,
        'type' => 'adjustment',
        'quantity' => -6,
        'stock_before' => 10,
        'stock_after' => 4,
    ]);
});

test('no movement is logged when stock does not change', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.products.update', $this->product), [
            'name' => 'Tracked Product',
            'description' => 'A test product',
            'price' => 350,
            'stock' => 10,
            'sku' => 'TRK-001',
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

    expect(StockMovement::count())->toBe(0);
});
