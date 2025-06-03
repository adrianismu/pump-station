<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'pump_house_id',
        'title',
        'description',
        'status',
        'reporter_name',
        'reporter_phone',
        'reporter_email',
        'location',
        'images',
        'cloudinary_ids',
    ];

    protected $casts = [
        'images' => 'array',
        'cloudinary_ids' => 'array',
    ];

    public function pump_house(): BelongsTo
    {
        return $this->belongsTo(PumpHouse::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ReportResponse::class);
    }
}