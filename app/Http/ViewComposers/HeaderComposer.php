<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

class HeaderComposer
{
    /**
     * Provides $cartCount, $wishlistCount and $orderCount for the header view.
     * Uses auth check to avoid queries for guests.
     */
    public function compose(View $view): void
    {
        $user = auth()->user();

        $view->with('cartCount', $user ? $user->carts()->count() : 0);
        $view->with('wishlistCount', $user ? $user->wishlists()->count() : 0);
        $view->with('orderCount', $user ? $user->orders()->count() : 0);
    }
}
