<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('trading:buy')
            ->everyThreeDays()
            ->at('09:00')
            ->timezone(config('app.timezone'))
            ->description('Buy coins with 5% dip');
        
        $schedule->command('trading:sell')
            ->daily()
            ->at('09:00')
            ->timezone(config('app.timezone'))
            ->description('Sell coins with 10% gain');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}