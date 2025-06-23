<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Alert;
use App\Models\User;
use App\Services\NotificationService;
use App\Notifications\WeatherAlertNotification;
use Illuminate\Support\Facades\Log;

echo "=== DEBUG NOTIFICATION SYSTEM ===\n\n";

// 1. Cek database notifications table
echo "1. CEK NOTIFICATIONS TABLE:\n";
try {
    $tableExists = \Illuminate\Support\Facades\Schema::hasTable('notifications');
    echo "   ✓ Notifications table exists: " . ($tableExists ? 'YES' : 'NO') . "\n";
    
    if ($tableExists) {
        $count = \Illuminate\Support\Facades\DB::table('notifications')->count();
        echo "   - Total notifications in DB: {$count}\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error checking table: {$e->getMessage()}\n";
}

// 2. Cek latest alert
echo "\n2. CEK LATEST WEATHER ALERT:\n";
$latestAlert = Alert::where('type', 'weather_forecast')->latest()->first();
if ($latestAlert) {
    echo "   ✓ Latest weather alert found:\n";
    echo "     - ID: {$latestAlert->id}\n";
    echo "     - Title: {$latestAlert->title}\n";
    echo "     - Pump House ID: {$latestAlert->pump_house_id}\n";
    echo "     - Created: {$latestAlert->created_at}\n";
} else {
    echo "   ✗ No weather alerts found\n";
    exit;
}

// 3. Cek users yang seharusnya menerima notifikasi
echo "\n3. CEK TARGET USERS:\n";
$admins = User::role('admin')->get();
$petugas = User::role('petugas')->get();

echo "   - Admin users: " . $admins->count() . "\n";
foreach ($admins as $admin) {
    echo "     • {$admin->name} ({$admin->email})\n";
}

echo "   - Petugas users: " . $petugas->count() . "\n";
foreach ($petugas as $user) {
    echo "     • {$user->name} ({$user->email})\n";
}

// 4. Test manual notification sending
echo "\n4. TEST MANUAL NOTIFICATION:\n";
try {
    $testUser = User::where('email', 'admin@pumpstation.com')->first();
    if ($testUser) {
        echo "   ✓ Test user found: {$testUser->email}\n";
        
        // Send notification manually
        $testUser->notify(new WeatherAlertNotification($latestAlert));
        echo "   ✓ Manual notification sent successfully\n";
        
        // Check if stored in database
        $userNotifications = $testUser->notifications()->count();
        echo "   - Total notifications for user: {$userNotifications}\n";
        
        $latestNotification = $testUser->notifications()->latest()->first();
        if ($latestNotification) {
            echo "   ✓ Latest notification in DB:\n";
            echo "     - ID: {$latestNotification->id}\n";
            echo "     - Type: {$latestNotification->type}\n";
            echo "     - Read at: " . ($latestNotification->read_at ? $latestNotification->read_at : 'Unread') . "\n";
            echo "     - Data: " . json_encode($latestNotification->data, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "   ✗ Test user not found\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error sending notification: {$e->getMessage()}\n";
}

// 5. Test NotificationService
echo "\n5. TEST NOTIFICATION SERVICE:\n";
try {
    $notificationService = app(NotificationService::class);
    echo "   ✓ NotificationService instantiated\n";
    
    // Test distributeAlertNotifications
    $recipients = $notificationService->distributeAlertNotifications($latestAlert);
    echo "   ✓ distributeAlertNotifications completed\n";
    echo "   - Recipients count: " . count($recipients) . "\n";
    
    foreach ($recipients as $recipient) {
        $user = User::find($recipient['user_id']);
        echo "     • {$user->name} ({$user->email}) - Role: {$recipient['role']}\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Error with NotificationService: {$e->getMessage()}\n";
}

// 6. Check queue configuration
echo "\n6. CHECK QUEUE CONFIGURATION:\n";
echo "   - Queue Driver: " . config('queue.default') . "\n";
echo "   - Environment: " . app()->environment() . "\n";

// 7. Check logs
echo "\n7. CHECK RECENT LOGS:\n";
$logPath = storage_path('logs/laravel.log');
if (file_exists($logPath)) {
    $logLines = array_slice(file($logPath), -10);
    foreach ($logLines as $line) {
        if (strpos($line, 'Notification') !== false || strpos($line, 'notification') !== false) {
            echo "   " . trim($line) . "\n";
        }
    }
} else {
    echo "   Log file not found\n";
}

echo "\n=== TROUBLESHOOTING RAILWAY ===\n";
echo "Kemungkinan masalah di Railway:\n";
echo "1. Queue not running (perlu queue worker)\n";
echo "2. Database connection issues\n";
echo "3. Missing environment variables\n";
echo "4. Memory/timeout issues\n";
echo "5. Spatie roles not seeded properly\n";

echo "\n=== SOLUSI UNTUK RAILWAY ===\n";
echo "1. Pastikan queue worker running: php artisan queue:work\n";
echo "2. Cek database notifications table exists\n";
echo "3. Cek environment variables sesuai\n";
echo "4. Add logging untuk debug di production\n";

echo "\n=== DEBUG SELESAI ===\n"; 