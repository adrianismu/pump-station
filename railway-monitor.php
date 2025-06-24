<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PumpHouse;
use App\Models\WaterLevelHistory;
use App\Models\Alert;
use App\Models\Report;

echo "🚂 RAILWAY PERFORMANCE MONITOR\n";
echo "==============================\n\n";

function checkDatabasePerformance() {
    echo "📊 DATABASE PERFORMANCE CHECK\n";
    echo "-----------------------------\n";
    
    try {
        // Test connection speed
        $start = microtime(true);
        DB::connection()->getPdo();
        $connectionTime = round((microtime(true) - $start) * 1000, 2);
        echo "✅ Connection time: {$connectionTime}ms\n";
        
        // Test simple query speed
        $start = microtime(true);
        $count = DB::table('pump_houses')->count();
        $queryTime = round((microtime(true) - $start) * 1000, 2);
        echo "✅ Simple query time: {$queryTime}ms (Count: {$count})\n";
        
        // Test complex query speed
        $start = microtime(true);
        $waterLevels = WaterLevelHistory::with('pumpHouse')->take(100)->get();
        $complexQueryTime = round((microtime(true) - $start) * 1000, 2);
        echo "✅ Complex query time: {$complexQueryTime}ms (Records: {$waterLevels->count()})\n";
        
        // Check connection pool
        $processlist = DB::select('SHOW PROCESSLIST');
        $activeConnections = count($processlist);
        echo "📋 Active connections: {$activeConnections}\n";
        
        // Check for long-running queries
        $longQueries = collect($processlist)->filter(function($process) {
            return isset($process->Time) && $process->Time > 10;
        })->count();
        
        if ($longQueries > 0) {
            echo "⚠️  Long-running queries: {$longQueries}\n";
        } else {
            echo "✅ No long-running queries\n";
        }
        
        // Performance recommendations
        if ($connectionTime > 1000) {
            echo "⚠️  High connection latency detected\n";
        }
        if ($queryTime > 500) {
            echo "⚠️  Slow query performance detected\n";
        }
        if ($activeConnections > 50) {
            echo "⚠️  High connection count detected\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Database performance check failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

function checkMemoryUsage() {
    echo "💾 MEMORY USAGE CHECK\n";
    echo "-------------------\n";
    
    $memoryUsage = memory_get_usage(true) / 1024 / 1024;
    $peakMemory = memory_get_peak_usage(true) / 1024 / 1024;
    $memoryLimit = ini_get('memory_limit');
    
    echo "Current memory: " . number_format($memoryUsage, 2) . " MB\n";
    echo "Peak memory: " . number_format($peakMemory, 2) . " MB\n";
    echo "Memory limit: {$memoryLimit}\n";
    
    if ($memoryUsage > 100) {
        echo "⚠️  High memory usage detected\n";
    } else {
        echo "✅ Memory usage normal\n";
    }
    
    echo "\n";
}

function checkCriticalErrors() {
    echo "🚨 CRITICAL ERROR CHECK\n";
    echo "----------------------\n";
    
    try {
        // Check for recent alerts creation failures
        $recentAlerts = Alert::where('created_at', '>=', now()->subHours(1))->count();
        echo "Recent alerts created: {$recentAlerts}\n";
        
        // Check for recent reports
        $recentReports = Report::where('created_at', '>=', now()->subHours(1))->count();
        echo "Recent reports created: {$recentReports}\n";
        
        // Check for failed water level insertions
        $recentWaterLevels = WaterLevelHistory::where('created_at', '>=', now()->subHours(1))->count();
        echo "Recent water levels recorded: {$recentWaterLevels}\n";
        
        // Check for database integrity
        $orphanedReports = Report::whereNotExists(function($query) {
            $query->select(DB::raw(1))
                  ->from('pump_houses')
                  ->whereRaw('pump_houses.id = reports.pump_house_id');
        })->count();
        
        if ($orphanedReports > 0) {
            echo "⚠️  Orphaned reports found: {$orphanedReports}\n";
        } else {
            echo "✅ Database integrity good\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Critical error check failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

function testWaterLevelAlert() {
    echo "🧪 WATER LEVEL ALERT SIMULATION\n";
    echo "------------------------------\n";
    
    try {
        $pumpHouse = PumpHouse::first();
        if (!$pumpHouse) {
            echo "❌ No pump house available for testing\n";
            return;
        }
        
        echo "Testing with: {$pumpHouse->name}\n";
        
        // Simulate critical water level (without actually creating alert)
        $testLevel = 2.8;
        echo "Simulating level: {$testLevel}m\n";
        
        // Check if thresholds exist
        $thresholds = DB::table('threshold_settings')
            ->where('is_active', true)
            ->where('water_level', '<=', $testLevel)
            ->orderBy('water_level', 'desc')
            ->first();
            
        if ($thresholds) {
            echo "✅ Threshold matched: {$thresholds->name} ({$thresholds->water_level}m)\n";
            echo "✅ Alert would be created with severity: {$thresholds->severity}\n";
        } else {
            echo "⚠️  No thresholds matched for this level\n";
        }
        
        // Check existing alerts to prevent spam
        $existingAlerts = Alert::where('pump_house_id', $pumpHouse->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();
            
        echo "Existing alerts in last hour: {$existingAlerts}\n";
        
        if ($existingAlerts > 0) {
            echo "ℹ️  Alert creation would be skipped (duplicate prevention)\n";
        } else {
            echo "✅ Alert would be created\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Water level alert test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

function checkRailwayEnvironment() {
    echo "🚂 RAILWAY ENVIRONMENT CHECK\n";
    echo "---------------------------\n";
    
    $railwayVars = [
        'RAILWAY_ENVIRONMENT' => env('RAILWAY_ENVIRONMENT'),
        'DATABASE_URL' => env('DATABASE_URL') ? 'Set' : 'Not set',
        'APP_ENV' => env('APP_ENV'),
        'APP_DEBUG' => env('APP_DEBUG') ? 'true' : 'false',
        'DB_CONNECTION' => env('DB_CONNECTION'),
        'QUEUE_CONNECTION' => env('QUEUE_CONNECTION'),
    ];
    
    foreach ($railwayVars as $var => $value) {
        echo "{$var}: {$value}\n";
    }
    
    // Check timezone
    echo "Timezone: " . date_default_timezone_get() . "\n";
    echo "Current time: " . now()->toDateTimeString() . "\n";
    
    echo "\n";
}

function generateRecommendations() {
    echo "💡 RAILWAY OPTIMIZATION RECOMMENDATIONS\n";
    echo "======================================\n";
    
    echo "1. Database Optimization:\n";
    echo "   - Use connection pooling\n";
    echo "   - Add database query timeout\n";
    echo "   - Implement retry logic for failed queries\n\n";
    
    echo "2. Error Handling:\n";
    echo "   - Separate alert creation from critical operations\n";
    echo "   - Use queues for heavy operations\n";
    echo "   - Add circuit breaker patterns\n\n";
    
    echo "3. Monitoring:\n";
    echo "   - Set up error tracking (Sentry)\n";
    echo "   - Monitor database connection pool\n";
    echo "   - Track response times\n\n";
    
    echo "4. Railway-Specific:\n";
    echo "   - Handle connection timeouts gracefully\n";
    echo "   - Use Railway's built-in metrics\n";
    echo "   - Optimize memory usage\n\n";
}

// Run all checks
try {
    checkDatabasePerformance();
    checkMemoryUsage();
    checkCriticalErrors();
    testWaterLevelAlert();
    checkRailwayEnvironment();
    generateRecommendations();
    
    echo "🎯 MONITORING COMPLETED\n";
    echo "======================\n";
    echo "Run this script regularly to monitor Railway performance.\n";
    echo "Use: php railway-monitor.php\n\n";
    
} catch (Exception $e) {
    echo "💥 MONITORING FAILED: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "🏁 Monitoring completed at " . now()->toDateTimeString() . "\n"; 