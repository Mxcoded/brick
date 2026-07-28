<?php

namespace Modules\Frontdeskcrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RateCode extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'default_rate', 'currency',
        'min_los', 'max_los', 'closed_to_arrival', 'closed_to_departure',
        'apply_weekdays', 'apply_weekends', 'is_active',
        'valid_from', 'valid_to', 'sort_order',
    ];

    protected $casts = [
        'default_rate' => 'decimal:2',
        'min_los' => 'integer',
        'max_los' => 'integer',
        'closed_to_arrival' => 'boolean',
        'closed_to_departure' => 'boolean',
        'apply_weekdays' => 'boolean',
        'apply_weekends' => 'boolean',
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'sort_order' => 'integer',
    ];

    public function calendar(): HasMany
    {
        return $this->hasMany(RateCalendar::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
