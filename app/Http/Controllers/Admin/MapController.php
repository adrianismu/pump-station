<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PumpHouse;
use App\Models\ThresholdSetting;
use App\Models\PumpHouseThresholdSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MapController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Filter pump houses berdasarkan akses user dengan relasi water level
        $pumpHousesQuery = PumpHouse::with(['waterLevelHistory' => function($query) {
            $query->latest()->limit(1);
        }]);
        
        if (!$user->isAdmin()) {
            $accessibleIds = $user->getAccessiblePumpHouseIds();
            $pumpHousesQuery->whereIn('id', $accessibleIds);
        }
        
        $pumpHouses = $pumpHousesQuery->get();
        
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
        
        return Inertia::render('Admin/Map', [
            'pumpHouses' => $pumpHousesWithStatus,
            'userRole' => $user->role,
        ]);
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
