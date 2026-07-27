<?php

namespace App\Providers;

use App\Http\ViewComposers\AdminSidebarComposer;
use App\Http\ViewComposers\CategoryComposer;
use App\Http\ViewComposers\HeaderComposer;
use App\Http\ViewComposers\HomepageComposer;
use App\Http\ViewComposers\NavigationComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Navigation categories - shared across header, navigation, and category grids
        View::composer(
            [
                'layouts.header',
                'layouts.navigation',
                'theme::partials._category_grid',
            ],
            NavigationComposer::class
        );

        // Header cart/wishlist counts
        View::composer('layouts.header', HeaderComposer::class);

        // Admin sidebar unresolved stock-alert badge
        View::composer('layouts.admin', AdminSidebarComposer::class);

        // Homepage data
        View::composer('shop.index', HomepageComposer::class);

        // Category image/tagline maps
        View::composer(
            [
                'shop.partials._category-products',
                'shop.category',
                'shop.product-details',
                'shop.partials.product-card',
                'theme::partials._category_grid',
                'theme::partials._category_products',
            ],
            CategoryComposer::class
        );
    }
}
