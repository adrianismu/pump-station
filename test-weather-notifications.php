<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PumpHouse;
use App\Models\Alert;
use App\Models\User;
use App\Services\AlertService;
use App\Services\WeatherService;
use App\Services\NotificationService;

echo "=== TEST WEATHER NOTIFICATION TRIGGER ===\n\n";

function createWeatherData($severity = 'high', $customData = []) {
    $defaultData = [
        'low' => [
            'precipitation' => 1.0,
            'precipitation_probability' => 30,
            'wind_speed' => 5,
            'weather_code' => 61,
            'description' => 'Hujan ringan',
        ],
        'medium' => [
            'precipitation' => 3.0,
            'precipitation_probability' => 55,
            'wind_speed' => 12,
            'weather_code' => 63,
            'description' => 'Hujan sedang',
        ],
        'high' => [
            'precipitation' => 6.0,
            'precipitation_probability' => 75,
            'wind_speed' => 18,
            'weather_code' => 65,
            'description' => 'Hujan lebat',
        ],
        'critical' => [
            'precipitation' => 12.0,
            'precipitation_probability' => 95,
            'wind_speed' => 30,
            'weather_code' => 95,
            'description' => 'Badai petir dengan hujan sangat lebat',
        ]
    ];

    $data = $defaultData[$severity] ?? $defaultData['high'];
    return array_merge($data, $customData);
}

function testWeatherAlert($severity = 'high', $description = '') {
    global $alertService;
    
    echo "TEST: {$description}\n";
    echo "----------------------------------------\n";
    
    try {
        // Get pump house pertama
        $pumpHouse = PumpHouse::first();
        if (!$pumpHouse) {
            echo "❌ ERROR: Tidak ada pump house di database\n\n";
            return false;
        }
        
        echo "🏠 Pump House: {$pumpHouse->name}\n";
        echo "📍 Lokasi: {$pumpHouse->address}\n";
        
        // Generate weather data sesuai severity
        $weatherData = createWeatherData($severity);
        echo "🌧️  Weather Data:\n";
        echo "   - Precipitation: {$weatherData['precipitation']}mm\n";
        echo "   - Probability: {$weatherData['precipitation_probability']}%\n";
        echo "   - Wind Speed: {$weatherData['wind_speed']} km/h\n";
        echo "   - Description: {$weatherData['description']}\n";
        
        // Create alert
        $alert = $alertService->createWeatherAlert($pumpHouse, $weatherData);
        
        echo "✅ Weather Alert Created!\n";
        echo "   - Alert ID: {$alert->id}\n";
        echo "   - Title: {$alert->title}\n";
        echo "   - Severity: {$alert->severity}\n";
        echo "   - Description: {$alert->description}\n";
        echo "   - Internal Message: {$alert->internal_message}\n";
        if ($alert->public_message) {
            echo "   - Public Message: {$alert->public_message}\n";
        }
        
        // Check notifications sent
        $admins = User::role('admin')->get();
        $petugas = User::role('petugas')->get();
        
        echo "📧 Notifications sent to:\n";
        echo "   - Admins: {$admins->count()} users\n";
        echo "   - Petugas: {$petugas->count()} users\n";
        
        // Check database notifications
        $totalNotifications = \Illuminate\Support\Facades\DB::table('notifications')
            ->whereJsonContains('data->alert_id', $alert->id)
            ->count();
            
        echo "💾 Database notifications created: {$totalNotifications}\n";
        
        echo "✅ SUCCESS!\n\n";
        return $alert;
        
    } catch (Exception $e) {
        echo "❌ ERROR: {$e->getMessage()}\n\n";
        return false;
    }
}

function runWeatherCommand() {
    echo "TEST: Manual Weather Command\n";
    echo "----------------------------------------\n";
    
    try {
        $exitCode = \Illuminate\Support\Facades\Artisan::call('app:check-weather-for-alerts');
        $output = \Illuminate\Support\Facades\Artisan::output();
        
        echo "Command Exit Code: {$exitCode}\n";
        echo "Command Output:\n{$output}\n";
        
        if ($exitCode === 0) {
            echo "✅ Weather command completed successfully!\n\n";
        } else {
            echo "❌ Weather command failed!\n\n";
        }
        
    } catch (Exception $e) {
        echo "❌ ERROR running command: {$e->getMessage()}\n\n";
    }
}

