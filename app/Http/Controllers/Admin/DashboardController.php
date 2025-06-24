<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\PumpHouse;
use App\Models\Report;
use App\Models\WaterLevelHistory;
use App\Models\ThresholdSetting;
use App\Models\PumpHouseThresholdSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Filter data berdasarkan akses user
        $pumpHousesQuery = PumpHouse::with(['waterLevelHistory' => function($query) {
            $query->latest()->limit(1);
        }]);
        $alertsQuery = Alert::with('pump_house');
        
        if (!$user->isAdmin()) {
            $accessibleIds = $user->getAccessiblePumpHouseIds();
            $pumpHousesQuery->whereIn('id', $accessibleIds);
            $alertsQuery->whereIn('pump_house_id', $accessibleIds);
        }
        
        $dashboardData = [
            'totalPumpHouses' => $pumpHousesQuery->count(),
            'activePumpHouses' => $pumpHousesQuery->where('status', 'Aktif')->count(),
            'totalPumps' => $pumpHousesQuery->sum('pump_count'),
            'activePumps' => $pumpHousesQuery->where('status', 'Aktif')->sum('pump_count'),
            'recentAlerts' => $alertsQuery
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
            'recentReports' => $this->getRecentReports($user),
        ];

        $pumpHouses = $pumpHousesQuery->orderBy('created_at', 'desc')->get();
        $recentpumpHouses = $pumpHousesQuery->orderBy('created_at', 'desc')->take(7)->get();

        // Get water level statistics
        $waterLevelStats = $this->getWaterLevelStats($user);

        // Add threshold status to pump houses
        $pumpHousesWithStatus = $pumpHouses->map(function ($pumpHouse) {
            $latestWaterLevel = $pumpHouse->waterLevelHistory->first();
            $status = $this->getWaterLevelStatus($pumpHouse->id, $latestWaterLevel?->water_level ?? 0);
            
            return array_merge($pumpHouse->toArray(), [
                'current_water_level' => $latestWaterLevel?->water_level ?? 0,
                'water_level_status' => $status,
                'last_recorded' => $latestWaterLevel?->recorded_at,
            ]);
        });

        return Inertia::render('Admin/Dashboard', [
            'dashboardData' => $dashboardData,
            'pumpHouses' => $pumpHousesWithStatus,
            'recentpumpHouses' => $recentpumpHouses,
            'waterLevelStats' => $waterLevelStats,
            'userRole' => $user->role,
        ]);
    }

    private function getRecentReports($user)
    {
        if ($user->isAdmin()) {
            // Admin dapat melihat semua laporan
            return Report::with('pump_house')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        } else {
            // Petugas hanya melihat laporan dari rumah pompa yang dapat mereka akses
            $accessibleIds = $user->getAccessiblePumpHouseIds();
            return Report::with('pump_house')
                ->whereIn('pump_house_id', $accessibleIds)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }
    }

    private function getWaterLevelStats($user)
    {
        $pumpHousesQuery = PumpHouse::with(['waterLevelHistory' => function($query) {
            $query->latest()->limit(1);
        }]);
        
        if (!$user->isAdmin()) {
            $accessibleIds = $user->getAccessiblePumpHouseIds();
            $pumpHousesQuery->whereIn('id', $accessibleIds);
        }
        
        $pumpHouses = $pumpHousesQuery->get();
        
        $stats = [
            'normal' => 0,
            'warning' => 0,
            'critical' => 0,
            'emergency' => 0,
            'no_data' => 0,
        ];
        
        foreach ($pumpHouses as $pumpHouse) {
            $latestWaterLevel = $pumpHouse->waterLevelHistory->first();
            $waterLevel = $latestWaterLevel?->water_level ?? null;
            
            if ($waterLevel === null) {
                $stats['no_data']++;
                continue;
            }
            
            $status = $this->getWaterLevelStatus($pumpHouse->id, $waterLevel);
            $levelKey = strtolower($status['level']);
            
            // Handle different possible threshold names
            if (isset($stats[$levelKey])) {
                $stats[$levelKey]++;
            } else {
                // Default to normal if unknown level
                $stats['normal']++;
            }
        }
        
        return $stats;
    }

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
