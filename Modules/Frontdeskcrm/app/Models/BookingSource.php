<?php

namespace Modules\Frontdeskcrm\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingSource extends Model
{
    use HasFactory, HasProperty;

    protected $fillable = ['name', 'description', 'is_active', 'type', 'commission_rate'];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
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
        // Fallback if stay_status not migrated
        $query = $this->registrations();
        if (Schema::hasColumn('registrations', 'stay_status')) {
            $query->where('stay_status', 'checked_out');
        }

        return $query->sum(DB::raw('room_rate * no_of_nights')); // Or total_amount if added
    }
}
