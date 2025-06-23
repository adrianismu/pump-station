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
        'active_pumps',
        'water_level_warning',
        'water_level_critical',
        'image',
        'cloudinary_id',
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

    // Get pump status percentage
    public function getPumpStatusPercentage()
    {
        $totalPumps = $this->pump_count ?: 1;
        $activePumps = $this->active_pumps ?: 0;
        
        return round(($activePumps / $totalPumps) * 100, 1);
    }

    // Get pump status text
    public function getPumpStatusText()
    {
        $activePumps = $this->active_pumps ?: 0;
        $totalPumps = $this->pump_count ?: 1;
        
        if ($activePumps === 0) {
            return 'Semua pompa tidak aktif';
        } elseif ($activePumps === $totalPumps) {
            return 'Semua pompa aktif';
        } else {
            return "{$activePumps} dari {$totalPumps} pompa aktif";
        }
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

    /**
     * Relasi many-to-many dengan User (petugas yang di-assign)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_pump_house')
                    ->withPivot(['access_level', 'is_active', 'assigned_at', 'expires_at', 'notes'])
                    ->withTimestamps()
                    ->wherePivot('is_active', true)
                    ->where(function($query) {
                        $query->whereNull('user_pump_house.expires_at')
                              ->orWhere('user_pump_house.expires_at', '>', now());
                    });
    }

    /**
     * Relasi untuk semua users (termasuk yang tidak aktif)
     */
    public function allUsers()
    {
        return $this->belongsToMany(User::class, 'user_pump_house')
                    ->withPivot(['access_level', 'is_active', 'assigned_at', 'expires_at', 'notes'])
                    ->withTimestamps();
    }
}
