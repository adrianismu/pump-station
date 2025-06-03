<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test Route Rename: Detail -> Show ===\n\n";

// Test 1: Check if admin.database.show route exists
echo "1. Testing admin.database.show route...\n";
try {
    $route = Route::getRoutes()->getByName('admin.database.show');
    if ($route) {
        echo "   ✓ Route 'admin.database.show' exists\n";
        echo "   URI: " . $route->uri() . "\n";
        echo "   Method: " . implode(', ', $route->methods()) . "\n";
        echo "   Controller: " . $route->getControllerClass() . '@' . $route->getActionMethod() . "\n";
    } else {
        echo "   ✗ Route 'admin.database.show' NOT FOUND\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Check if admin.map route exists  
echo "2. Testing admin.map route...\n";
try {
    $route = Route::getRoutes()->getByName('admin.map');
    if ($route) {
        echo "   ✓ Route 'admin.map' exists\n";
        echo "   URI: " . $route->uri() . "\n";
        echo "   Method: " . implode(', ', $route->methods()) . "\n";
        echo "   Controller: " . $route->getControllerClass() . '@' . $route->getActionMethod() . "\n";
    } else {
        echo "   ✗ Route 'admin.map' NOT FOUND\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Check if old routes are removed
echo "3. Testing if old routes are removed...\n";

$oldRoutes = ['admin.database.detail', 'admin.database.map'];
foreach ($oldRoutes as $routeName) {
    try {
        $route = Route::getRoutes()->getByName($routeName);
        if ($route) {
            echo "   ✗ Old route '$routeName' still exists (should be removed)\n";
        } else {
            echo "   ✓ Old route '$routeName' properly removed\n";
        }
    } catch (Exception $e) {
        echo "   ✓ Old route '$routeName' properly removed\n";
    }
}

echo "\n";

// Test 4: Check file structure
echo "4. Testing file structure...\n";

$files = [
    'resources/js/Pages/Admin/Database/Show.vue' => 'should exist',
    'resources/js/Pages/Admin/Database/Detail.vue' => 'should NOT exist',
    'resources/js/Pages/Admin/Map.vue' => 'should exist',
    'resources/js/Pages/Admin/Database/Map.vue' => 'should NOT exist'
];

foreach ($files as $file => $expectation) {
    $exists = file_exists($file);
    $shouldExist = strpos($expectation, 'should exist') !== false;
    
    if ($exists === $shouldExist) {
        echo "   ✓ $file: $expectation ✓\n";
    } else {
        echo "   ✗ $file: $expectation ✗\n";
    }
}

echo "\n";

// Test 5: Test DatabaseController methods
echo "5. Testing DatabaseController methods...\n";

try {
    $controller = new \App\Http\Controllers\Admin\DatabaseController(
        app(\App\Services\ImageUploadService::class)
    );
    
    $reflection = new ReflectionClass($controller);
    
    if ($reflection->hasMethod('show')) {
        echo "   ✓ DatabaseController::show() method exists\n";
    } else {
        echo "   ✗ DatabaseController::show() method NOT FOUND\n";
    }
    
    if ($reflection->hasMethod('detail')) {
        echo "   ✗ DatabaseController::detail() method still exists (should be removed)\n";
    } else {
        echo "   ✓ DatabaseController::detail() method properly removed/renamed\n";
    }
    
    if ($reflection->hasMethod('map')) {
        echo "   ✓ DatabaseController::map() method exists\n";
    } else {
        echo "   ✗ DatabaseController::map() method NOT FOUND\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Error testing controller: " . $e->getMessage() . "\n";
}

echo "\n=== Test completed ===\n"; 