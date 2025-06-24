<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule weather alert checker
// On Railway: run every 10 minutes for testing, then change to 30 minutes for production
$interval = app()->environment('local') ? 'everyMinute' : 'everyTenMinutes';

Schedule::command('app:check-weather-for-alerts')
    ->{$interval}()
    ->withoutOverlapping(10) // Wait max 10 minutes for previous job to finish
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/weather-alerts.log'))
    ->onFailure(function () {
        Log::error('Weather alert scheduler failed', [
            'timestamp' => now(),
            'command' => 'app:check-weather-for-alerts',
            'environment' => app()->environment(),
            'memory_usage' => memory_get_peak_usage(true),
            'load_average' => sys_getloadavg()
        ]);
    })
    ->onSuccess(function () {
        Log::info('Weather alert scheduler completed successfully', [
            'timestamp' => now(),
            'command' => 'app:check-weather-for-alerts',
            'environment' => app()->environment(),
            'memory_usage' => memory_get_peak_usage(true)
        ]);
    })
    ->before(function () {
        Log::info('Weather alert scheduler starting', [
            'timestamp' => now(),
            'environment' => app()->environment()
        ]);
    });

// Add a test command that runs every minute in development
if (app()->environment('local')) {
    Schedule::call(function () {
        Log::info('Scheduler heartbeat', [
            'timestamp' => now(),
            'memory' => memory_get_usage(true),
            'environment' => app()->environment()
        ]);
    })->everyMinute();
}
