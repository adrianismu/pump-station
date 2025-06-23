<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationContent extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'description',
        'type',
        'image',
        'cloudinary_id',
        'content',
        'video_url',
        'infographic_url',
        'infographic_cloudinary_id',
        'date',
        'published',
        'updated_at',
    ];
    
    protected $casts = [
        'date' => 'datetime',
        'published' => 'boolean',
    ];
}