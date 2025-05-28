<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PumpHouseThresholdSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'pump_house_id',
        'name',
        'label',
        'water_level',
        'color',
        'severity',
        'is_active',
        'description',
    ];

    protected $casts = [
        'water_level' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke PumpHouse
     */
    public function pumpHouse()
    {
        return $this->belongsTo(PumpHouse::class);
    }

    /**
     * Get active thresholds for a pump house ordered by water level
     */
    public static function getActiveThresholdsForPumpHouse($pumpHouseId)
    {
        return self::where('pump_house_id', $pumpHouseId)
            ->where('is_active', true)
            ->orderBy('water_level', 'asc')
            ->get();
    }

    /**
     * Get threshold by name for a pump house
     */
    public static function getByNameForPumpHouse($pumpHouseId, $name)
    {
        return self::where('pump_house_id', $pumpHouseId)
            ->where('name', $name)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Check if water level exceeds this threshold
     */
    public function isExceeded($waterLevel)
    {
        return $waterLevel >= $this->water_level;
    }

    /**
     * Get the highest exceeded threshold for given water level and pump house
     */
    public static function getExceededThresholdForPumpHouse($pumpHouseId, $waterLevel)
    {
        return self::where('pump_house_id', $pumpHouseId)
            ->where('is_active', true)
            ->where('water_level', '<=', $waterLevel)
            ->orderBy('water_level', 'desc')
            ->first();
    }

    /**
     * Copy default thresholds to a pump house
     */
    public static function copyDefaultThresholds($pumpHouseId)
    {
        $defaultThresholds = ThresholdSetting::getActiveThresholds();
        
        foreach ($defaultThresholds as $threshold) {
            self::updateOrCreate(
                [
                    'pump_house_id' => $pumpHouseId,
                    'name' => $threshold->name,
                ],
                [
                    'label' => $threshold->label,
                    'water_level' => $threshold->water_level,
                    'color' => $threshold->color,
                    'severity' => $threshold->severity,
                    'is_active' => $threshold->is_active,
                    'description' => $threshold->description,
                ]
            );
        }
    }
}
