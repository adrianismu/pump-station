<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "ENVIRONMENT VARIABLES ANALYSIS\n";
echo "==============================\n\n";

echo "All CLOUDINARY env vars:\n";
$envVars = $_ENV;
foreach ($envVars as $key => $value) {
    if (str_contains(strtoupper($key), 'CLOUDINARY')) {
        $safeValue = str_contains(strtoupper($key), 'SECRET') ? '[HIDDEN]' : $value;
        echo "{$key}: {$safeValue}\n";
    }
}

echo "\n";

echo "System getenv() results:\n";
$cloudinaryKeys = [
    'CLOUDINARY_CLOUD_NAME',
    'CLOUDINARY_API_KEY', 
    'CLOUDINARY_API_SECRET',
    'CLOUDINARY_URL'
];

foreach ($cloudinaryKeys as $key) {
    $value = getenv($key);
    $safeValue = str_contains($key, 'SECRET') ? ($value ? '[SET]' : 'NOT SET') : ($value ?: 'NOT SET');
    echo "{$key}: {$safeValue}\n";
}

echo "\n";

echo "Laravel env() function:\n";
foreach ($cloudinaryKeys as $key) {
    $value = env($key);
    $safeValue = str_contains($key, 'SECRET') ? ($value ? '[SET]' : 'NOT SET') : ($value ?: 'NOT SET');
    echo "{$key}: {$safeValue}\n";
}

echo "\n";

echo "Config values:\n";
$configKeys = [
    'cloudinary.cloud_name',
    'cloudinary.api_key',
    'cloudinary.api_secret',
    'cloudinary.cloud_url'
];

foreach ($configKeys as $key) {
    $value = config($key);
    $safeValue = str_contains($key, 'secret') ? ($value ? '[SET]' : 'NOT SET') : ($value ?: 'NOT SET');
    echo "{$key}: {$safeValue}\n";
}

echo "\n";

// Check if there's a .env file
if (file_exists('.env')) {
    echo "✅ .env file exists\n";
} else {
    echo "❌ .env file not found\n";
}

if (file_exists('.env.example')) {
    echo "✅ .env.example file exists\n";
} else {
    echo "❌ .env.example file not found\n";
}

?> 