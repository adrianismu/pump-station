<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PumpHouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'lat',
        'lng',
        'status',
        'capacity',
        'pump_count',
        'water_level_warning',
        'water_level_critical',
        'image',
        'built_year',
        'manager_name',
        'contact_phone',
        'contact_email',
        'staff_count',
        'last_updated',
    ];

    protected $casts = [
        'lat' => 'decimal:12',
        'lng' => 'decimal:12',
        'water_level_warning' => 'float',
        'water_level_critical' => 'float',
        'last_updated' => 'datetime',
    ];

    // Add a method to check if the water level exceeds thresholds
    public function getWaterLevelStatusAttribute()
    {
        $currentWaterLevel = $this->getCurrentWaterLevel();
        
        if (!$currentWaterLevel) {
            return 'normal';
        }
        
        if ($this->water_level_critical && $currentWaterLevel >= $this->water_level_critical) {
            return 'critical';
        }
        
        if ($this->water_level_warning && $currentWaterLevel >= $this->water_level_warning) {
            return 'warning';
        }
        
        return 'normal';
    }

    // Get current water level from latest history record
    public function getCurrentWaterLevel()
    {
        $latestRecord = $this->waterLevelHistory()->latest('recorded_at')->first();
        return $latestRecord ? (float) $latestRecord->water_level : 0;
    }

    // Relationships
    public function waterLevelHistory()
    {
        return $this->hasMany(WaterLevelHistory::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function recentAlerts()
    {
        return $this->hasMany(Alert::class)->where('is_active', true)->latest()->limit(5);
    }

    public function threshold_settings()
    {
        return $this->hasMany(PumpHouseThresholdSetting::class);
    }
}