function checkNotificationCounts() {
    echo "CEK NOTIFICATION COUNTS:\n";
    echo "----------------------------------------\n";
    
    try {
        $notificationService = app(NotificationService::class);
        
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $notifications = $notificationService->getActiveNotifications($admin->id);
            echo "👤 {$admin->name}: " . count($notifications) . " notifications\n";
            
            foreach ($notifications as $notif) {
                if ($notif['type'] === 'weather_forecast') {
                    echo "   🌧️  Weather: {$notif['title']} ({$notif['severity']})\n";
                }
            }
        }
        
        echo "\n";
        
    } catch (Exception $e) {
        echo "❌ ERROR: {$e->getMessage()}\n\n";
    }
}

function cleanupOldAlerts() {
    echo "CLEANUP: Hapus alert lama (opsional)\n";
    echo "----------------------------------------\n";
    
    $oldAlerts = Alert::where('type', 'weather_forecast')
        ->where('created_at', '<', now()->subHours(1))
        ->count();
    
    if ($oldAlerts > 0) {
        echo "Found {$oldAlerts} old weather alerts\n";
        echo "Delete them? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $answer = trim(fgets($handle));
        fclose($handle);
        
        if (strtolower($answer) === 'y') {
            Alert::where('type', 'weather_forecast')
                ->where('created_at', '<', now()->subHours(1))
                ->delete();
            echo "✅ Old alerts deleted!\n";
        }
    } else {
        echo "No old alerts to cleanup\n";
    }
    echo "\n";
}

// Main execution
try {
    $alertService = app(AlertService::class);
    
    echo "PILIH TEST SCENARIO:\n";
    echo "1. Test Weather Alert - Severity Low\n";
    echo "2. Test Weather Alert - Severity Medium  \n";
    echo "3. Test Weather Alert - Severity High\n";
    echo "4. Test Weather Alert - Severity Critical\n";
    echo "5. Run Weather Command (Real API)\n";
    echo "6. Check Current Notifications\n";
    echo "7. Cleanup Old Alerts\n";
    echo "8. Test All Scenarios\n";
    echo "\nPilih opsi (1-8): ";
    
    $handle = fopen("php://stdin", "r");
    $choice = trim(fgets($handle));
    fclose($handle);
    
    switch ($choice) {
        case '1':
            testWeatherAlert('low', 'Weather Alert with Low Severity');
            break;
            
        case '2':
            testWeatherAlert('medium', 'Weather Alert with Medium Severity');
            break;
            
        case '3':
            testWeatherAlert('high', 'Weather Alert with High Severity');
            break;
            
        case '4':
            testWeatherAlert('critical', 'Weather Alert with Critical Severity');
            break;
            
        case '5':
            runWeatherCommand();
            break;
            
        case '6':
            checkNotificationCounts();
            break;
            
        case '7':
            cleanupOldAlerts();
            break;
            
        case '8':
            echo "RUNNING ALL TEST SCENARIOS:\n";
            echo "=".str_repeat("=", 50)."\n\n";
            
            cleanupOldAlerts();
            testWeatherAlert('low', 'Test 1: Low Severity Weather Alert');
            testWeatherAlert('medium', 'Test 2: Medium Severity Weather Alert'); 
            testWeatherAlert('high', 'Test 3: High Severity Weather Alert');
            testWeatherAlert('critical', 'Test 4: Critical Severity Weather Alert');
            checkNotificationCounts();
            
            echo "🎉 ALL TESTS COMPLETED!\n";
            break;
            
        default:
            echo "Invalid choice. Exiting...\n";
            break;
    }
    
} catch (Exception $e) {
    echo "❌ FATAL ERROR: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
}

echo "\n=== TEST SELESAI ===\n"; 