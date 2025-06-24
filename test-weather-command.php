<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🌩️  TESTING WEATHER COMMAND\n";
echo "=".str_repeat("=", 40)."\n\n";

// Method 1: Via Artisan facade
echo "METHOD 1: Using Artisan facade\n";
echo "----------------------------------------\n";

try {
    echo "Running command: php artisan app:check-weather-for-alerts\n\n";
    
    $exitCode = \Illuminate\Support\Facades\Artisan::call('app:check-weather-for-alerts');
    $output = \Illuminate\Support\Facades\Artisan::output();
    
    echo "Exit Code: {$exitCode}\n";
    echo "Output:\n{$output}\n";
    
    if ($exitCode === 0) {
        echo "✅ Command completed successfully!\n\n";
    } else {
        echo "❌ Command failed with exit code {$exitCode}!\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n\n";
}

// Method 2: Direct instantiation
echo "METHOD 2: Direct command instantiation\n";
echo "----------------------------------------\n";

try {
    $command = new \App\Console\Commands\CheckWeatherForAlerts(
        app(\App\Services\WeatherService::class),
        app(\App\Services\AlertService::class)
    );
    
    // Mock console input/output
    $input = new \Symfony\Component\Console\Input\ArrayInput([]);
    $output = new \Symfony\Component\Console\Output\BufferedOutput();
    
    $command->setLaravel($app);
    $command->run($input, $output);
    
    echo "Direct command output:\n";
    echo $output->fetch();
    echo "\n✅ Direct command execution completed!\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR in direct execution: {$e->getMessage()}\n\n";
}

// Check results
echo "CHECKING RESULTS:\n";
echo "----------------------------------------\n";

try {
    $recentWeatherAlerts = \App\Models\Alert::where('type', 'weather_forecast')
        ->where('created_at', '>=', now()->subMinutes(10))
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "Weather alerts created in last 10 minutes: " . $recentWeatherAlerts->count() . "\n";
    
    foreach ($recentWeatherAlerts as $alert) {
        echo "  - Alert #{$alert->id}: {$alert->title} (Severity: {$alert->severity})\n";
        echo "    Created: {$alert->created_at}\n";
        echo "    Pump House: {$alert->pump_house->name}\n";
        echo "    Description: {$alert->description}\n\n";
    }
    
    if ($recentWeatherAlerts->count() === 0) {
        echo "No new weather alerts created. This might mean:\n";
        echo "- Weather conditions don't meet alert criteria\n";
        echo "- API issues with weather service\n";
        echo "- No pump houses in database\n";
        echo "- Command logic issues\n\n";
    }
    
    // Check total notifications
    $totalNotifications = \Illuminate\Support\Facades\DB::table('notifications')
        ->where('created_at', '>=', now()->subMinutes(10))
        ->whereJsonContains('data->type', 'weather_forecast')
        ->count();
    
    echo "Total weather notifications in last 10 minutes: {$totalNotifications}\n";
    
} catch (Exception $e) {
    echo "❌ ERROR checking results: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("=", 50) . "\n"; 