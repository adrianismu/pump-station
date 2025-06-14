<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== ASSIGN SPATIE ROLES ===\n";

$users = User::all();
$assigned = 0;

foreach ($users as $user) {
    if ($user->role === 'admin' && !$user->hasRole('admin')) {
        $user->assignRole('admin');
        echo "✓ Assigned 'admin' role to: {$user->email}\n";
        $assigned++;
    } elseif ($user->role === 'petugas' && !$user->hasRole('petugas')) {
        $user->assignRole('petugas');
        echo "✓ Assigned 'petugas' role to: {$user->email}\n";
        $assigned++;
    }
}

echo "\nTotal roles assigned: {$assigned}\n";

echo "\n=== VERIFY ASSIGNMENTS ===\n";
$admins = User::role('admin')->get();
$petugas = User::role('petugas')->get();

echo "Admin users: " . $admins->count() . "\n";
foreach ($admins as $admin) {
    echo "  - {$admin->email}\n";
}

echo "Petugas users: " . $petugas->count() . "\n";
foreach ($petugas as $user) {
    echo "  - {$user->email}\n";
}

echo "\n=== DONE ===\n"; 