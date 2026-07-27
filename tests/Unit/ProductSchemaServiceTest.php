<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Services\ProductSchemaService;

beforeEach(function () {
    $this->service = app(ProductSchemaService::class);
    $this->siteSlug = theme('slug', 'default');

    $this->category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Schema Test Product',
        'slug' => 'schema-test-product',
        'description' => 'Test',
        'price' => 500,
        'stock' => 10,
        'sku' => 'STP-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);
});

function createAttribute(string $siteSlug, array $overrides = []): ProductAttribute
{
    return ProductAttribute::create(array_merge([
        'site_slug' => $siteSlug,
        'code' => 'fabric',
        'label' => 'Fabric',
        'type' => 'select',
        'options' => ['Cotton', 'Silk'],
        'is_required' => false,
        'is_filterable' => true,
        'sort_order' => 0,
    ], $overrides));
}

test('sync creates attributes from site config', function () {
    $this->service->syncSchemaToDatabase('badlab');

    $attributes = ProductAttribute::forSite('badlab')->get();

    expect($attributes)->not->toBeEmpty()
        ->and($attributes->pluck('code'))->toContain('ingredients')
        ->and($attributes->where('code', 'scent')->first()->options)->toBeArray();
});

test('sync is idempotent', function () {
    $this->service->syncSchemaToDatabase('badlab');
    $countFirst = ProductAttribute::forSite('badlab')->count();

    $this->service->syncSchemaToDatabase('badlab');
    $countSecond = ProductAttribute::forSite('badlab')->count();

    expect($countSecond)->toBe($countFirst);
});

test('get attributes returns only for requested site in sort order', function () {
    createAttribute('site-a', ['code' => 'alpha', 'sort_order' => 1]);
    createAttribute('site-a', ['code' => 'beta', 'sort_order' => 0]);
    createAttribute('site-b', ['code' => 'gamma', 'sort_order' => 0]);

    $attributes = $this->service->getAttributes('site-a');

    expect($attributes->pluck('code')->all())->toBe(['beta', 'alpha']);
});

test('get filterable attributes excludes non-filterable', function () {
    createAttribute('site-a', ['code' => 'filterable_one', 'is_filterable' => true]);
    createAttribute('site-a', ['code' => 'hidden_one', 'is_filterable' => false, 'sort_order' => 1]);

    $filterable = $this->service->getFilterableAttributes('site-a');

    expect($filterable->pluck('code')->all())->toBe(['filterable_one']);
});

test('save product attributes stores values keyed by code', function () {
    $attr = createAttribute($this->siteSlug, ['code' => 'origin', 'type' => 'text', 'options' => null]);

    $this->service->saveProductAttributes($this->product, ['origin' => 'Dhaka']);

    $stored = ProductAttributeValue::where('product_id', $this->product->id)
        ->where('product_attribute_id', $attr->id)
        ->first();

    expect($stored->value)->toBe('Dhaka');
});

test('save product attributes json encodes array values', function () {
    $attr = createAttribute($this->siteSlug, ['code' => 'occasion', 'type' => 'multiselect']);

    $this->service->saveProductAttributes($this->product, ['occasion' => ['Casual', 'Festive']]);

    $stored = ProductAttributeValue::where('product_id', $this->product->id)
        ->where('product_attribute_id', $attr->id)
        ->first();

    expect(json_decode($stored->value, true))->toBe(['Casual', 'Festive']);
});

test('save product attributes ignores unknown codes', function () {
    createAttribute($this->siteSlug, ['code' => 'origin', 'type' => 'text', 'options' => null]);

    $this->service->saveProductAttributes($this->product, [
        'origin' => 'Dhaka',
        'nonexistent_code' => 'should be ignored',
    ]);

    expect(ProductAttributeValue::where('product_id', $this->product->id)->count())->toBe(1);
});

test('save product attributes updates existing values', function () {
    $attr = createAttribute($this->siteSlug, ['code' => 'origin', 'type' => 'text', 'options' => null]);

    $this->service->saveProductAttributes($this->product, ['origin' => 'Dhaka']);
    $this->service->saveProductAttributes($this->product, ['origin' => 'Narayanganj']);

    $count = ProductAttributeValue::where('product_id', $this->product->id)
        ->where('product_attribute_id', $attr->id)
        ->count();
    $value = ProductAttributeValue::where('product_id', $this->product->id)
        ->where('product_attribute_id', $attr->id)
        ->first()->value;

    expect($count)->toBe(1)
        ->and($value)->toBe('Narayanganj');
});

test('get product attributes returns keyed array with metadata', function () {
    createAttribute($this->siteSlug, ['code' => 'origin', 'label' => 'Origin', 'type' => 'text', 'options' => null]);
    $this->service->saveProductAttributes($this->product, ['origin' => 'Dhaka']);

    $result = $this->service->getProductAttributes($this->product);

    expect($result)->toHaveKey('origin')
        ->and($result['origin']['label'])->toBe('Origin')
        ->and($result['origin']['type'])->toBe('text')
        ->and($result['origin']['value'])->toBe('Dhaka');
});

test('get product attributes returns empty array for product without values', function () {
    expect($this->service->getProductAttributes($this->product))->toBe([]);
});
