<?php

namespace Modules\Frontdeskcrm\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class GuestType extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'color',
        'discount_rate',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'discount_rate' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(GuestTypeRate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the negotiated rate for a given room type on a specific date.
     * Falls back to discount_rate-based calculation when no rate table entry exists.
     *
     * @return array{rate: float, source: string, has_negotiated_rate: bool}
     */
    public function getNegotiatedRate(int $roomTypeId, $date = null): array
    {
        $date = $date ?? now()->toDateString();

        $rateEntry = $this->rates()
            ->active()
            ->where('room_type_id', $roomTypeId)
            ->applicableToDate($date)
            ->orderBy('valid_from', 'desc')
            ->first();

        if ($rateEntry) {
            return [
                'rate' => (float) $rateEntry->rate,
                'source' => 'negotiated',
                'has_negotiated_rate' => true,
            ];
        }

        return [
            'rate' => (float) $this->discount_rate,
            'source' => 'discount',
            'has_negotiated_rate' => false,
        ];
    }

    /**
     * Check if this guest type contract is currently valid.
     */
    public function isValidNow(): bool
    {
        $today = now()->toDateString();

        if ($this->valid_from && $this->valid_from->gt($today)) {
            return false;
        }

        if ($this->valid_to && $this->valid_to->lt($today)) {
            return false;
        }

        return true;
    }

    /**
     * Get total revenue from actual payments (not room_rate * nights).
     */
    public function getTotalRevenueAttribute()
    {
        return $this->registrations()
            ->when(Schema::hasColumn('registrations', 'stay_status'), fn ($q) => $q->where('stay_status', 'checked_out'))
            ->join('registration_payments', 'registration_payments.registration_id', '=', 'registrations.id')
            ->sum('registration_payments.amount');
    }
}
