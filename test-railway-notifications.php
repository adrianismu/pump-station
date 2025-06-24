<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PumpHouse;
use App\Models\Alert;
use App\Models\User;
use App\Services\AlertService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

echo "🚀 RAILWAY NOTIFICATION TEST\n";
echo "Environment: " . app()->environment() . "\n";
echo "=".str_repeat("=", 50)."\n\n";

function checkRailwayEnvironment() {
    echo "🔍 RAILWAY ENVIRONMENT CHECK:\n";
    echo "----------------------------------------\n";
    
    try {
        // Database connection
        $dbConnected = \DB::connection()->getPdo() ? true : false;
        echo "✓ Database: " . ($dbConnected ? "Connected" : "Failed") . "\n";
        
        // Required tables
        $tables = ['pump_houses', 'alerts', 'notifications', 'users', 'roles', 'model_has_roles'];
        foreach ($tables as $table) {
            $exists = \Schema::hasTable($table);
            echo ($exists ? "✓" : "✗") . " Table '{$table}': " . ($exists ? "Exists" : "Missing") . "\n";
        }
        
        // User counts
        $adminCount = User::role('admin')->count();
        $petugasCount = User::role('petugas')->count();
        $totalUsers = User::count();
        
        echo "✓ Users: {$totalUsers} total ({$adminCount} admin, {$petugasCount} petugas)\n";
        
        // Pump houses
        $pumpHouseCount = PumpHouse::count();
        $pumpHousesWithCoords = PumpHouse::whereNotNull('lat')->whereNotNull('lng')->count();
        echo "✓ Pump Houses: {$pumpHouseCount} total ({$pumpHousesWithCoords} with coordinates)\n";
        
        // Queue config
        echo "✓ Queue Driver: " . config('queue.default') . "\n";
        echo "✓ App URL: " . config('app.url') . "\n";
        echo "✓ Log Channel: " . config('logging.default') . "\n";
        
        return true;
        
    } catch (Exception $e) {
        echo "✗ Environment check failed: {$e->getMessage()}\n";
        return false;
    }
}

function testWeatherNotification() {
    echo "\n🌧️  WEATHER NOTIFICATION TEST:\n";
    echo "----------------------------------------\n";
    
    try {
        $pumpHouse = PumpHouse::first();
        if (!$pumpHouse) {
            echo "✗ No pump house found\n";
            return false;
        }
        
        echo "🏠 Testing with: {$pumpHouse->name}\n";
        
        // Create critical weather data
        $weatherData = [
            'precipitation' => 25.0,
            'precipitation_probability' => 95,
            'wind_speed' => 40,
            'weather_code' => 95,
            'description' => 'Badai petir sangat lebat (Railway Test)',
        ];
        
        echo "🌦️  Simulated weather: {$weatherData['description']}\n";
        echo "   - Precipitation: {$weatherData['precipitation']}mm\n";
        echo "   - Probability: {$weatherData['precipitation_probability']}%\n";
        
        // Create alert
        $alertService = app(AlertService::class);
        $alert = $alertService->createWeatherAlert($pumpHouse, $weatherData);
        
        echo "✓ Alert created: ID {$alert->id}\n";
        echo "   - Title: {$alert->title}\n";
        echo "   - Severity: {$alert->severity}\n";
        echo "   - Type: {$alert->type}\n";
        
        // Check notifications in database
        sleep(2); // Give it a moment to process
        
        $notificationCount = \DB::table('notifications')
            ->whereJsonContains('data->alert_id', $alert->id)
            ->count();
            
        echo "✓ Notifications created: {$notificationCount}\n";
        
        if ($notificationCount > 0) {
            $notifications = \DB::table('notifications')
                ->whereJsonContains('data->alert_id', $alert->id)
                ->get();
                
            foreach ($notifications as $notif) {
                $data = json_decode($notif->data, true);
                echo "   - User ID {$notif->notifiable_id}: {$data['title']}\n";
            }
        }
        
        return $alert;
        
    } catch (Exception $e) {
        echo "✗ Weather notification test failed: {$e->getMessage()}\n";
        echo "Stack trace: {$e->getTraceAsString()}\n";
        return false;
    }
}

function testSchedulerCommand() {
    echo "\n⏰ SCHEDULER COMMAND TEST:\n";
    echo "----------------------------------------\n";
    
    try {
        echo "Running weather alert command...\n";
        $exitCode = \Artisan::call('app:check-weather-for-alerts');
        $output = \Artisan::output();
        
        echo "Exit Code: {$exitCode}\n";
        echo "Output:\n{$output}\n";
        
        if ($exitCode === 0) {
            echo "✓ Command executed successfully\n";
        } else {
            echo "✗ Command failed with exit code {$exitCode}\n";
        }
        
        return $exitCode === 0;
        
    } catch (Exception $e) {
        echo "✗ Scheduler test failed: {$e->getMessage()}\n";
        return false;
    }
}

function checkRecentLogs() {
    echo "\n📋 RECENT LOGS:\n";
    echo "----------------------------------------\n";
    
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $lines = file($logFile);
        $recentLines = array_slice($lines, -20);
        
        foreach ($recentLines as $line) {
            if (strpos($line, 'Notification') !== false || 
                strpos($line, 'weather') !== false || 
                strpos($line, 'alert') !== false) {
                echo "  " . trim($line) . "\n";
            }
        }
    } else {
        echo "Log file not found\n";
    }
}

function railwayDiagnostics() {
    echo "\n🔧 RAILWAY DIAGNOSTICS:\n";
    echo "----------------------------------------\n";
    
    // Memory usage
    echo "Memory Usage: " . number_format(memory_get_usage(true) / 1024 / 1024, 2) . " MB\n";
    echo "Peak Memory: " . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB\n";
    
    // Disk space
    echo "Disk Usage: " . number_format(disk_free_space('.') / 1024 / 1024, 2) . " MB free\n";
    
    // Environment variables
    $envVars = ['APP_ENV', 'APP_DEBUG', 'LOG_CHANNEL', 'QUEUE_CONNECTION', 'DB_CONNECTION'];
    foreach ($envVars as $var) {
        echo "{$var}: " . (env($var) ?: 'not set') . "\n";
    }
    
    // Storage permissions
    $storagePath = storage_path('logs');
    echo "Storage logs writable: " . (is_writable($storagePath) ? 'Yes' : 'No') . "\n";
}

// Main execution
try {
    if (!checkRailwayEnvironment()) {
        echo "\n❌ Environment check failed. Cannot proceed.\n";
        exit(1);
    }
    
    $alert = testWeatherNotification();
    
    if ($alert) {
        echo "\n✅ Manual notification test PASSED\n";
    } else {
        echo "\n❌ Manual notification test FAILED\n";
    }
    
    $schedulerWorked = testSchedulerCommand();
    
    if ($schedulerWorked) {
        echo "\n✅ Scheduler command test PASSED\n";
    } else {
        echo "\n❌ Scheduler command test FAILED\n";
    }
    
    checkRecentLogs();
    railwayDiagnostics();
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎯 RAILWAY NOTIFICATION TEST COMPLETED\n";
    
    if ($alert && $schedulerWorked) {
        echo "✅ All tests PASSED - Notifications should work on Railway\n";
    } else {
        echo "❌ Some tests FAILED - Check logs and configuration\n";
    }
    
} catch (Exception $e) {
    echo "\n💥 FATAL ERROR: {$e->getMessage()}\n";
    echo "Stack trace: {$e->getTraceAsString()}\n";
    exit(1);
}

echo "\n🚀 Test completed at " . now() . "\n"; 