<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('trading:buy')
    //->cron('0 9 */3 * *') // Every 3 days at 09:00
    ->everyMinute()
    ->timezone(config('app.timezone'))
    ->description('Buy coins with 5% dip');

Schedule::command('trading:sell')
    ->daily()
    ->at('09:00')
    ->timezone(config('app.timezone'))
    ->description('Sell coins with 10% gain');

Schedule::command('coins:import-top')
    ->dailyAt('08:30')
    ->timezone(config('app.timezone'))
    ->description('Import top coins from CoinGecko');
