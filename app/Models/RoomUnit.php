<?php

namespace App\Models;

use App\Models\Traits\HasProperty;
use App\Services\RoomAvailabilityService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Housekeeping\Models\HousekeepingLog;
use Modules\Website\Models\Booking;

class RoomUnit extends Model
{
    use HasFactory, HasProperty, SoftDeletes;

    protected $fillable = [
        'property_id',
        'room_type_id',
        'room_number',
        'floor',
        'status',
        'cleaning_status',
        'last_cleaned_at',
        'last_cleaned_by',
        'notes',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function registrations()
    {
        if (class_exists(Registration::class)) {
            return $this->hasMany(Registration::class);
        }

        return $this->hasMany(Booking::class)->whereRaw('1 = 0');
    }

    public function currentOccupant()
    {
        if (class_exists(Registration::class)) {
            return $this->hasOne(Registration::class)
                ->whereIn('stay_status', ['checked_in', 'draft_by_guest'])
                ->latest('check_in');
        }

        return $this->hasOne(Booking::class)->whereRaw('1 = 0');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeAvailable($query)
    {
        return $query->whereIn('status', ['available', 'occupied']);
    }

    public function scopeOnFloor($query, $floor)
    {
        return $query->where('floor', $floor);
    }

    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getDisplayNameAttribute()
    {
        return $this->room_number.' - '.($this->roomType->name ?? 'Unknown Type');
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'available' => 'success',
            'occupied' => 'danger',
            'maintenance' => 'secondary',
            'blocked' => 'secondary',
            default => 'light',
        };
    }

    public function getCleaningStatusColorAttribute()
    {
        return match ($this->cleaning_status) {
            'dirty' => 'danger',
            'cleaning' => 'warning',
            'clean' => 'success',
            'inspected' => 'info',
            default => 'secondary',
        };
    }

    public function housekeepingLogs()
    {
        return $this->hasMany(HousekeepingLog::class, 'room_unit_id');
    }

    // ==========================================
    // AVAILABILITY METHODS
    // ==========================================

    public function isAvailableForDates($checkIn, $checkOut, $ignoreBookingId = null)
    {
        $service = app(RoomAvailabilityService::class);

        return $service->isUnitAvailable($this->id, $checkIn, $checkOut, $ignoreBookingId);
    }

    public function isCurrentlyOccupied()
    {
        $today = Carbon::today();

        return ! $this->isAvailableForDates($today, $today->copy()->addDay());
    }

    public function getCurrentGuestName()
    {
        $occupant = $this->currentOccupant;

        return $occupant ? $occupant->full_name : null;
    }

    public function getCurrentStatusInfo()
    {
        $service = app(RoomAvailabilityService::class);

        return $service->getUnitCurrentStatus($this->id);
    }
}
