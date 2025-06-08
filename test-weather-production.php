<?php

use Illuminate\Support\Facades\Log;
use App\Models\Alert;

echo "🌦️ Testing Weather Alert System in Production...\n\n";

// Check recent weather alerts
$weatherAlerts = Alert::where('type', 'weather_forecast')
    ->where('created_at', '>', now()->subDays(1))
    ->orderBy('created_at', 'desc')
    ->get();

echo "Weather alerts in last 24 hours: " . $weatherAlerts->count() . "\n";

foreach ($weatherAlerts as $alert) {
    echo "- ID {$alert->id}: {$alert->title}\n";
    echo "  Created: {$alert->created_at}\n";
    echo "  Severity: {$alert->severity}\n\n";
}

// Log test
Log::info('Weather alert system test completed', [
    'weather_alerts_count' => $weatherAlerts->count(),
    'timestamp' => now()
]);

echo "✅ Test completed. Check logs for more details.\n"; 