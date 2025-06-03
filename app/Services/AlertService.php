<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\PumpHouse;

class AlertService
{
    /**
     * Create water level alert
     */
    public function createWaterLevelAlert(PumpHouse $pumpHouse, float $waterLevel): Alert
    {
        $severity = $this->determineSeverity($pumpHouse, $waterLevel);
        
        return Alert::create([
            'pump_house_id' => $pumpHouse->id,
            'title' => "Water Level {$severity} Alert",
            'description' => "Water level at {$pumpHouse->name} has reached {$waterLevel}m",
            'severity' => $severity,
            'water_level' => $waterLevel,
            'recipients' => json_encode(['admin@pumpstation.com']),
            'is_active' => true,
        ]);
    }

    /**
     * Determine alert severity based on water level
     */
    private function determineSeverity(PumpHouse $pumpHouse, float $waterLevel): string
    {
        if ($pumpHouse->water_level_critical && $waterLevel >= $pumpHouse->water_level_critical) {
            return 'critical';
        }

        if ($pumpHouse->water_level_warning && $waterLevel >= $pumpHouse->water_level_warning) {
            return 'warning';
        }

        return 'info';
    }
} 