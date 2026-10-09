<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pengingat Otomatis Sistem Laboratorium (INV-F-003, INV-F-004, PJM-F-005)
Schedule::command('lab:send-reminders')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/reminders.log'));
