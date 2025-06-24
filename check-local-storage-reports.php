<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Report;

echo "🔍 CHECKING LOCAL STORAGE REPORTS\n";
echo "=================================\n\n";

$reports = Report::whereNotNull('images')->get();

echo "Total reports with images: {$reports->count()}\n\n";

$localStorageReports = [];
$cloudinaryReports = [];
$otherReports = [];

foreach ($reports as $report) {
    $images = is_string($report->images) ? json_decode($report->images, true) : $report->images;
    
    if ($images && is_array($images)) {
        $hasLocalStorage = false;
        $hasCloudinary = false;
        
        foreach ($images as $imageUrl) {
            if (str_contains($imageUrl, '/storage/')) {
                $hasLocalStorage = true;
            } else if (str_contains($imageUrl, 'cloudinary.com')) {
                $hasCloudinary = true;
            }
        }
        
        if ($hasLocalStorage) {
            $localStorageReports[] = $report;
        } else if ($hasCloudinary) {
            $cloudinaryReports[] = $report;
        } else {
            $otherReports[] = $report;
        }
    }
}

echo "📊 BREAKDOWN:\n";
echo "Local storage reports: " . count($localStorageReports) . "\n";
echo "Cloudinary reports: " . count($cloudinaryReports) . "\n";
echo "Other reports: " . count($otherReports) . "\n\n";

if (count($localStorageReports) > 0) {
    echo "🔍 LOCAL STORAGE REPORTS:\n";
    echo "========================\n";
    
    foreach ($localStorageReports as $report) {
        echo "Report ID: {$report->id}\n";
        echo "Title: {$report->title}\n";
        
        $images = is_string($report->images) ? json_decode($report->images, true) : $report->images;
        foreach ($images as $index => $imageUrl) {
            echo "  Image {$index}: {$imageUrl}\n";
        }
        echo "\n";
    }
} else {
    echo "✅ No reports using local storage found\n";
}

if (count($cloudinaryReports) > 0) {
    echo "☁️  CLOUDINARY REPORTS (sample):\n";
    echo "==============================\n";
    
    foreach (array_slice($cloudinaryReports, 0, 3) as $report) {
        echo "Report ID: {$report->id}\n";
        echo "Title: {$report->title}\n";
        
        $images = is_string($report->images) ? json_decode($report->images, true) : $report->images;
        foreach ($images as $index => $imageUrl) {
            echo "  Image {$index}: " . substr($imageUrl, 0, 80) . "...\n";
        }
        echo "\n";
    }
}

echo "✅ Analysis completed\n";

?> 