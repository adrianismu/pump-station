<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== CHECK NOTIFICATIONS IN DATABASE ===\n";

try {
    $admin = User::where('email', 'admin@pumpstation.com')->first();
    if ($admin) {
        $count = $admin->notifications()->count();
        echo "Admin notifications count: {$count}\n";
        
        if ($count > 0) {
            $latest = $admin->notifications()->latest()->first();
            echo "Latest notification:\n";
            echo "  - Type: {$latest->type}\n";
            echo "  - Read: " . ($latest->read_at ? 'YES' : 'NO') . "\n";
            echo "  - Created: {$latest->created_at}\n";
            echo "  - Data: " . json_encode($latest->data, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "Admin user not found\n";
    }

    $totalNotifications = \Illuminate\Support\Facades\DB::table('notifications')->count();
    echo "\nTotal notifications in database: {$totalNotifications}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n"; 