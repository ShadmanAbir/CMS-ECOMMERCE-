<?php

namespace App\Jobs;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmation implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $orderId
    ) {}

    public function handle(): void
    {
        $order = Order::with(['user', 'items.product', 'items.variant'])->find($this->orderId);

        if (!$order || !$order->user) {
            return;
        }

        try {
            Mail::to($order->user->email)->send(new OrderConfirmationMail($order));

            Log::info("Order confirmation sent for order #{$order->order_number} → {$order->user->email}");
        } catch (\Throwable $e) {
            Log::error("Failed to send order confirmation for #{$order->order_number}: " . $e->getMessage());
        }
    }
}
