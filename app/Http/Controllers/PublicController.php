<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\EducationContent;
use App\Models\PumpHouse;
use App\Models\Report;
use App\Models\WaterLevelHistory;
use App\Models\ThresholdSetting;
use App\Models\PumpHouseThresholdSetting;
use App\Services\ImageUploadService;
use App\Services\PublicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Carbon\Carbon;

class PublicController extends Controller
{
    protected $imageUploadService;
    protected $publicService;

    public function __construct(
        ImageUploadService $imageUploadService,
        PublicService $publicService
    ) {
        $this->imageUploadService = $imageUploadService;
        $this->publicService = $publicService;
    }

    /**
     * Landing page untuk masyarakat
     */
    public function index()
    {
        $data = $this->publicService->getLandingPageData();
        
        return Inertia::render('Public/Landing', $data);
    }
    
    /**
     * Halaman laporan publik
     */
    public function reports()
    {
        $pumpHouses = PumpHouse::select('id', 'name', 'address')->get();
        
        return Inertia::render('Public/Reports', [
            'pumpHouses' => $pumpHouses,
        ]);
    }
    
    /**
     * Submit laporan dari masyarakat
     */
    public function submitReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pump_house_id' => 'required|exists:pump_houses,id',
            'reporter_name' => 'required|string|max:255',
            'reporter_email' => 'nullable|email|max:255',
            'reporter_phone' => 'nullable|string|max:20',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'location_detail' => 'nullable|string|max:500',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $report = $this->publicService->createReport($request);
        
        return redirect()->route('public.report-success')
            ->with('success', 'Laporan berhasil dikirim!');
    }
    
    /**
     * Halaman sukses setelah submit laporan
     */
    public function reportSuccess()
    {
        return Inertia::render('Public/ReportSuccess');
    }
    
    /**
     * Halaman edukasi publik
     */
    public function education(Request $request)
    {
        $data = $this->publicService->getEducationContent($request);
        
        return Inertia::render('Public/Education', $data);
    }
    
    /**
     * Detail konten edukasi
     */
    public function educationDetail($id)
    {
        $data = $this->publicService->getEducationDetail($id);
        
        return Inertia::render('Public/EducationDetail', $data);
    }
    
    /**
     * Halaman peta publik
     */
    public function map()
    {
        $pumpHouses = $this->publicService->getPumpHousesForMap();
        
        return Inertia::render('Public/Map', [
            'pumpHouses' => $pumpHouses,
        ]);
    }
    
    /**
     * Get active public alerts (Endpoint untuk landing page)
     */
    public function getActiveAlerts()
    {
        $alerts = $this->publicService->getActiveAlerts();

        return response()->json($alerts);
    }

    /**
     * Get water level status berdasarkan threshold
     */
    private function getWaterLevelStatus($pumpHouseId, $waterLevel)
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
 