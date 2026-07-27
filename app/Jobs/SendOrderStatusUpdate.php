<?php

namespace App\Jobs;

use App\Mail\OrderStatusUpdateMail;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusUpdate implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $orderId,
        protected string $status,
        protected ?string $note = null
    ) {}

    public function handle(): void
    {
        $order = Order::with(['user', 'items.product', 'items.variant'])->find($this->orderId);

        if (!$order || !$order->user) {
            return;
        }

        try {
            Mail::to($order->user->email)->send(new OrderStatusUpdateMail($order, $this->status, $this->note));

            Log::info("Order status update ({$this->status}) sent for #{$order->order_number} → {$order->user->email}");
        } catch (\Throwable $e) {
            Log::error("Failed to send status update for #{$order->order_number}: " . $e->getMessage());
        }
    }
}
