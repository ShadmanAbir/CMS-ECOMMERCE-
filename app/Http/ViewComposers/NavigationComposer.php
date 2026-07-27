<?php

namespace App\Http\ViewComposers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class NavigationComposer
{
    /**
     * Provides $navCategories for header, navigation, and category grid views.
     * Cached per-site for 1 hour; invalidated on admin category changes.
     */
    public function compose(View $view): void
    {
        $data = $view->getData();
        if (!array_key_exists('navCategories', $data)) {
            $view->with('navCategories', $this->getCategories());
        }
    }

    protected function getCategories()
    {
        $siteSlug = theme('slug', 'default');

        return Cache::remember("nav_categories_{$siteSlug}", 3600, function () {
            return Category::active()
                ->parents()
                ->with('children')
                ->orderBy('name')
                ->get();
        });
    }
}
