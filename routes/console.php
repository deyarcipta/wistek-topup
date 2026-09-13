<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('points:expire')->daily();
Schedule::command('products:sync-digiflazz')->everySixHours();
Schedule::command('doku:check-pending')->everyTwoMinutes()->withoutOverlapping();
Schedule::command('digiflazz:check-pending')->everyTwoMinutes()->withoutOverlapping();
Schedule::command('members:reset-annual-tiers')->yearlyOn(1, 1, '00:00');
