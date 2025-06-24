<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ImageUploadService;

echo "🔍 CLOUDINARY CONFIGURATION CHECK\n";
echo "=================================\n\n";

echo "Environment Variables:\n";
echo "CLOUDINARY_CLOUD_NAME: " . (env('CLOUDINARY_CLOUD_NAME') ?: 'NOT SET') . "\n";
echo "CLOUDINARY_API_KEY: " . (env('CLOUDINARY_API_KEY') ?: 'NOT SET') . "\n";
echo "CLOUDINARY_API_SECRET: " . (env('CLOUDINARY_API_SECRET') ? 'SET' : 'NOT SET') . "\n";
echo "CLOUDINARY_URL: " . (env('CLOUDINARY_URL') ?: 'NOT SET') . "\n\n";

echo "Config Values:\n";
echo "cloudinary.cloud_url: " . (config('cloudinary.cloud_url') ?: 'NOT SET') . "\n";
echo "cloudinary.cloud_name: " . (config('cloudinary.cloud_name') ?: 'NOT SET') . "\n";
echo "cloudinary.api_key: " . (config('cloudinary.api_key') ?: 'NOT SET') . "\n";
echo "cloudinary.api_secret: " . (config('cloudinary.api_secret') ? 'SET' : 'NOT SET') . "\n\n";

$imageUploadService = new ImageUploadService();

echo "Testing ImageUploadService:\n";

// Test the isCloudinaryConfigured method through reflection
$reflection = new ReflectionClass($imageUploadService);
$method = $reflection->getMethod('isCloudinaryConfigured');
$method->setAccessible(true);
$isConfigured = $method->invoke($imageUploadService);

echo "isCloudinaryConfigured(): " . ($isConfigured ? 'TRUE' : 'FALSE') . "\n\n";

if (!$isConfigured) {
    echo "❌ Cloudinary is NOT configured properly!\n";
    echo "This is why images are falling back to local storage.\n\n";
    
    echo "To fix this, ensure you have these in your .env file:\n";
    echo "CLOUDINARY_CLOUD_NAME=your_cloud_name\n";
    echo "CLOUDINARY_API_KEY=your_api_key\n";
    echo "CLOUDINARY_API_SECRET=your_api_secret\n\n";
    
    echo "OR set this single variable:\n";
    echo "CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name\n";
} else {
    echo "✅ Cloudinary is configured properly!\n";
    echo "Images should upload to Cloudinary.\n";
}

?> 