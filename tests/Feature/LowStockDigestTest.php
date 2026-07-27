<?php

use App\Mail\LowStockDigestMail;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockAlert;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->category = Category::create([
        'name' => 'Digest Category',
        'slug' => 'digest-category',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Digest Product',
        'slug' => 'digest-product',
        'price' => 100,
        'stock' => 2,
        'sku' => 'DIGEST-1',
        'category_id' => $this->category->id,
        'status' => 'active',
    ]);
});

function makeDigestAlert(Product $product, bool $resolved = false): StockAlert
{
    return StockAlert::create([
        'product_id' => $product->id,
        'stock_at_alert' => $product->stock,
        'threshold' => 5,
        'is_resolved' => $resolved,
        'resolved_at' => $resolved ? now() : null,
    ]);
}

test('alerts:digest emails admins when there are open alerts', function () {
    Mail::fake();
    $admin = User::create([
        'name' => 'Digest Admin',
        'email' => 'digestadmin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    makeDigestAlert($this->product);

    $this->artisan('alerts:digest')->assertSuccessful();

    Mail::assertSent(LowStockDigestMail::class, function ($mail) use ($admin) {
        return $mail->hasTo($admin->email) && $mail->alerts->count() === 1;
    });
});

test('alerts:digest sends nothing when there are no open alerts', function () {
    Mail::fake();
    User::create([
        'name' => 'Digest Admin',
        'email' => 'digestadmin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    makeDigestAlert($this->product, true); // resolved only

    $this->artisan('alerts:digest')->assertSuccessful();

    Mail::assertNothingSent();
});

test('the digest email lists each low-stock product with its threshold', function () {
    makeDigestAlert($this->product);

    $mail = new LowStockDigestMail(app(\App\Services\StockAlertService::class)->openAlerts());

    $rendered = $mail->render();

    expect($rendered)->toContain('Digest Product');
    expect($rendered)->toContain('Low Stock Digest');
    expect($rendered)->toContain('DIGEST-1');
});
