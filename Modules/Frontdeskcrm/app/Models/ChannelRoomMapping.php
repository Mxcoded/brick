<?php

namespace Modules\Frontdeskcrm\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Website\Models\RoomUnit;

class ChannelRoomMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'room_unit_id',
        'external_room_id',
        'external_room_name',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function roomUnit(): BelongsTo
    {
        return $this->belongsTo(RoomUnit::class);
    }
}
