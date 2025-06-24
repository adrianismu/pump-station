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
        'type',
        'title',
        'pump_house_id',
        'severity',
        'description',
        'internal_message',
        'public_message',
        'water_level',
        'pump_status',
        'rainfall',
        'is_active',
        'recipients',
        'status',
    ];

    protected $casts = [
        'recipients' => 'array',
        'is_active' => 'boolean',
    ];

    public function pump_house(): BelongsTo
    {
        return $this->belongsTo(PumpHouse::class);
    }
    
    public function actions(): HasMany
    {
        return $this->hasMany(AlertAction::class);
    }
    
    /**
     * Get active public alerts (critical and high level with public message)
     */
    public static function getActivePublicAlerts()
    {
        return static::whereIn('severity', ['critical', 'high'])
            ->whereNotNull('public_message')
            ->where('is_active', true)
            ->where('created_at', '>', now()->subHours(3))
            ->latest()
            ->get();
    }
}