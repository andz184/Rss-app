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
        // Cập nhật tất cả nguồn tin RSS mỗi 12 giờ
        $schedule->command('feeds:fetch')
                 ->twiceDaily(0, 12) // Chạy vào 00:00 và 12:00
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/feed-fetch.log'));
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
