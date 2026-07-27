<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\ProductReview;
use App\Models\ProductImage;

beforeEach(function () {
    $this->category = Category::create([
        'name' => 'Skin Care',
        'slug' => 'skin-care',
        'status' => 'active',
    ]);
});

test('product can be created', function () {
    $product = Product::create([
        'name' => 'Test Face Wash',
        'slug' => 'test-face-wash',
        'description' => 'A great face wash',
        'price' => 500.00,
        'discount' => 10,
        'stock' => 100,
        'sku' => 'TFW-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    expect($product)->toBeInstanceOf(Product::class);
    expect($product->slug)->toBe('test-face-wash');
});

test('product slug auto-generated from name', function () {
    $product = Product::create([
        'name' => 'Auto Slug Product',
        'description' => 'Test',
        'price' => 100,
        'stock' => 10,
        'sku' => 'ASP-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    expect($product->slug)->toBe('auto-slug-product');
});

test('discounted price calculated correctly', function () {
    $product = Product::create([
        'name' => 'Discounted Product',
        'slug' => 'discounted-product',
        'description' => 'Test',
        'price' => 1000,
        'discount' => 25,
        'stock' => 50,
        'sku' => 'DP-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    expect($product->getDiscountedPrice())->toEqual(750.0);
});

test('discounted price equals regular price when no discount', function () {
    $product = Product::create([
        'name' => 'No Discount Product',
        'slug' => 'no-discount-product',
        'description' => 'Test',
        'price' => 500,
        'discount' => 0,
        'stock' => 50,
        'sku' => 'NDP-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    expect($product->getDiscountedPrice())->toEqual(500.0);
});

test('product stock check works', function () {
    $inStock = Product::create([
        'name' => 'In Stock',
        'slug' => 'in-stock',
        'description' => 'Test',
        'price' => 100,
        'stock' => 10,
        'sku' => 'IS-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    $outOfStock = Product::create([
        'name' => 'Out of Stock',
        'slug' => 'out-of-stock',
        'description' => 'Test',
        'price' => 100,
        'stock' => 0,
        'sku' => 'OOS-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    expect($inStock->isInStock())->toBeTrue();
    expect($outOfStock->isInStock())->toBeFalse();
});

test('product total stock returns correct value', function () {
    $product = Product::create([
        'name' => 'Stock Test',
        'slug' => 'stock-test',
        'description' => 'Test',
        'price' => 100,
        'stock' => 50,
        'sku' => 'ST-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    expect($product->totalStock())->toBe(50);
});

test('active scope filters correctly', function () {
    Product::create([
        'name' => 'Active Product',
        'slug' => 'active-product',
        'description' => 'Test',
        'price' => 100,
        'stock' => 10,
        'sku' => 'AP-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    Product::create([
        'name' => 'Draft Product',
        'slug' => 'draft-product',
        'description' => 'Test',
        'price' => 100,
        'stock' => 10,
        'sku' => 'DP-002',
        'category_id' => $this->category->id,
        'status' => 'draft',
    ]);

    expect(Product::active()->count())->toBe(1);
    expect(Product::active()->first()->name)->toBe('Active Product');
});

test('search scope finds products by name', function () {
    Product::create([
        'name' => 'Salicylic Face Wash',
        'slug' => 'salicylic-face-wash',
        'description' => 'For acne',
        'price' => 300,
        'stock' => 10,
        'sku' => 'SFW-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    Product::create([
        'name' => 'Moisturizer',
        'slug' => 'moisturizer',
        'description' => 'Hydrating',
        'price' => 400,
        'stock' => 10,
        'sku' => 'M-001',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);

    $results = Product::active()->search('Salicylic')->get();
    expect($results->count())->toBe(1);
    expect($results->first()->name)->toBe('Salicylic Face Wash');
});

test('trending featured and new arrivals scopes work', function () {
    Product::create([
        'name' => 'Trending', 'slug' => 'trending', 'description' => 'Test',
        'price' => 100, 'stock' => 10, 'sku' => 'TR-001',
        'category_id' => $this->category->id, 'status' => 'active', 'is_trending' => true,
    ]);

    Product::create([
        'name' => 'Featured', 'slug' => 'featured', 'description' => 'Test',
        'price' => 100, 'stock' => 10, 'sku' => 'F-001',
        'category_id' => $this->category->id, 'status' => 'active', 'is_featured' => true,
    ]);

    Product::create([
        'name' => 'New', 'slug' => 'new', 'description' => 'Test',
        'price' => 100, 'stock' => 10, 'sku' => 'N-001',
        'category_id' => $this->category->id, 'status' => 'active', 'is_new' => true,
    ]);

    expect(Product::trending()->count())->toBe(1);
    expect(Product::featured()->count())->toBe(1);
    expect(Product::newArrivals()->count())->toBe(1);
});

test('on sale scope filters discounted products', function () {
    Product::create([
        'name' => 'On Sale', 'slug' => 'on-sale', 'description' => 'Test',
        'price' => 100, 'discount' => 20, 'stock' => 10, 'sku' => 'OS-001',
        'category_id' => $this->category->id, 'status' => 'active',
    ]);

    Product::create([
        'name' => 'Full Price', 'slug' => 'full-price', 'description' => 'Test',
        'price' => 100, 'discount' => 0, 'stock' => 10, 'sku' => 'FP-001',
        'category_id' => $this->category->id, 'status' => 'active',
    ]);

    expect(Product::onSale()->count())->toBe(1);
    expect(Product::onSale()->first()->name)->toBe('On Sale');
});

test('product has variants relationship', function () {
    $product = Product::create([
        'name' => 'Variant Product', 'slug' => 'variant-product', 'description' => 'Test',
        'price' => 500, 'stock' => 0, 'sku' => 'VP-001',
        'category_id' => $this->category->id, 'status' => 'active',
    ]);

    ProductVariant::create([
        'product_id' => $product->id, 'name' => '100ml Matte',
        'attributes' => ['size' => '100ml', 'finish' => 'Matte'],
        'price' => 550, 'stock' => 20, 'is_active' => true,
    ]);

    ProductVariant::create([
        'product_id' => $product->id, 'name' => '200ml Glossy',
        'attributes' => ['size' => '200ml', 'finish' => 'Glossy'],
        'price' => 750, 'stock' => 15, 'is_active' => true,
    ]);

    expect($product->hasVariants())->toBeTrue();
    expect($product->activeVariants()->count())->toBe(2);
    expect($product->isInStock())->toBeTrue();
    expect($product->totalStock())->toBe(35);
});

test('product without variants uses base stock', function () {
    $product = Product::create([
        'name' => 'Simple Product', 'slug' => 'simple-product', 'description' => 'Test',
        'price' => 300, 'stock' => 25, 'sku' => 'SP-001',
        'category_id' => $this->category->id, 'status' => 'active',
    ]);

    expect($product->hasVariants())->toBeFalse();
    expect($product->isInStock())->toBeTrue();
    expect($product->totalStock())->toBe(25);
});

test('average rating accessor works', function () {
    $product = Product::create([
        'name' => 'Rated Product', 'slug' => 'rated-product', 'description' => 'Test',
        'price' => 500, 'stock' => 10, 'sku' => 'RP-001',
        'category_id' => $this->category->id, 'status' => 'active',
    ]);

    $user = User::create(['name' => 'R1', 'email' => 'r1@test.com', 'password' => bcrypt('password')]);
    $user2 = User::create(['name' => 'R2', 'email' => 'r2@test.com', 'password' => bcrypt('password')]);

    ProductReview::create(['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 4, 'title' => 'Great', 'is_approved' => true]);
    ProductReview::create(['product_id' => $product->id, 'user_id' => $user2->id, 'rating' => 5, 'title' => 'Amazing', 'is_approved' => true]);

    expect($product->average_rating)->toEqual(4.5);
    expect($product->reviews_count)->toBe(2);
});

test('unapproved reviews not counted in average', function () {
    $product = Product::create([
        'name' => 'Partial Reviews', 'slug' => 'partial-reviews', 'description' => 'Test',
        'price' => 500, 'stock' => 10, 'sku' => 'PR-001',
        'category_id' => $this->category->id, 'status' => 'active',
    ]);

    $user = User::create(['name' => 'U', 'email' => 'u@test.com', 'password' => bcrypt('password')]);

    ProductReview::create(['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 5, 'is_approved' => true]);
    ProductReview::create(['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 1, 'is_approved' => false]);

    expect($product->average_rating)->toEqual(5.0);
    expect($product->reviews_count)->toBe(1);
});

test('views count increments', function () {
    $product = Product::create([
        'name' => 'Viewed Product', 'slug' => 'viewed-product', 'description' => 'Test',
        'price' => 100, 'stock' => 10, 'sku' => 'VPT-001',
        'category_id' => $this->category->id, 'status' => 'active', 'views_count' => 0,
    ]);

    $product->incrementViewsCount();
    expect($product->fresh()->views_count)->toBe(1);

    $product->incrementViewsCount();
    expect($product->fresh()->views_count)->toBe(2);
});

test('variant options extracted correctly', function () {
    $product = Product::create([
        'name' => 'Multi Variant', 'slug' => 'multi-variant', 'description' => 'Test',
        'price' => 500, 'stock' => 0, 'sku' => 'MV-001',
        'category_id' => $this->category->id, 'status' => 'active',
    ]);

    $v1 = ProductVariant::create([
        'product_id' => $product->id, 'name' => 'Small Red',
        'attributes' => ['size' => 'Small', 'color' => 'Red'],
        'price' => 500, 'stock' => 10, 'is_active' => true,
    ]);

    $v2 = ProductVariant::create([
        'product_id' => $product->id, 'name' => 'Large Blue',
        'attributes' => ['size' => 'Large', 'color' => 'Blue'],
        'price' => 600, 'stock' => 10, 'is_active' => true,
    ]);

    // Verify variants exist and have correct attributes
    $variants = $product->fresh()->activeVariants()->get();
    expect($variants->count())->toBe(2);

    $options = $product->fresh()->getVariantOptions();
    expect($options)->toHaveKeys(['size', 'color']);
    expect($options['size'])->toContain('Small', 'Large');
    expect($options['color'])->toContain('Red', 'Blue');
});
