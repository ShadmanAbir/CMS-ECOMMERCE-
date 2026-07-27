<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function envelope(): Envelope
    {
        $siteName = theme('name', config('app.name'));

        return new Envelope(
            subject: "Order Confirmed — #{$this->order->order_number} | {$siteName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
            with: [
                'order' => $this->order,
                'siteName' => theme('name', config('app.name')),
                'accentColor' => theme('colors.accent', '#000000'),
                'contactEmail' => theme('contact.email', config('mail.from.address')),
            ],
        );
    }
}
