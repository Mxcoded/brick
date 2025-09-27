<?php

namespace Modules\Frontdeskcrm\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class GuestType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'color',
        'discount_rate',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'discount_rate' => 'decimal:2',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTotalRevenueAttribute()
    {
        // Fallback calc if total_amount not present
        $query = $this->registrations();
        if (Schema::hasColumn('registrations', 'stay_status')) {
            $query->where('stay_status', 'checked_out');
        }
        return $query->sum(DB::raw('room_rate * no_of_nights'));
    }
}
