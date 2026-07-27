<?php

namespace App\Console\Commands;

use App\Mail\LowStockDigestMail;
use App\Services\StockAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendLowStockDigest extends Command
{
    protected $signature = 'alerts:digest';

    protected $description = 'Email admins a digest of products that are currently low on stock';

    public function handle(StockAlertService $alerts): int
    {
        $open = $alerts->openAlerts();

        if ($open->isEmpty()) {
            $this->info('No open stock alerts — digest skipped.');

            return self::SUCCESS;
        }

        $recipients = $alerts->digestRecipients();

        if (empty($recipients)) {
            $this->warn('No digest recipients configured — nothing sent.');

            return self::SUCCESS;
        }

        Mail::to($recipients)->send(new LowStockDigestMail($open));

        $this->info("Low-stock digest ({$open->count()}) sent to " . implode(', ', $recipients) . '.');

        return self::SUCCESS;
    }
}
