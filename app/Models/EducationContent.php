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
        'content',
        'video_url',
        'infographic_url',
        'tags',
        'date',
        'views',
        'published',
        'updated_at',
    ];
    
    protected $casts = [
        'tags' => 'array',
        'date' => 'datetime',
        'published' => 'boolean',
        'views' => 'integer',
    ];
}