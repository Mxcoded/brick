<?php

namespace Modules\Frontdeskcrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateCalendar extends Model
{
    protected $table = 'rate_calendar';

    protected $fillable = [
        'rate_code_id', 'date', 'rate', 'is_available', 'available_rooms',
    ];

    protected $casts = [
        'date' => 'date',
        'rate' => 'decimal:2',
        'is_available' => 'boolean',
        'available_rooms' => 'integer',
    ];

    public function rateCode(): BelongsTo
    {
        return $this->belongsTo(RateCode::class);
    }
}
