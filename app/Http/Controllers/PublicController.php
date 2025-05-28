<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\EducationContent;
use App\Models\PumpHouse;
use App\Models\Report;
use App\Models\WaterLevelHistory;
use App\Models\ThresholdSetting;
use App\Models\PumpHouseThresholdSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Carbon\Carbon;

class PublicController extends Controller
{
    /**
     * Landing page untuk masyarakat
     */
    public function index()
    {
        // Get pump houses dengan data ketinggian air terbaru
        $pumpHouses = PumpHouse::with(['waterLevelHistory' => function($query) {
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
                'location' => $pumpHouse->address, // Untuk display di weather card
                'address' => $pumpHouse->address,
                'latitude' => $pumpHouse->lat, // Untuk weather API
                'longitude' => $pumpHouse->lng, // Untuk weather API
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
        
        // Get recent alerts untuk notifikasi publik
        $recentAlerts = Alert::with('pump_house')
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
        
        // Get education content
        $educationContent = EducationContent::select('id', 'title', 'content', 'type', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();
        
        // Statistics untuk dashboard publik
        $stats = [
            'total_pump_houses' => PumpHouse::count(),
            'active_pump_houses' => PumpHouse::where('status', 'Aktif')->count(),
            'total_pumps' => PumpHouse::sum('pump_count'),
            'recent_reports' => Report::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
        ];
        
        return Inertia::render('Public/Landing', [
            'pumpHouses' => $pumpHouses,
            'recentAlerts' => $recentAlerts,
            'educationContent' => $educationContent,
            'stats' => $stats,
        ]);
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
        
        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reports', 'public');
                $imagePaths[] = asset('storage/' . $path);
            }
        }
        
        $report = new Report();
        $report->pump_house_id = $request->pump_house_id;
        $report->reporter_name = $request->reporter_name;
        $report->reporter_email = $request->reporter_email;
        $report->reporter_phone = $request->reporter_phone;
        $report->title = $request->title;
        $report->description = $request->description;
        $report->location = $request->location_detail ?? '';
        $report->images = $imagePaths;
        $report->status = 'Belum Ditanggapi';
        $report->save();
        
        return redirect()->route('public.report-success')->with('success', 'Laporan berhasil dikirim!');
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
    public function education()
    {
        $educationContent = EducationContent::orderBy('created_at', 'desc')->paginate(12);
        
        return Inertia::render('Public/Education', [
            'educationContent' => $educationContent,
        ]);
    }
    
    /**
     * Detail konten edukasi
     */
    public function educationDetail($id)
    {
        $content = EducationContent::findOrFail($id);
        
        // Get related content
        $relatedContent = EducationContent::where('id', '!=', $id)
            ->where('type', $content->type)
            ->take(3)
            ->get();
        
        return Inertia::render('Public/EducationDetail', [
            'content' => $content,
            'relatedContent' => $relatedContent,
        ]);
    }
    
    /**
     * Halaman peta publik
     */
    public function map()
    {
        // Get all pump houses dengan status terkini
        $pumpHouses = PumpHouse::with(['waterLevelHistory' => function($query) {
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
        
        return Inertia::render('Public/Map', [
            'pumpHouses' => $pumpHouses,
        ]);
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
 