<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== DEBUG USER ACCESS ===" . PHP_EOL;

$user = User::where('email', 'petugas.test@example.com')->first();

if ($user) {
    echo "User: " . $user->name . PHP_EOL;
    echo "Email: " . $user->email . PHP_EOL;
    echo "Role: " . $user->role . PHP_EOL;
    echo "Is Petugas: " . ($user->isPetugas() ? 'Yes' : 'No') . PHP_EOL;
    echo "Is Admin: " . ($user->isAdmin() ? 'Yes' : 'No') . PHP_EOL;
    
    $accessibleIds = $user->getAccessiblePumpHouseIds();
    echo "Accessible Pump House IDs: " . json_encode($accessibleIds) . PHP_EOL;
    echo "Has pump house access: " . (empty($accessibleIds) ? 'No' : 'Yes') . PHP_EOL;
    
    // Check specific pump house access
    if (!empty($accessibleIds)) {
        $firstPumpHouseId = $accessibleIds[0];
        echo "First pump house ID: " . $firstPumpHouseId . PHP_EOL;
        echo "Has read access to first pump house: " . ($user->hasAccessToPumpHouse($firstPumpHouseId, 'read') ? 'Yes' : 'No') . PHP_EOL;
        echo "Has write access to first pump house: " . ($user->hasAccessToPumpHouse($firstPumpHouseId, 'write') ? 'Yes' : 'No') . PHP_EOL;
    }
    
    // Check pivot data
    $pumpHouses = $user->allPumpHouses()->get();
    echo "Total assigned pump houses: " . $pumpHouses->count() . PHP_EOL;
    
    foreach ($pumpHouses as $pumpHouse) {
        echo "  - Pump House: " . $pumpHouse->name . PHP_EOL;
        echo "    Access Level: " . $pumpHouse->pivot->access_level . PHP_EOL;
        echo "    Is Active: " . ($pumpHouse->pivot->is_active ? 'Yes' : 'No') . PHP_EOL;
        echo "    Expires At: " . ($pumpHouse->pivot->expires_at ?? 'Never') . PHP_EOL;
    }
    
} else {
    echo "User not found!" . PHP_EOL;
}

echo PHP_EOL . "=== TEST MIDDLEWARE CONDITION ===" . PHP_EOL;

if ($user) {
    $condition1 = !$user->isPetugas();
    $condition2 = $user->getAccessiblePumpHouseIds() === [];
    $combined = $condition1 || $condition2;
    
    echo "!user->isPetugas(): " . ($condition1 ? 'true' : 'false') . PHP_EOL;
    echo "getAccessiblePumpHouseIds() === []: " . ($condition2 ? 'true' : 'false') . PHP_EOL;
    echo "Combined condition (should be false for access): " . ($combined ? 'true (BLOCKED)' : 'false (ALLOWED)') . PHP_EOL;
}

echo "Debug completed!" . PHP_EOL; 