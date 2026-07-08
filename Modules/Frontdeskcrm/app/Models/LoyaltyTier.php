<?php

namespace Modules\Frontdeskcrm\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyTier extends Model
{
    protected $fillable = [
        'name', 'slug', 'min_points', 'multiplier',
        'points_per_currency', 'color', 'benefits', 'is_active',
    ];

    protected $casts = [
        'min_points' => 'integer',
        'multiplier' => 'float',
        'points_per_currency' => 'integer',
        'is_active' => 'boolean',
    ];
}
