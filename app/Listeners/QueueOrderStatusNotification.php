<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Jobs\SendOrderStatusUpdate;

class QueueOrderStatusNotification
{
    /**
     * Statuses that warrant a customer email. The initial 'pending' state is
     * already covered by the order confirmation email, so it is excluded here.
     */
    protected array $notifiable = ['processing', 'shipped', 'delivered', 'cancelled'];

    public function handle(OrderStatusChanged $event): void
    {
        if (in_array($event->newStatus, $this->notifiable, true)) {
            SendOrderStatusUpdate::dispatch($event->order->id, $event->newStatus, $event->note);
        }
    }
}
