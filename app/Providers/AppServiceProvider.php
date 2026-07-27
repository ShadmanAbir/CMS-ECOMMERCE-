<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Payment gateways are resolved via config('payments.gateways')
        // and the container. No explicit binding needed since each gateway
        // class is self-contained and resolved by class name.
    }

    public function boot(): void
    {
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);

        // Rate limiters
        RateLimiter::for('auth', function ($request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('checkout', function ($request) {
            return Limit::perMinute(10)->by($request->user()?->id ?? $request->ip());
        });

        RateLimiter::for('search', function ($request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
