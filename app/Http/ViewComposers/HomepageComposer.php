<?php

namespace App\Http\ViewComposers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomepageComposer
{
    /**
     * Provides homepage data for all theme partials.
     * Cached per-site for 10 minutes; invalidated on admin changes.
     */
    public function compose(View $view): void
    {
        $siteSlug = theme('slug', 'default');
        $ttl = 600; // 10 minutes

        // Banners for hero carousel
        $view->with('banners', Cache::remember("banners_{$siteSlug}", $ttl, function () {
            return Banner::active()->orderBy('sort_order')->get();
        }));

        // Parent categories for category grids
        $view->with('parentCategories', Cache::remember("parent_categories_{$siteSlug}", $ttl, function () {
            return Category::active()->parents()->orderBy('sort_order')->get();
        }));

        // Nav categories (with children) for category grid partials
        $view->with('navCategories', Cache::remember("nav_categories_{$siteSlug}", 3600, function () {
            return Category::active()->parents()->with('children')->orderBy('name')->get();
        }));

        // Popular / trending products
        $popularProducts = Cache::remember("popular_products_{$siteSlug}", $ttl, function () {
            $products = Product::where('is_trending', true)->with(['category', 'images'])->take(6)->get();
            if ($products->count() < 6) {
                $products = Product::with(['category', 'images'])->inRandomOrder()->take(6)->get();
            }
            return $products;
        });
        $view->with('popularProducts', $popularProducts);
        $view->with('trendingProducts', $popularProducts);

        // Featured products
        $view->with('featuredProducts', Cache::remember("featured_products_{$siteSlug}", $ttl, function () {
            return Product::where('is_featured', true)->with(['category', 'images'])->take(8)->get();
        }));

        // New arrivals
        $view->with('newArrivals', Cache::remember("new_arrivals_{$siteSlug}", $ttl, function () {
            return Product::where('is_new', true)->with(['category', 'images'])->take(8)->get();
        }));
    }
}
