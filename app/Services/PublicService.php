<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\EducationContent;
use App\Models\PumpHouse;
use App\Models\Report;
use App\Models\ThresholdSetting;
use App\Models\PumpHouseThresholdSetting;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PublicService
{
    protected $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    /**
     * Get data for landing page
     */
    public function getLandingPageData()
    {
        $pumpHouses = $this->getPumpHousesWithWaterLevel();
        $recentAlerts = $this->getRecentAlerts();
        $educationContent = $this->getRecentEducationContent();
        $stats = $this->getPublicStats();

        return [
            'pumpHouses' => $pumpHouses,
            'recentAlerts' => $recentAlerts,
            'educationContent' => $educationContent,
            'stats' => $stats,
        ];
    }

    /**
     * Create new report
     */
    public function createReport(Request $request)
    {
        try {
            $imageUrls = [];
            $cloudinaryIds = [];
            
            // Railway-specific: Handle image uploads with timeout protection
            if ($request->hasFile('images')) {
                try {
                    $uploadResults = $this->imageUploadService->uploadMultipleImages(
                        $request->file('images'), 
                        'reports'
                    );
                    
                    foreach ($uploadResults as $result) {
                        if (isset($result['url'])) {
                            $imageUrls[] = $result['url'];
                        }
                        if (isset($result['cloudinary_id']) && $result['cloudinary_id']) {
                            $cloudinaryIds[] = $result['cloudinary_id'];
                        }
                    }
                } catch (Exception $e) {
                    // Log image upload error but don't fail report creation
                    Log::error('Image upload failed in Railway for public report', [
                        'error' => $e->getMessage(),
                        'request_data' => $request->only(['pump_house_id', 'reporter_name', 'title'])
                    ]);
                    // Continue without images
                }
            }
            
            // Railway-specific: Handle nullable email properly
            $reporterEmail = $request->reporter_email;
            if (empty($reporterEmail) || $reporterEmail === '') {
                $reporterEmail = null;
            }
            
            $report = new Report();
            $report->pump_house_id = $request->pump_house_id;
            $report->reporter_name = $request->reporter_name;
            $report->reporter_email = $reporterEmail; // Properly handle null
            $report->reporter_phone = $request->reporter_phone;
            $report->title = $request->title;
            $report->description = $request->description;
            $report->location = $request->location_detail ?? '';
            $report->images = $imageUrls;
            $report->cloudinary_ids = $cloudinaryIds;
            $report->status = 'Belum Ditanggapi';
            
            // Railway-specific: Use explicit save with error handling
            if (!$report->save()) {
                throw new Exception('Failed to save report to database');
            }

            return $report;
            
        } catch (Exception $e) {
            Log::error('Public report creation failed in Railway', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->only([
                    'pump_house_id', 'reporter_name', 'reporter_email', 
                    'reporter_phone', 'title', 'description'
                ])
            ]);
            
            // Re-throw the exception to be handled by controller
            throw $e;
        }
    }

    /**
     * Get education content with filters
     */
    public function getEducationContent(Request $request)
    {
        $query = EducationContent::where('published', true);
        
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }
        
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        
        $educationContent = $query->orderBy('date', 'desc')
                                ->paginate(12)
                                ->withQueryString();

        return [
            'educationContent' => $educationContent,
            'filters' => [
                'search' => $request->search,
                'type' => $request->type,
            ],
        ];
    }

    /**
     * Get education content detail with related content
     */
    public function getEducationDetail($id)
    {
        $content = EducationContent::where('published', true)->findOrFail($id);
        
        $relatedContent = EducationContent::where('id', '!=', $id)
            ->where('published', true)
            ->where('type', $content->type)
            ->orderBy('date', 'desc')
            ->take(3)
            ->get();
        
        if ($relatedContent->count() < 3) {
            $additionalContent = EducationContent::where('id', '!=', $id)
                ->where('published', true)
                ->whereNotIn('id', $relatedContent->pluck('id')->toArray())
                ->orderBy('date', 'desc')
                ->take(3 - $relatedContent->count())
                ->get();
                
            $relatedContent = $relatedContent->concat($additionalContent);
        }

        return [
            'content' => $content,
            'relatedContent' => $relatedContent,
        ];
    }

    /**
     * Get pump houses for map with water level status
     */
    public function getPumpHousesForMap()
    {
        return PumpHouse::with(['waterLevelHistory' => function($query) {
            $query->latest()->limit(1);
        }])
        ->get()
        ->map(function ($pumpHouse) {
            $latestWaterLevel = $pumpHouse->waterLevelHistory->first();
            $waterLevel = $latestWaterLevel?->water_level ?? 0;
            $status = $this->getWaterLevelStatus($pumpHouse->id, $waterLevel);
            
            return [
                'id' => $pumpHouse->id,
                'name' => $pumpHouse->name,
                'address' => $pumpHouse->address,
                'lat' => $pumpHouse->lat,
                'lng' => $pumpHouse->lng,
                'status' => $pumpHouse->status,
                'pump_count' => $pumpHouse->pump_count,
                'capacity' => $pumpHouse->capacity,
                'current_water_level' => $waterLevel,
                'water_level_status' => $status,
                'last_recorded' => $latestWaterLevel?->recorded_at,
            ];
        });
    }

    /**
     * Get active alerts for public
     */
    public function getActiveAlerts()
    {
        return Alert::whereIn('severity', ['critical', 'high'])
            ->whereNotNull('public_message')
            ->where('is_active', true)
            ->where('created_at', '>', now()->subHours(3))
            ->latest()
            ->select('public_message', 'created_at', 'type', 'severity')
            ->get()
            ->map(function ($alert) {
                return [
                    'message' => $alert->public_message,
                    'type' => $alert->type,
                    'severity' => $alert->severity,
                    'created_at' => $alert->created_at->format('H:i'),
                    'time_ago' => $alert->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Get pump houses with water level data
     */
    protected function getPumpHousesWithWaterLevel()
    {
        return PumpHouse::with(['waterLevelHistory' => function($query) {
            $query->latest()->limit(1);
        }])
        ->select('id', 'name', 'address', 'lat', 'lng', 'status', 'pump_count', 'capacity')
        ->get()
        ->map(function ($pumpHouse) {
            $latestWaterLevel = $pumpHouse->waterLevelHistory->first();
            $waterLevel = $latestWaterLevel?->water_level ?? 0;
            $status = $this->getWaterLevelStatus($pumpHouse->id, $waterLevel);
            
            return [
                'id' => $pumpHouse->id,
                'name' => $pumpHouse->name,
                'location' => $pumpHouse->address,
                'address' => $pumpHouse->address,
                'latitude' => $pumpHouse->lat,
                'longitude' => $pumpHouse->lng,
                'lat' => $pumpHouse->lat,
                'lng' => $pumpHouse->lng,
                'status' => $pumpHouse->status,
                'pump_count' => $pumpHouse->pump_count,
                'capacity' => $pumpHouse->capacity,
                'current_water_level' => $waterLevel,
                'water_level_status' => $status,
                'last_recorded' => $latestWaterLevel?->recorded_at,
            ];
        });
    }

    /**
     * Get recent alerts
     */
    protected function getRecentAlerts()
    {
        return Alert::with('pump_house')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($alert) {
                return [
                    'id' => $alert->id,
                    'title' => $alert->title,
                    'description' => $alert->description,
                    'severity' => $alert->severity,
                    'pump_house_name' => $alert->pump_house->name,
                    'created_at' => $alert->created_at,
                ];
            });
    }

    /**
     * Get recent education content
     */
    protected function getRecentEducationContent()
    {
        return EducationContent::select('id', 'title', 'content', 'type', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();
    }

    /**
     * Get public statistics
     */
    protected function getPublicStats()
    {
        return [
            'total_pump_houses' => PumpHouse::count(),
            'active_pump_houses' => PumpHouse::where('status', 'Aktif')->count(),
            'total_pumps' => PumpHouse::sum('pump_count'),
            'recent_reports' => Report::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
        ];
    }

    /**
     * Get water level status based on thresholds
     */
    protected function getWaterLevelStatus($pumpHouseId, $waterLevel)
    {
        // Get pump house specific thresholds first
        $thresholds = PumpHouseThresholdSetting::where('pump_house_id', $pumpHouseId)
            ->where('is_active', true)
            ->orderBy('water_level', 'desc')
            ->get();

        // Fallback to global thresholds if no pump house specific thresholds
        if ($thresholds->isEmpty()) {
            $thresholds = ThresholdSetting::where('is_active', true)
                ->orderBy('water_level', 'desc')
                ->get();
        }

        // Find the appropriate threshold
        foreach ($thresholds as $threshold) {
            if ($waterLevel >= $threshold->water_level) {
                return [
                    'level' => strtolower($threshold->name),
                    'label' => $threshold->label,
                    'color' => $threshold->color,
                    'description' => $threshold->description,
                    'threshold_value' => $threshold->water_level,
                ];
            }
        }

        // Default to normal if no threshold matched
        return [
            'level' => 'normal',
            'label' => 'Normal',
            'color' => '#22c55e',
            'description' => 'Ketinggian air dalam batas normal',
            'threshold_value' => 0,
        ];
    }
} 