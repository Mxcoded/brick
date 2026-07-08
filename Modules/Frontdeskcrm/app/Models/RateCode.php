<?php

namespace Modules\Frontdeskcrm\Models;

use App\Models\RoomType;
use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RateCode extends Model
{
    use HasFactory, HasProperty;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(RateCodePrice::class);
    }

    public function calendar(): HasMany
    {
        return $this->hasMany(RateCalendar::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getPriceForRoomType(RoomType $roomType, ?string $date = null): ?string
    {
        if ($date) {
            $override = $this->calendar()
                ->where('room_type_id', $roomType->id)
                ->where('date', $date)
                ->first();

            if ($override && $override->price !== null) {
                return $override->price;
            }
        }

        $base = $this->prices()
            ->where('room_type_id', $roomType->id)
            ->first();

        return $base?->price;
    }
}
