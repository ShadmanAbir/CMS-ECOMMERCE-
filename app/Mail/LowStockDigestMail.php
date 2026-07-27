<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  \Illuminate\Database\Eloquent\Collection  $alerts  Open stock alerts, each with its product.
     */
    public function __construct(
        public Collection $alerts
    ) {}

    public function envelope(): Envelope
    {
        $siteName = theme('name', config('app.name'));
        $count = $this->alerts->count();
        $noun = $count === 1 ? 'product is' : 'products are';

        return new Envelope(
            subject: "{$count} {$noun} low on stock | {$siteName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.low-stock-digest',
            with: [
                'alerts'      => $this->alerts,
                'siteName'    => theme('name', config('app.name')),
                'accentColor' => theme('colors.accent', '#000000'),
                'currency'    => theme('currency.symbol', '৳'),
            ],
        );
    }
}
