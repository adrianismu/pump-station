<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PumpHouse;
use App\Models\PumpHouseThresholdSetting;
use App\Models\ThresholdSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PumpHouseThresholdController extends Controller
{
    public function index()
    {
        $pumpHouses = PumpHouse::with(['threshold_settings' => function($query) {
            $query->orderBy('water_level', 'asc');
        }])->get();

        return Inertia::render('Admin/PumpHouseThreshold/Index', [
            'pumpHouses' => $pumpHouses,
        ]);
    }

    public function show($pumpHouseId)
    {
        $pumpHouse = PumpHouse::with(['threshold_settings' => function($query) {
            $query->orderBy('water_level', 'asc');
        }, 'waterLevelHistory' => function($query) {
            $query->orderBy('recorded_at', 'desc')->limit(10);
        }])->findOrFail($pumpHouseId);

        // Get current water level (latest record)
        $currentWaterLevel = $pumpHouse->waterLevelHistory->first()?->water_level ?? 0;

        // Get recent history (last 90 days for chart, limit 100)
        $recentHistory = $pumpHouse->waterLevelHistory()
            ->where('recorded_at', '>=', now()->subDays(90))
            ->orderBy('recorded_at', 'desc')
            ->limit(100)
            ->get();

        // Get total history count
        $totalHistoryCount = $pumpHouse->waterLevelHistory()->count();

        // Get global thresholds as fallback
        $globalThresholds = \App\Models\ThresholdSetting::where('is_active', true)
            ->orderBy('water_level', 'asc')
            ->get();

        return Inertia::render('Admin/PumpHouseThreshold/Show', [
            'pumpHouse' => $pumpHouse,
            'thresholds' => $pumpHouse->threshold_settings,
            'currentWaterLevel' => $currentWaterLevel,
            'recentHistory' => $recentHistory,
            'totalHistoryCount' => $totalHistoryCount,
            'globalThresholds' => $globalThresholds,
        ]);
    }

    public function edit($pumpHouseId)
    {
        $pumpHouse = PumpHouse::with(['threshold_settings' => function($query) {
            $query->orderBy('water_level', 'asc');
        }])->findOrFail($pumpHouseId);

        // If no thresholds exist for this pump house, copy from default
        if ($pumpHouse->threshold_settings->isEmpty()) {
            PumpHouseThresholdSetting::copyDefaultThresholds($pumpHouseId);
            $pumpHouse->load('threshold_settings');
        }

        return Inertia::render('Admin/PumpHouseThreshold/Edit', [
            'pumpHouse' => $pumpHouse,
        ]);
    }

    public function update(Request $request, $pumpHouseId)
    {
        $pumpHouse = PumpHouse::findOrFail($pumpHouseId);
        
        $request->validate([
            'thresholds' => 'required|array',
            'thresholds.*.id' => 'nullable|exists:pump_house_threshold_settings,id',
            'thresholds.*.name' => 'required|string',
            'thresholds.*.label' => 'required|string',
            'thresholds.*.water_level' => 'required|numeric|min:0|max:10',
            'thresholds.*.color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'thresholds.*.severity' => 'required|in:low,medium,high,critical',
            'thresholds.*.is_active' => 'boolean',
            'thresholds.*.description' => 'nullable|string',
        ]);

        foreach ($request->thresholds as $thresholdData) {
            if (isset($thresholdData['id'])) {
                // Update existing threshold
                $threshold = PumpHouseThresholdSetting::findOrFail($thresholdData['id']);
                $threshold->update([
                    'label' => $thresholdData['label'],
                    'water_level' => $thresholdData['water_level'],
                    'color' => $thresholdData['color'],
                    'severity' => $thresholdData['severity'],
                    'is_active' => $thresholdData['is_active'] ?? true,
                    'description' => $thresholdData['description'],
                ]);
            } else {
                // Create new threshold
                PumpHouseThresholdSetting::create([
                    'pump_house_id' => $pumpHouseId,
                    'name' => $thresholdData['name'],
                    'label' => $thresholdData['label'],
                    'water_level' => $thresholdData['water_level'],
                    'color' => $thresholdData['color'],
                    'severity' => $thresholdData['severity'],
                    'is_active' => $thresholdData['is_active'] ?? true,
                    'description' => $thresholdData['description'],
                ]);
            }
        }

        return redirect()->route('admin.pump-house-thresholds.show', $pumpHouseId)
            ->with('success', 'Pengaturan threshold berhasil diperbarui');
    }

    public function copyFromDefault($pumpHouseId)
    {
        $pumpHouse = PumpHouse::findOrFail($pumpHouseId);
        
        PumpHouseThresholdSetting::copyDefaultThresholds($pumpHouseId);

        return redirect()->route('admin.pump-house-thresholds.edit', $pumpHouseId)
            ->with('success', 'Threshold default berhasil disalin');
    }

    public function resetToDefault($pumpHouseId)
    {
        $pumpHouse = PumpHouse::findOrFail($pumpHouseId);
        
        // Delete existing thresholds
        PumpHouseThresholdSetting::where('pump_house_id', $pumpHouseId)->delete();
        
        // Copy default thresholds
        PumpHouseThresholdSetting::copyDefaultThresholds($pumpHouseId);

        return redirect()->route('admin.pump-house-thresholds.edit', $pumpHouseId)
            ->with('success', 'Threshold berhasil direset ke pengaturan default');
    }

    public function destroy($pumpHouseId, $thresholdId)
    {
        $threshold = PumpHouseThresholdSetting::where('pump_house_id', $pumpHouseId)
            ->findOrFail($thresholdId);
        
        $threshold->delete();

        return redirect()->route('admin.pump-house-thresholds.edit', $pumpHouseId)
            ->with('success', 'Threshold berhasil dihapus');
    }
}
