<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Per-status copy for the subject line, hero headline and body message.
     */
    protected const COPY = [
        'processing' => [
            'subject'  => 'Your order is being prepared',
            'headline' => 'Your order is being prepared',
            'message'  => "Good news! We've started processing your order and are getting it ready for shipment.",
            'icon'     => '&#9881;',
        ],
        'shipped' => [
            'subject'  => 'Your order has shipped',
            'headline' => 'Your order is on its way',
            'message'  => 'Your order has been shipped and is heading your way. Hang tight — it will arrive soon!',
            'icon'     => '&#128666;',
        ],
        'delivered' => [
            'subject'  => 'Your order has been delivered',
            'headline' => 'Your order has been delivered',
            'message'  => 'Your order has been delivered. We hope you love it! Thank you for shopping with us.',
            'icon'     => '&#10003;',
        ],
        'cancelled' => [
            'subject'  => 'Your order has been cancelled',
            'headline' => 'Your order has been cancelled',
            'message'  => 'Your order has been cancelled. If a payment was made, any refund will be processed shortly.',
            'icon'     => '&#10005;',
        ],
    ];

    public function __construct(
        public Order $order,
        public string $status,
        public ?string $note = null
    ) {}

    public function envelope(): Envelope
    {
        $siteName = theme('name', config('app.name'));
        $subject = self::COPY[$this->status]['subject'] ?? 'Order update';

        return new Envelope(
            subject: "{$subject} — #{$this->order->order_number} | {$siteName}",
        );
    }

    public function content(): Content
    {
        $copy = self::COPY[$this->status] ?? [
            'headline' => 'Order Update',
            'message'  => "Your order status has been updated to {$this->status}.",
            'icon'     => '&#8226;',
        ];

        return new Content(
            view: 'emails.order-status-update',
            with: [
                'order'        => $this->order,
                'status'       => $this->status,
                'headline'     => $copy['headline'],
                'bodyText'     => $copy['message'],
                'icon'         => $copy['icon'],
                'note'         => $this->note,
                'siteName'     => theme('name', config('app.name')),
                'accentColor'  => theme('colors.accent', '#000000'),
                'contactEmail' => theme('contact.email', config('mail.from.address')),
            ],
        );
    }
}
