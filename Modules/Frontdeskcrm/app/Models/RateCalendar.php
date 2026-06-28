<?php

namespace Modules\Frontdeskcrm\Models;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateCalendar extends Model
{
    use HasFactory;

    protected $table = 'rate_calendar';

    protected $fillable = [
        'rate_code_id',
        'room_type_id',
        'date',
        'price',
        'min_stay',
        'cta',
        'ctd',
        'stop_sell',
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
        'min_stay' => 'integer',
        'cta' => 'boolean',
        'ctd' => 'boolean',
        'stop_sell' => 'boolean',
    ];

    public function rateCode(): BelongsTo
    {
        return $this->belongsTo(RateCode::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
