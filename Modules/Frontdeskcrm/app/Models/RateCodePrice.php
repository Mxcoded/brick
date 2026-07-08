<?php

namespace Modules\Frontdeskcrm\Models;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateCodePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate_code_id',
        'room_type_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
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
