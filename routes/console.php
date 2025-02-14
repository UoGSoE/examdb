<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('activitylog:clean')->daily();
Schedule::command('examdb:timed-notifications')->dailyAt('03:00');
Schedule::command('examdb:send-print-ready-reminder-emails')->dailyAt('07:00');
