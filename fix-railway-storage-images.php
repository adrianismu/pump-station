<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use App\Models\Report;

echo "Railway Storage Image Fix\n";
echo "========================\n\n";

echo "Step 1: Create Storage Link\n";
echo "---------------------------\n";

try {
    // Run storage:link command
    Artisan::call('storage:link');
    echo "✅ Storage link command executed\n";
    echo Artisan::output();
    
    // Verify the link
    $publicStoragePath = public_path('storage');
    if (is_link($publicStoragePath) || is_dir($publicStoragePath)) {
        echo "✅ Storage accessible at: /storage\n";
    } else {
        echo "❌ Storage link failed\n";
    }
    
} catch (Exception $e) {
    echo "⚠️  Storage link error: " . $e->getMessage() . "\n";
}

echo "\n📁 Step 2: Ensure Directory Structure\n";
echo "------------------------------------\n";

$directories = [
    'reports',
    'education/thumbnails', 
    'education/infographics'
];

foreach ($directories as $dir) {
    $fullPath = storage_path("app/public/{$dir}");
    if (!File::exists($fullPath)) {
        File::makeDirectory($fullPath, 0755, true);
        echo "✅ Created: {$dir}\n";
    } else {
        echo "✅ Exists: {$dir}\n";
    }
}

echo "\n🔍 Step 3: Analyze Report Images\n";
echo "-------------------------------\n";

$reports = Report::whereNotNull('images')->get();
$localStorageCount = 0;
$cloudinaryCount = 0;
$brokenLinks = [];

foreach ($reports as $report) {
    $images = is_string($report->images) ? json_decode($report->images, true) : $report->images;
    
    if ($images && is_array($images)) {
        foreach ($images as $imageUrl) {
            if (str_contains($imageUrl, '/storage/')) {
                $localStorageCount++;
                
                // Check if file exists
                $relativePath = str_replace(asset('storage/'), '', $imageUrl);
                $fullPath = storage_path('app/public/' . $relativePath);
                
                if (!File::exists($fullPath)) {
                    $brokenLinks[] = [
                        'report_id' => $report->id,
                        'url' => $imageUrl,
                        'path' => $fullPath
                    ];
                }
            } else if (str_contains($imageUrl, 'cloudinary.com')) {
                $cloudinaryCount++;
            }
        }
    }
}

echo "📊 Image Statistics:\n";
echo "   Local storage: {$localStorageCount}\n";
echo "   Cloudinary: {$cloudinaryCount}\n";
echo "   Broken links: " . count($brokenLinks) . "\n";

if (count($brokenLinks) > 0) {
    echo "\n❌ Broken Image Links Found:\n";
    foreach (array_slice($brokenLinks, 0, 5) as $broken) {
        echo "   Report {$broken['report_id']}: {$broken['url']}\n";
    }
    if (count($brokenLinks) > 5) {
        echo "   ... and " . (count($brokenLinks) - 5) . " more\n";
    }
}

echo "\n🛠️  Step 4: Railway-Specific Fixes\n";
echo "----------------------------------\n";

// Check if running on Railway
if (env('RAILWAY_ENVIRONMENT')) {
    echo "🚂 Detected Railway environment\n";
    
    // Railway workaround: create physical directories instead of symlinks
    $publicStoragePhysical = public_path('storage');
    
    if (!File::exists($publicStoragePhysical)) {
        File::makeDirectory($publicStoragePhysical, 0755, true);
        echo "✅ Created public/storage directory\n";
    }
    
    // Create subdirectories in public/storage
    foreach ($directories as $dir) {
        $publicDir = $publicStoragePhysical . '/' . $dir;
        if (!File::exists($publicDir)) {
            File::makeDirectory($publicDir, 0755, true);
            echo "✅ Created public/storage/{$dir}\n";
        }
    }
    
    // Copy files from storage/app/public to public/storage (if any exist)
    $sourceBase = storage_path('app/public');
    foreach ($directories as $dir) {
        $sourceDir = $sourceBase . '/' . $dir;
        $targetDir = $publicStoragePhysical . '/' . $dir;
        
        if (File::exists($sourceDir)) {
            $files = File::files($sourceDir);
            foreach ($files as $file) {
                $targetFile = $targetDir . '/' . $file->getFilename();
                if (!File::exists($targetFile)) {
                    File::copy($file->getPathname(), $targetFile);
                }
            }
            echo "📁 Synced {$dir}: " . count($files) . " files\n";
        }
    }
    
} else {
    echo "💻 Local environment detected\n";
}

echo "\n💡 Step 5: Recommendations\n";
echo "-------------------------\n";

if ($localStorageCount > 0) {
    echo "⚠️  Found {$localStorageCount} images using local storage\n";
    echo "🎯 Recommendations for Railway:\n";
    echo "   1. Migrate images to Cloudinary for better reliability\n";
    echo "   2. Update ImageUploadService to prioritize Cloudinary\n";
    echo "   3. Run this script after each deployment\n";
    echo "\n📝 To migrate existing images:\n";
    echo "   - Use ImageUploadService->uploadFromUrl() for each local image\n";
    echo "   - Update database with new Cloudinary URLs\n";
    echo "   - Remove old local files\n";
} else {
    echo "✅ All images using Cloudinary - optimal for Railway!\n";
}

echo "\n🧪 Step 6: Test Image Access\n";
echo "---------------------------\n";

// Test a few image URLs
$testReport = Report::whereNotNull('images')->first();
if ($testReport) {
    $images = is_string($testReport->images) ? json_decode($testReport->images, true) : $testReport->images;
    
    if ($images && is_array($images)) {
        $testUrl = $images[0];
        echo "🔗 Testing URL: {$testUrl}\n";
        
        if (str_contains($testUrl, '/storage/')) {
            $relativePath = str_replace(asset('storage/'), '', $testUrl);
            $localPath = storage_path('app/public/' . $relativePath);
            $publicPath = public_path('storage/' . $relativePath);
            
            echo "   📁 Local path: " . (File::exists($localPath) ? "✅ EXISTS" : "❌ MISSING") . "\n";
            echo "   🌐 Public path: " . (File::exists($publicPath) ? "✅ EXISTS" : "❌ MISSING") . "\n";
            echo "   🔗 URL should be: " . asset('storage/' . $relativePath) . "\n";
        } else {
            echo "   ☁️  Cloudinary URL - should work directly\n";
        }
    }
}

echo "\n🎯 FINAL STATUS\n";
echo "==============\n";

if (env('RAILWAY_ENVIRONMENT')) {
    echo "🚂 Railway Environment:\n";
    echo "   - Storage directories created\n";
    echo "   - Files synced to public/storage\n";
    echo "   - Ready for testing\n";
    
    if ($localStorageCount > 0) {
        echo "   ⚠️  Still using local storage - consider Cloudinary migration\n";
    } else {
        echo "   ✅ Using Cloudinary - optimal setup\n";
    }
} else {
    echo "💻 Local Environment:\n";
    echo "   - Storage link should work normally\n";
    echo "   - Test by accessing: " . asset('storage/') . "\n";
}

echo "\n🏁 Fix completed at " . now()->toDateTimeString() . "\n";

?> 