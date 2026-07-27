<?php

namespace App\Listeners;

use App\Events\StockLow;
use App\Services\StockAlertService;

class RecordStockAlert
{
    public function __construct(protected StockAlertService $alerts) {}

    /**
     * Persist a low-stock alert so admins can review it on the
     * Inventory Alerts page. Threshold is read from the site's theme config.
     */
    public function handle(StockLow $event): void
    {
        $threshold = (int) theme('inventory.low_stock_threshold', 5);
        $this->alerts->record($event->product, $threshold);
    }
}
