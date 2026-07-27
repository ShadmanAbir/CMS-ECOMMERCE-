<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Clean up expired guest sessions daily
        $schedule->command('guest-sessions:cleanup')->daily();
        
        // Cancel pending orders older than 24 hours
        $schedule->command('orders:cancel-pending')->daily();

        // Email admins a digest of products low on stock (frequency per-site)
        $digest = $schedule->command('alerts:digest');
        if (theme('inventory.digest_frequency', 'daily') === 'weekly') {
            $digest->weeklyOn(theme('inventory.digest_day', 'monday'));
        } else {
            $digest->daily();
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
