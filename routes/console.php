<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule weather alert checker to run every 30 minutes
Schedule::command('app:check-weather-for-alerts')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/weather-alerts.log'))
    ->onFailure(function () {
        Log::error('Weather alert scheduler failed', [
            'timestamp' => now(),
            'command' => 'app:check-weather-for-alerts'
        ]);
    })
    ->onSuccess(function () {
        Log::info('Weather alert scheduler completed successfully', [
            'timestamp' => now(),
            'command' => 'app:check-weather-for-alerts'
        ]);
    });
