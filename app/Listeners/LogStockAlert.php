<?php

namespace App\Listeners;

use App\Events\StockLow;
use Illuminate\Support\Facades\Log;

class LogStockAlert
{
    public function handle(StockLow $event): void
    {
        $product = $event->product;

        Log::warning("Low stock alert: {$product->name} (ID: {$product->id}) — {$product->totalStock()} remaining");

        // Future implementation:
        // - Push notification to admin dashboard (broadcast already on 'admin-notifications' channel)
        // - Mail::to(config('mail.admin'))->send(new StockLowMail($product));
    }
}
