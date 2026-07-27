<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shop\ProductController as ShopProductController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\ReviewController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\StockAlertController as AdminStockAlertController;
use App\Http\Controllers\Superadmin\ThemeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Homepage
Route::get('/', [ShopProductController::class, 'index'])->name('home');

// Product routes
Route::get('/product/{slug}', [ShopProductController::class, 'show'])->name('product.show');
Route::get('/search', [ShopProductController::class, 'search'])->name('product.search');
Route::get('/live-search', [ShopProductController::class, 'liveSearch'])->middleware('throttle:search')->name('product.live-search');
Route::get('/category/{slug}', [ShopProductController::class, 'byCategory'])->name('category.show');

// Product review routes
Route::get('/product/{product}/reviews', [ReviewController::class, 'index'])->name('product.reviews');
Route::post('/product/{product}/reviews', [ReviewController::class, 'store'])->name('product.reviews.store');

// Cart routes
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::put('/update/{id}', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{id}', [CartController::class, 'remove'])->name('remove');
    Route::post('/coupon/apply', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::delete('/coupon/remove', [CartController::class, 'removeCoupon'])->name('coupon.remove');
});

// Authentication routes
Route::middleware(['guest', 'throttle:auth'])->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Social Login routes
    Route::get('auth/{provider}/redirect', [SocialLoginController::class, 'redirect'])->name('social.redirect');
    Route::get('auth/{provider}/callback', [SocialLoginController::class, 'callback'])->name('social.callback');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Protected routes (requires authentication)
Route::middleware('auth')->group(function () {
    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout.store');
    
    // User dashboard
    Route::get('/my-orders', [UserDashboardController::class, 'orders'])->name('user.orders');
    Route::get('/my-orders/{order_number}', [UserDashboardController::class, 'orderDetails'])->name('user.order.details');
    Route::post('/my-orders/{order_number}/cancel', [UserDashboardController::class, 'cancelOrder'])->name('user.order.cancel');
    Route::get('/profile', [UserDashboardController::class, 'profile'])->name('user.profile');
    Route::put('/profile', [UserDashboardController::class, 'updateProfile'])->name('user.profile.update');
    
    // Wishlist
    Route::get('/wishlist', [UserDashboardController::class, 'wishlist'])->name('user.wishlist');
    Route::post('/wishlist/toggle', [UserDashboardController::class, 'toggleWishlist'])->name('user.wishlist.toggle');
});

// Payment callback routes
Route::post('/payment/callback/{method}', [CheckoutController::class, 'paymentCallback'])
    ->name('payment.callback');

// Payment success/cancel
Route::get('/payment/success/{order_number}', [CheckoutController::class, 'paymentSuccess'])
    ->name('payment.success');
Route::get('/payment/cancel/{order_number}', [CheckoutController::class, 'paymentCancel'])
    ->name('payment.cancel');

// Newsletter subscription
Route::post('/newsletter/subscribe', [ShopProductController::class, 'newsletterSubscribe'])->name('newsletter.subscribe');

// Placeholder routes (UI only - no backend logic)
Route::view('/vendors', 'placeholder.vendors')->name('vendors');
Route::view('/coupons', 'placeholder.coupons')->name('coupons');
Route::view('/blogs', 'placeholder.blog')->name('blogs');
Route::view('/about', 'placeholder.about')->name('about');
Route::view('/contact', 'placeholder.contact')->name('contact');
Route::view('/faq', 'placeholder.faq')->name('faq');

// Admin routes (requires admin authentication)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Admin Banners
    Route::resource('banners', BannerController::class);
    Route::post('banners/{banner}/toggle-status', [BannerController::class, 'toggleStatus'])->name('banners.toggle-status');
    
    // Admin Categories
    Route::resource('categories', CategoryController::class);
    
    // Admin Products
    Route::resource('products', AdminProductController::class);
    Route::delete('product-images/{image}', [AdminProductController::class, 'deleteImage'])->name('product-images.destroy');

    // Admin Orders
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');

    // Admin Coupons
    Route::resource('coupons', AdminCouponController::class)->except(['show']);
    Route::post('coupons/{coupon}/toggle-status', [AdminCouponController::class, 'toggleStatus'])->name('coupons.toggle-status');

    // Admin Reviews
    Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::post('reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');
    Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    // Admin Inventory Alerts
    Route::get('alerts', [AdminStockAlertController::class, 'index'])->name('alerts.index');
    Route::post('alerts/resolve-all', [AdminStockAlertController::class, 'resolveAll'])->name('alerts.resolve-all');
    Route::post('alerts/{alert}/resolve', [AdminStockAlertController::class, 'resolve'])->name('alerts.resolve');
    Route::delete('alerts/{alert}', [AdminStockAlertController::class, 'destroy'])->name('alerts.destroy');
});

// Superadmin site management
Route::prefix('superadmin')->middleware(['auth', 'admin'])->name('superadmin.')->group(function () {
    // New site-based routes
    Route::get('/sites', [ThemeController::class, 'index'])->name('sites.index');
    Route::post('/sites/{slug}/activate', [ThemeController::class, 'activate'])->name('sites.activate');
    Route::get('/sites/{slug}/edit', [ThemeController::class, 'edit'])->name('sites.edit');
    Route::put('/sites/{slug}', [ThemeController::class, 'update'])->name('sites.update');

    // Legacy theme routes (backward compat)
    Route::get('/themes', [ThemeController::class, 'index'])->name('themes.index');
    Route::post('/themes/{slug}/activate', [ThemeController::class, 'activate'])->name('themes.activate');
    Route::get('/themes/{slug}/edit', [ThemeController::class, 'edit'])->name('themes.edit');
    Route::put('/themes/{slug}', [ThemeController::class, 'update'])->name('themes.update');
});
