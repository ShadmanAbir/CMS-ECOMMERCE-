<?php

namespace App\Http\ViewComposers;

use App\Services\StockAlertService;
use Illuminate\View\View;

class AdminSidebarComposer
{
    public function __construct(
        protected StockAlertService $alerts
    ) {}

    /**
     * Provides $unresolvedAlertCount for the admin sidebar badge.
     */
    public function compose(View $view): void
    {
        $view->with('unresolvedAlertCount', $this->alerts->unresolvedCount());
    }
}
