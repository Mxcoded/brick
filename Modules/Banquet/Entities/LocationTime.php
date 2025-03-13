<?php

namespace Modules\Banquet\Entities;

use Illuminate\Database\Eloquent\Model;

class LocationTime extends Model
{
    protected $guarded = [];

    /**
     * Get the banquet that owns the location/time entry
     */
    public function banquet()
    {
        return $this->belongsTo(Banquet::class);
    }

    /**
     * Cast date/time fields properly
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
}
