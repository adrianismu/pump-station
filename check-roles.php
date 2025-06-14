<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== CHECK USER ROLES ===\n";

$users = User::all();
echo "Total users: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo "User: {$user->email}\n";
    echo "  - Role field: {$user->role}\n";
    echo "  - hasRole('admin'): " . ($user->hasRole('admin') ? 'YES' : 'NO') . "\n";
    echo "  - hasRole('petugas'): " . ($user->hasRole('petugas') ? 'YES' : 'NO') . "\n";
    echo "  - isAdmin(): " . ($user->isAdmin() ? 'YES' : 'NO') . "\n";
    echo "\n";
}

echo "=== QUERY USERS BY ROLE ===\n";
try {
    $adminsByRole = User::role('admin')->get();
    echo "Users with 'admin' role: " . $adminsByRole->count() . "\n";
    
    $petugasByRole = User::role('petugas')->get();
    echo "Users with 'petugas' role: " . $petugasByRole->count() . "\n";
} catch (Exception $e) {
    echo "Error querying by role: " . $e->getMessage() . "\n";
}

echo "\n=== ASSIGN ROLES ===\n";
$admin = User::where('email', 'admin@pumpstation.com')->first();
if ($admin) {
    if (!$admin->hasRole('admin')) {
        $admin->assignRole('admin');
        echo "✓ Assigned 'admin' role to {$admin->email}\n";
    } else {
        echo "✓ {$admin->email} already has 'admin' role\n";
    }
}

$petugas = User::where('email', 'budi.santoso@pumpstation.com')->first();
if ($petugas && !$petugas->hasRole('petugas')) {
    $petugas->assignRole('petugas');
    echo "✓ Assigned 'petugas' role to {$petugas->email}\n";
} elseif ($petugas) {
    echo "✓ {$petugas->email} already has 'petugas' role\n";
}

echo "\n=== DONE ===\n"; 