<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class IncrementProductViews implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $productId
    ) {}

    public function handle(): void
    {
        Product::where('id', $this->productId)->increment('views_count');
    }
}
