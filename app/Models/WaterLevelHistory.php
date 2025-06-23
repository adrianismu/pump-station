<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaterLevelHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'pump_house_id',
        'water_level',
        'recorded_at',
    ];

    protected $casts = [
        'water_level' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function pumpHouse()
    {
        return $this->belongsTo(PumpHouse::class);
    }
}
