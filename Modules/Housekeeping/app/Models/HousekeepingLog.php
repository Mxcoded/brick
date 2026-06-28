<?php

namespace Modules\Housekeeping\Models;

use App\Models\RoomUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class HousekeepingLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'room_unit_id',
        'cleaned_by',
        'status_from',
        'status_to',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function roomUnit()
    {
        return $this->belongsTo(RoomUnit::class);
    }

    public function cleanedBy()
    {
        return $this->belongsTo(User::class, 'cleaned_by');
    }
}
