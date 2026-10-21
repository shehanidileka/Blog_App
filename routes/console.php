<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Every Monday at 08:00 (server time zone = APP timezone in config/app.php)
Schedule::command('report:weekly')
    ->weeklyOn(1, '08:00')
    ->withoutOverlapping()
    ->name('weekly-summary');