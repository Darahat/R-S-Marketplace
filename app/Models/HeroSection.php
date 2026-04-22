<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'headline',
        'highlight',
        'subheadline',
        'show_overlay',
        'overlay_color',
        'headline_color',
        'highlight_color',
        'subheadline_color',
        'primary_text',
        'primary_url',
        'secondary_text',
        'secondary_url',
        'banner_image',
    ];

    protected $casts = [
        'show_overlay' => 'boolean',
    ];
}
