<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Product Admin',
        'email' => 'productadmin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->category = Category::create([
        'name' => 'Search Category',
        'slug' => 'search-category',
        'status' => 'active',
    ]);
});

function makeProduct(Category $category, string $name, string $sku): Product
{
    return Product::create([
        'name' => $name,
        'slug' => \Illuminate\Support\Str::slug($name) . '-' . uniqid(),
        'price' => 500,
        'stock' => 10,
        'sku' => $sku,
        'category_id' => $category->id,
        'status' => 'active',
    ]);
}

test('admin can search products by name', function () {
    makeProduct($this->category, 'Beard Oil Supreme', 'SKU-001');
    makeProduct($this->category, 'Face Wash Classic', 'SKU-002');

    $this->actingAs($this->admin)
        ->get(route('admin.products.index', ['q' => 'Beard']))
        ->assertOk()
        ->assertSee('Beard Oil Supreme')
        ->assertDontSee('Face Wash Classic');
});

test('admin can search products by SKU', function () {
    makeProduct($this->category, 'Beard Oil Supreme', 'SKU-BEARD');
    makeProduct($this->category, 'Face Wash Classic', 'SKU-FACE');

    $this->actingAs($this->admin)
        ->get(route('admin.products.index', ['q' => 'SKU-BEARD']))
        ->assertOk()
        ->assertSee('Beard Oil Supreme')
        ->assertDontSee('Face Wash Classic');
});

test('admin product search with no results shows the empty state', function () {
    makeProduct($this->category, 'Beard Oil Supreme', 'SKU-001');

    $this->actingAs($this->admin)
        ->get(route('admin.products.index', ['q' => 'NOMATCH']))
        ->assertOk()
        ->assertSee('No products found for "NOMATCH"');
});

test('admin products index lists all products without a search query', function () {
    makeProduct($this->category, 'Beard Oil Supreme', 'SKU-001');
    makeProduct($this->category, 'Face Wash Classic', 'SKU-002');

    $this->actingAs($this->admin)
        ->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee('Beard Oil Supreme')
        ->assertSee('Face Wash Classic');
});
