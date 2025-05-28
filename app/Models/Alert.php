<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'pump_house_id',
        'severity',
        'description',
        'water_level',
        'pump_status',
        'rainfall',
        'is_active',
        'recipients',
        'status',
    ];

    protected $casts = [
        'recipients' => 'array',
    ];

    public function pump_house(): BelongsTo
    {
        return $this->belongsTo(PumpHouse::class);
    }
    
    public function actions(): HasMany
    {
        return $this->hasMany(AlertAction::class);
    }
}