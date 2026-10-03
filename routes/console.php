<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hourly rather than daily: the command only closes items returned 7+ days
// ago, so repeating it is harmless, and a worker restart can't skip a day.
Schedule::command('app:auto-close-returned-items')->hourly();
