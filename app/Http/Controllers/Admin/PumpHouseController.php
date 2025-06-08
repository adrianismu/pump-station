<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PumpHouse;
use App\Models\WaterLevelHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PumpHouseController extends Controller
{
    public function show(PumpHouse $pumpHouse)
    {
        // Load any additional data needed for the detail view
        $pumpHouse->load('pumps', 'recentAlerts', 'maintenanceHistory');
        
        return Inertia::render('Admin/PumpHouseDetail', [
            'pumpHouse' => $pumpHouse,
        ]);
    }
    
    public function getWaterLevels(Request $request, PumpHouse $pumpHouse)
    {
        $range = $request->input('range', '24h');
        
        // Determine the date range based on the requested range
        $startDate = now();
        
        switch ($range) {
            case '7d':
                $startDate = $startDate->subDays(7);
                break;
            case '30d':
                $startDate = $startDate->subDays(30);
                break;
            default: // 24h
                $startDate = $startDate->subHours(24);
                break;
        }
        
        // Query the water level history
        $waterLevels = WaterLevelHistory::where('pump_house_id', $pumpHouse->id)
            ->where('recorded_at', '>=', $startDate)
            ->orderBy('recorded_at', 'asc')
            ->get(['water_level', 'recorded_at']);
        
        return response()->json([
            'success' => true,
            'data' => $waterLevels,
            'current_level' => $pumpHouse->getCurrentWaterLevel(),
            'warning_threshold' => $pumpHouse->water_level_warning,
            'critical_threshold' => $pumpHouse->water_level_critical,
            'water_level_status' => $pumpHouse->water_level_status,
            'pump_active' => false // Dummy value since we're not using real hardware
        ]);
    }
    
    public function updateWaterLevel(Request $request, PumpHouse $pumpHouse)
    {
        $validated = $request->validate([
            'water_level' => 'required|numeric|min:0|max:500',
        ]);
        
        // Create new water level history record
        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => $validated['water_level'],
            'recorded_at' => now(),
        ]);
        
        // Update pump house last_updated timestamp
        $pumpHouse->last_updated = now();
        $pumpHouse->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Water level updated successfully',
            'water_level' => $validated['water_level'],
            'water_level_status' => $pumpHouse->water_level_status
        ]);
    }
    
    public function updateThresholds(Request $request, PumpHouse $pumpHouse)
    {
        $validated = $request->validate([
            'warning_threshold' => 'nullable|numeric|min:0|max:500',
            'critical_threshold' => 'nullable|numeric|min:0|max:500',
        ]);
        
        // Ensure critical threshold is higher than warning threshold if both are set
        if ($validated['warning_threshold'] && $validated['critical_threshold'] && 
            $validated['warning_threshold'] >= $validated['critical_threshold']) {
            return response()->json([
                'success' => false,
                'message' => 'Ambang batas kritis harus lebih tinggi dari ambang batas peringatan'
            ], 422);
        }
        
        // Update the thresholds
        $pumpHouse->water_level_warning = $validated['warning_threshold'];
        $pumpHouse->water_level_critical = $validated['critical_threshold'];
        $pumpHouse->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Ambang batas berhasil diperbarui',
            'warning_threshold' => $pumpHouse->water_level_warning,
            'critical_threshold' => $pumpHouse->water_level_critical
        ]);
    }

    // Dummy method for pump control (since we're not using real hardware)
    public function controlPump(Request $request, PumpHouse $pumpHouse)
    {
        $validated = $request->validate([
            'pump_active' => 'required|boolean',
        ]);

        // In a real implementation, this would control the actual pump
        // For now, we just return a success response

        return response()->json([
            'success' => true,
            'message' => 'Perintah berhasil dikirim ke pompa',
            'pump_active' => $validated['pump_active']
        ]);
    }

    /**
     * Update pump status (active pumps count)
     */
    public function updatePumpStatus(Request $request, PumpHouse $pumpHouse)
    {
        $validated = $request->validate([
            'active_pumps' => 'required|integer|min:0',
        ]);

        // Validate that active_pumps doesn't exceed pump_count
        if ($validated['active_pumps'] > $pumpHouse->pump_count) {
            return back()->withErrors([
                'active_pumps' => 'Jumlah pompa aktif tidak boleh lebih besar dari total pompa'
            ])->withInput();
        }

        // Update the pump status
        $pumpHouse->active_pumps = $validated['active_pumps'];
        $pumpHouse->last_updated = now();
        $pumpHouse->save();

        return back()->with('success', 'Status pompa berhasil diperbarui');
    }
}