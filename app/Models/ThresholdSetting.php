<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThresholdSetting extends Model
{
    use HasFactory;

    protected $fillable = [
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
     * Get active thresholds ordered by water level
     */
    public static function getActiveThresholds()
    {
        return self::where('is_active', true)
            ->orderBy('water_level', 'asc')
            ->get();
    }

    /**
     * Get threshold by name
     */
    public static function getByName($name)
    {
        return self::where('name', $name)->where('is_active', true)->first();
    }

    /**
     * Check if water level exceeds this threshold
     */
    public function isExceeded($waterLevel)
    {
        return $waterLevel >= $this->water_level;
    }

    /**
     * Get the highest exceeded threshold for given water level
     */
    public static function getExceededThreshold($waterLevel)
    {
        return self::where('is_active', true)
            ->where('water_level', '<=', $waterLevel)
            ->orderBy('water_level', 'desc')
            ->first();
    }
}
