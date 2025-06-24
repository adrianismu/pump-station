<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Report;
use Illuminate\Support\Facades\File;

echo "Testing Railway Image Access\n";
echo "===========================\n\n";

echo "1. Checking Directory Structure\n";
echo "-------------------------------\n";

$directories = [
    'storage/app/public/reports',
    'storage/app/public/education/thumbnails',
    'public/storage',
    'public/storage/reports',
    'public/storage/education/thumbnails'
];

foreach ($directories as $dir) {
    $exists = File::exists($dir);
    $status = $exists ? "EXISTS" : "MISSING";
    echo "{$dir}: {$status}\n";
    
    if ($exists && is_dir($dir)) {
        $files = File::files($dir);
        echo "   Files: " . count($files) . "\n";
    }
}

echo "\n2. Checking Storage Link\n";
echo "-----------------------\n";

$publicStorage = public_path('storage');
if (is_link($publicStorage)) {
    echo "✅ Storage link exists\n";
} else if (is_dir($publicStorage)) {
    echo "✅ Storage directory exists\n";
} else {
    echo "❌ Storage not accessible\n";
}

echo "\n3. Analyzing Report Images\n";
echo "-------------------------\n";

$reports = Report::whereNotNull('images')->take(3)->get();
echo "Sample reports: {$reports->count()}\n\n";

foreach ($reports as $report) {
    echo "Report {$report->id}: {$report->title}\n";
    $images = is_string($report->images) ? json_decode($report->images, true) : $report->images;
    
    if ($images && is_array($images)) {
        foreach ($images as $index => $imageUrl) {
            if (str_contains($imageUrl, 'cloudinary.com')) {
                echo "  Image {$index}: Cloudinary\n";
            } else if (str_contains($imageUrl, '/storage/')) {
                echo "  Image {$index}: Local Storage\n";
                
                $relativePath = str_replace([
                    'http://127.0.0.1:8000/storage/',
                    'http://localhost/storage/',
                    asset('storage/') . '/'
                ], '', $imageUrl);
                
                $storageFile = storage_path('app/public/' . $relativePath);
                $publicFile = public_path('storage/' . $relativePath);
                
                echo "     Storage: " . (File::exists($storageFile) ? "EXISTS" : "MISSING") . "\n";
                echo "     Public: " . (File::exists($publicFile) ? "EXISTS" : "MISSING") . "\n";
            }
        }
    }
    echo "\n";
}

echo "4. Summary\n";
echo "---------\n";

$localStorageCount = Report::whereNotNull('images')->get()->filter(function($report) {
    $images = is_string($report->images) ? json_decode($report->images, true) : $report->images;
    if ($images && is_array($images)) {
        foreach ($images as $imageUrl) {
            if (str_contains($imageUrl, '/storage/')) {
                return true;
            }
        }
    }
    return false;
})->count();

echo "Reports with local storage: {$localStorageCount}\n";

if ($localStorageCount > 0) {
    echo "⚠️  Railway deployment needs storage fix\n";
    echo "Use start-railway-fixed.sh for deployment\n";
} else {
    echo "✅ Railway ready - all using Cloudinary\n";
}

echo "\nTest completed: " . now()->toDateTimeString() . "\n";

?> 