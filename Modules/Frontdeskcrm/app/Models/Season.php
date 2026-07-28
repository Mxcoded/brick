<?php

namespace Modules\Frontdeskcrm\Models;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $fillable = [
        'code', 'name', 'description',
        'valid_from', 'valid_to', 'rate_multiplier', 'is_active',
    ];

    protected $casts = [
        'rate_multiplier' => 'decimal:4',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
