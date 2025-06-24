<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PumpHouse;
use App\Models\Alert;
use App\Services\AlertService;

echo "🌧️  QUICK WEATHER NOTIFICATION TEST\n";
echo "=".str_repeat("=", 40)."\n\n";

try {
    // Get first pump house
    $pumpHouse = PumpHouse::first();
    if (!$pumpHouse) {
        echo "❌ No pump house found in database!\n";
        exit(1);
    }

    // Create critical weather data
    $weatherData = [
        'precipitation' => 15.0,           // Heavy rain
        'precipitation_probability' => 95, // Very high chance
        'wind_speed' => 35,               // Strong wind
        'weather_code' => 95,             // Thunderstorm
        'description' => 'Badai petir dengan hujan sangat lebat (TESTING)',
    ];

    echo "🏠 Pump House: {$pumpHouse->name}\n";
    echo "📍 Location: {$pumpHouse->address}\n\n";

    echo "🌧️  Simulated Weather Data:\n";
    echo "   - Precipitation: {$weatherData['precipitation']}mm\n";
    echo "   - Probability: {$weatherData['precipitation_probability']}%\n";
    echo "   - Wind Speed: {$weatherData['wind_speed']} km/h\n";
    echo "   - Description: {$weatherData['description']}\n\n";

    // Create weather alert
    $alertService = app(AlertService::class);
    $alert = $alertService->createWeatherAlert($pumpHouse, $weatherData);

    echo "✅ WEATHER ALERT CREATED SUCCESSFULLY!\n\n";

    echo "📋 Alert Details:\n";
    echo "   - ID: {$alert->id}\n";
    echo "   - Title: {$alert->title}\n";
    echo "   - Severity: {$alert->severity}\n";
    echo "   - Type: {$alert->type}\n";
    echo "   - Description: {$alert->description}\n";
    echo "   - Internal Message: {$alert->internal_message}\n";
    
    if ($alert->public_message) {
        echo "   - Public Message: {$alert->public_message}\n";
    }

    // Check notifications in database
    $notificationCount = \Illuminate\Support\Facades\DB::table('notifications')
        ->whereJsonContains('data->alert_id', $alert->id)
        ->count();

    echo "\n📧 Notifications sent: {$notificationCount} users\n";

    echo "\n🎉 TEST COMPLETED! Check your admin dashboard to see the notification.\n";

} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
}

echo "\n" . str_repeat("=", 50) . "\n"; 