<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Report;
use App\Services\ImageUploadService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

echo "🚀 MIGRATE REPORT IMAGES TO CLOUDINARY\n";
echo "======================================\n\n";

$imageUploadService = new ImageUploadService();
$migratedCount = 0;
$errorCount = 0;
$skippedCount = 0;

echo "🔍 Finding reports with local storage images...\n";

// Get all reports with images and filter for local storage
$reports = Report::whereNotNull('images')->get()->filter(function($report) {
    $images = is_string($report->images) ? json_decode($report->images, true) : $report->images;
    if ($images && is_array($images)) {
        foreach ($images as $imageUrl) {
            if (str_contains($imageUrl, '/storage/')) {
                return true;
            }
        }
    }
    return false;
});

echo "Found {$reports->count()} reports with local storage images\n\n";

foreach ($reports as $report) {
    echo "📋 Processing Report ID: {$report->id}\n";
    echo "   Title: {$report->title}\n";
    
    $images = is_string($report->images) ? json_decode($report->images, true) : $report->images;
    $newImages = [];
    $newCloudinaryIds = [];
    $hasChanges = false;
    
    if ($images && is_array($images)) {
        foreach ($images as $index => $imageUrl) {
            echo "   🖼️  Image {$index}: ";
            
            // Skip if already Cloudinary
            if (str_contains($imageUrl, 'cloudinary.com')) {
                echo "Already Cloudinary ✅\n";
                $newImages[] = $imageUrl;
                continue;
            }
            
            // Skip if not local storage
            if (!str_contains($imageUrl, '/storage/')) {
                echo "Not local storage, skipping\n";
                $newImages[] = $imageUrl;
                continue;
            }
            
            // Extract local file path
            $relativePath = str_replace([
                'http://127.0.0.1:8000/storage/',
                'http://localhost/storage/',
                asset('storage/') . '/'
            ], '', $imageUrl);
            
            $localFilePath = storage_path('app/public/' . $relativePath);
            
            if (!File::exists($localFilePath)) {
                echo "File missing locally ❌\n";
                echo "      Looking for: {$localFilePath}\n";
                $errorCount++;
                // Keep original URL even if file missing
                $newImages[] = $imageUrl;
                continue;
            }
            
            try {
                // Create temporary UploadedFile from local file
                $fileInfo = pathinfo($localFilePath);
                $mimeType = mime_content_type($localFilePath);
                
                // Create a temporary copy
                $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.' . $fileInfo['extension'];
                File::copy($localFilePath, $tempPath);
                
                // Create UploadedFile instance
                $uploadedFile = new \Illuminate\Http\UploadedFile(
                    $tempPath,
                    $fileInfo['basename'],
                    $mimeType,
                    null,
                    true
                );
                
                // Upload to Cloudinary
                $result = $imageUploadService->uploadImage($uploadedFile, 'reports');
                
                // Clean up temp file
                unlink($tempPath);
                
                if (isset($result['url'])) {
                    echo "Migrated to Cloudinary ✅\n";
                    echo "      New URL: {$result['url']}\n";
                    
                    $newImages[] = $result['url'];
                    if (isset($result['cloudinary_id'])) {
                        $newCloudinaryIds[] = $result['cloudinary_id'];
                    }
                    
                    $hasChanges = true;
                    $migratedCount++;
                    
                    // Delete old local file
                    File::delete($localFilePath);
                    echo "      Old file deleted ✅\n";
                } else {
                    echo "Upload failed ❌\n";
                    $newImages[] = $imageUrl; // Keep original
                    $errorCount++;
                }
                
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage() . " ❌\n";
                $newImages[] = $imageUrl; // Keep original
                $errorCount++;
            }
        }
        
        // Update report if there are changes
        if ($hasChanges) {
            try {
                $report->images = $newImages;
                if (!empty($newCloudinaryIds)) {
                    $report->cloudinary_ids = $newCloudinaryIds;
                }
                $report->save();
                
                echo "   💾 Report updated in database ✅\n";
            } catch (Exception $e) {
                echo "   💾 Database update failed: " . $e->getMessage() . " ❌\n";
                $errorCount++;
            }
        } else {
            $skippedCount++;
        }
    }
    
    echo "\n";
}

echo "📊 MIGRATION SUMMARY\n";
echo "===================\n";
echo "Reports processed: {$reports->count()}\n";
echo "Images migrated: {$migratedCount}\n";
echo "Errors: {$errorCount}\n";
echo "Skipped: {$skippedCount}\n\n";

echo "🔍 VERIFICATION\n";
echo "==============\n";

// Verify migration by checking again
$remainingReports = Report::whereNotNull('images')->get()->filter(function($report) {
    $images = is_string($report->images) ? json_decode($report->images, true) : $report->images;
    if ($images && is_array($images)) {
        foreach ($images as $imageUrl) {
            if (str_contains($imageUrl, '/storage/')) {
                return true;
            }
        }
    }
    return false;
});

$cloudinaryReports = Report::whereNotNull('images')->get()->filter(function($report) {
    $images = is_string($report->images) ? json_decode($report->images, true) : $report->images;
    if ($images && is_array($images)) {
        foreach ($images as $imageUrl) {
            if (str_contains($imageUrl, 'cloudinary.com')) {
                return true;
            }
        }
    }
    return false;
});

echo "Reports still using local storage: {$remainingReports->count()}\n";
echo "Reports using Cloudinary: {$cloudinaryReports->count()}\n";

if ($remainingReports->count() == 0) {
    echo "\n🎉 ALL REPORTS MIGRATED TO CLOUDINARY!\n";
    echo "Railway deployment should now work properly.\n";
} else {
    echo "\n⚠️  Some reports still using local storage.\n";
    echo "Check error logs and re-run migration if needed.\n";
}

echo "\n✅ Migration completed at " . now()->toDateTimeString() . "\n";

?> 