<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\PumpHouse;
use App\Models\Alert;
use App\Models\ThresholdSetting;
use App\Services\AlertService;
use App\Services\NotificationService;

echo "Railway Error 500 Diagnostic Tool\n";
echo "=================================\n\n";

try {
    echo "1. Testing Database Connection\n";
    echo "------------------------------\n";
    
    DB::connection()->getPdo();
    echo "✅ Database connection: OK\n";
    
    $result = DB::select('SELECT 1 as test');
    echo "✅ Database query test: OK\n";
    
    echo "\n2. Checking Critical Tables\n";
    echo "---------------------------\n";
    
    $tables = ['pump_houses', 'water_level_histories', 'alerts', 'threshold_settings'];
    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            echo "✅ {$table}: {$count} records\n";
        } else {
            echo "❌ {$table}: TABLE MISSING!\n";
        }
    }
    
    echo "\n3. Testing Alert Service\n";
    echo "------------------------\n";
    
    $pumpHouse = PumpHouse::first();
    if ($pumpHouse) {
        echo "Testing with: {$pumpHouse->name}\n";
        
        $alertService = app(AlertService::class);
        echo "✅ AlertService instantiated\n";
        
        $notificationService = app(NotificationService::class);
        echo "✅ NotificationService instantiated\n";
        
        $existingAlerts = Alert::where('pump_house_id', $pumpHouse->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();
        echo "Existing alerts: {$existingAlerts}\n";
    }
    
    echo "\n4. Environment Check\n";
    echo "-------------------\n";
    echo "Environment: " . app()->environment() . "\n";
    echo "Debug mode: " . (config('app.debug') ? 'enabled' : 'disabled') . "\n";
    echo "Memory limit: " . ini_get('memory_limit') . "\n";
    
    echo "\nDiagnostic completed successfully!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
