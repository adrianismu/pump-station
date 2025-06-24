<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Report;

echo "🔧 RAILWAY STORAGE LINK FIX\n";
echo "===========================\n\n";

try {
    echo "1. 📂 CHECKING STORAGE DIRECTORY STRUCTURE\n";
    echo "-------------------------------------------\n";
    
    $publicStoragePath = public_path('storage');
    $appPublicPath = storage_path('app/public');
    
    echo "Public storage path: {$publicStoragePath}\n";
    echo "App public path: {$appPublicPath}\n";
    
    // Check if storage link exists
    if (is_link($publicStoragePath)) {
        echo "✅ Storage link exists\n";
    } else {
        echo "❌ Storage link missing\n";
        
        if (File::exists($publicStoragePath)) {
            echo "🗑️  Removing existing storage directory\n";
            File::deleteDirectory($publicStoragePath);
        }
        
        echo "🔗 Creating storage link\n";
        if (symlink($appPublicPath, $publicStoragePath)) {
            echo "✅ Storage link created\n";
        } else {
            echo "❌ Failed to create link\n";
        }
    }
    
    echo "\n2. 📋 CHECKING REPORT IMAGES STORAGE\n";
    echo "------------------------------------\n";
    
    // Check reports directory
    $reportsDir = storage_path('app/public/reports');
    if (File::exists($reportsDir)) {
        echo "✅ Reports directory exists: {$reportsDir}\n";
        $files = File::files($reportsDir);
        echo "   Files count: " . count($files) . "\n";
    } else {
        echo "⚠️  Reports directory does not exist, creating...\n";
        File::makeDirectory($reportsDir, 0755, true);
        echo "✅ Reports directory created\n";
    }
    
    // Check education directory for comparison
    $educationDir = storage_path('app/public/education');
    if (File::exists($educationDir)) {
        echo "✅ Education directory exists: {$educationDir}\n";
        $files = File::allFiles($educationDir);
        echo "   Files count: " . count($files) . "\n";
    }
    
    echo "\n3. 🔍 ANALYZING REPORT IMAGES IN DATABASE\n";
    echo "------------------------------------------\n";
    
    $reports = Report::whereNotNull('images')->take(10)->get();
    echo "Reports with images: " . $reports->count() . "\n\n";
    
    foreach ($reports as $report) {
        echo "Report ID: {$report->id}\n";
        $images = is_string($report->images) ? json_decode($report->images, true) : $report->images;
        
        if ($images && is_array($images)) {
            foreach ($images as $index => $imageUrl) {
                echo "  Image {$index}: {$imageUrl}\n";
                
                // Check if it's a local storage URL
                if (str_contains($imageUrl, '/storage/')) {
                    $relativePath = str_replace(asset('storage/'), '', $imageUrl);
                    $fullPath = storage_path('app/public/' . $relativePath);
                    
                    if (File::exists($fullPath)) {
                        echo "    ✅ File exists locally\n";
                    } else {
                        echo "    ❌ File missing locally: {$fullPath}\n";
                    }
                    
                    // Check if accessible via web
                    $webUrl = asset('storage/' . $relativePath);
                    echo "    🌐 Web URL: {$webUrl}\n";
                } else if (str_contains($imageUrl, 'cloudinary.com')) {
                    echo "    ☁️  Cloudinary URL - should be accessible\n";
                } else {
                    echo "    ⚠️  Unknown URL format\n";
                }
            }
        }
        echo "\n";
    }
    
    echo "4. 🛠️  RAILWAY-SPECIFIC FIXES\n";
    echo "-----------------------------\n";
    
    // Check Railway environment
    if (env('RAILWAY_ENVIRONMENT')) {
        echo "✅ Running on Railway environment\n";
        
        // Railway doesn't support symlinks well, we need to copy files
        if (!File::exists($publicStoragePath) || !is_link($publicStoragePath)) {
            echo "🔄 Railway fix: Creating storage directory structure\n";
            
            // Create public/storage directory
            if (!File::exists($publicStoragePath)) {
                File::makeDirectory($publicStoragePath, 0755, true);
            }
            
            // Create subdirectories
            $subdirs = ['reports', 'education/thumbnails', 'education/infographics'];
            foreach ($subdirs as $subdir) {
                $publicSubdir = $publicStoragePath . '/' . $subdir;
                if (!File::exists($publicSubdir)) {
                    File::makeDirectory($publicSubdir, 0755, true);
                    echo "   ✅ Created: {$publicSubdir}\n";
                }
            }
            
            echo "✅ Railway storage structure created\n";
        }
    } else {
        echo "ℹ️  Not running on Railway (local environment)\n";
    }
    
    echo "\n5. 🔧 ALTERNATIVE SOLUTION: UPDATE URLS\n";
    echo "--------------------------------------\n";
    
    // Check if we need to update report URLs to use Cloudinary
    $localStorageReports = Report::whereNotNull('images')
        ->whereRaw("JSON_EXTRACT(images, '$[0]') LIKE '%/storage/%'")
        ->count();
        
    if ($localStorageReports > 0) {
        echo "⚠️  Found {$localStorageReports} reports with local storage URLs\n";
        echo "💡 Recommendation: Migrate to Cloudinary for Railway compatibility\n";
        
        // Show migration command
        echo "\n📝 Migration commands to run:\n";
        echo "   php artisan storage:link\n";
        echo "   php migrate-images-to-cloudinary.php\n";
    } else {
        echo "✅ All reports using Cloudinary URLs\n";
    }
    
    echo "\n6. 🎯 SUMMARY AND RECOMMENDATIONS\n";
    echo "=================================\n";
    
    echo "Problem: Report images return 404 on Railway\n";
    echo "Cause: Storage link not working properly on Railway\n\n";
    
    echo "Solutions:\n";
    echo "1. ✅ Use Cloudinary for all new uploads (already implemented)\n";
    echo "2. 🔧 Migrate existing local storage images to Cloudinary\n";
    echo "3. 🛠️  Ensure storage:link is run on Railway deployment\n";
    echo "4. 📂 Create proper directory structure in public/storage\n\n";
    
    echo "Next steps:\n";
    echo "- Run 'php artisan storage:link' on Railway\n";
    echo "- Migrate existing report images to Cloudinary\n";
    echo "- Update image URLs in database\n\n";
    
} catch (Exception $e) {
    echo "💥 ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "🏁 Storage fix analysis completed at " . now()->toDateTimeString() . "\n"; 