<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\EducationContent;
use App\Models\PumpHouse;
use App\Models\Report;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class LandingController extends Controller
{
    
    public function index()
    {
        // Get active pump houses for the map
        $pumpHouses = PumpHouse::where('status', 'Aktif')
            ->select('id', 'name', 'location', 'latitude', 'longitude', 'status')
            ->get();
        
        // Get recent alerts
        $recentAlerts = Alert::with('pump_house')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
        
        // Get educational content
        $educationContent = EducationContent::orderBy('created_at', 'desc')
            ->take(3)
            ->get();
        
        // Calculate statistics
        $stats = [
            'total_pump_houses' => PumpHouse::count(),
            'active_pump_houses' => PumpHouse::where('status', 'Aktif')->count(),
            'total_pumps' => PumpHouse::where('status', 'Aktif')->sum('pump_count') ?: 0,
            'recent_reports' => Report::where('created_at', '>=', now()->subWeek())->count(),
        ];
        
        return Inertia::render('Public/Landing', [
            'pumpHouses' => $pumpHouses,
            'recentAlerts' => $recentAlerts,
            'educationContent' => $educationContent,
            'stats' => $stats,
        ]);
    }
    
    /**
     * Update weather data for a pump house
     *
     * @param PumpHouse $pumpHouse
     * @return void
     */
    protected function updatePumpHouseWeather(PumpHouse $pumpHouse): void
    {
        try {
            $weatherData = $this->weatherService->getWeatherData($pumpHouse->lat, $pumpHouse->lng);
            
            if ($weatherData) {
                $parsedData = $this->weatherService->parseWeatherData($weatherData);
                
                $pumpHouse->weather_data = $parsedData;
                $pumpHouse->weather_updated_at = now();
                $pumpHouse->save();
            }
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error("Failed to update weather data for pump house {$pumpHouse->id}: " . $e->getMessage());
        }
    }
    
    public function submitReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string',
            'reporter_name' => 'required|string|max:255',
            'reporter_phone' => 'required|string|max:20',
            'reporter_email' => 'required|email|max:255',
            'pump_house_id' => 'nullable|exists:pump_houses,id',
            'images' => 'nullable|array',
            'images.*' => 'string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $report = new Report();
        $report->title = $request->title;
        $report->description = $request->description;
        $report->location = $request->location;
        $report->reporter_name = $request->reporter_name;
        $report->reporter_phone = $request->reporter_phone;
        $report->reporter_email = $request->reporter_email;
        $report->pump_house_id = $request->pump_house_id;
        $report->status = 'Belum Ditanggapi';
        
        if ($request->has('images')) {
            $report->images = json_encode($request->images);
        }
        
        $report->save();
        
        return redirect()->route('landing.success');
    }
    
    public function reportSuccess()
    {
        return Inertia::render('Public/ReportSuccess');
    }
}
