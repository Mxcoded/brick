<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Frontdeskcrm\Models\Registration;
use Carbon\Carbon;

class RoomUnit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_type_id',
        'room_number',
        'floor',
        'status',
        'notes',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the room type this unit belongs to.
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get bookings assigned to this unit.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get registrations for this unit.
     */
    public function registrations()
    {
        if (class_exists(Registration::class)) {
            return $this->hasMany(Registration::class);
        }
        return $this->hasMany(Booking::class)->whereRaw('1 = 0');
    }

    /**
     * Get current occupant (if checked in).
     */
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

    /**
     * Scope: Only available units (not maintenance/blocked).
     */
    public function scopeAvailable($query)
    {
        return $query->whereIn('status', ['available', 'occupied']);
    }

    /**
     * Scope: Filter by floor.
     */
    public function scopeOnFloor($query, $floor)
    {
        return $query->where('floor', $floor);
    }

    /**
     * Scope: Filter by status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get display name (room number + type).
     */
    public function getDisplayNameAttribute()
    {
        return $this->room_number . ' - ' . ($this->roomType->name ?? 'Unknown Type');
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'available' => 'success',
            'occupied' => 'danger',
            'maintenance' => 'warning',
            'blocked' => 'secondary',
            default => 'light',
        };
    }

    // ==========================================
    // AVAILABILITY METHODS
    // ==========================================

    /**
     * Check if this unit is available for specific dates.
     * Uses unified RoomAvailabilityService for comprehensive checking including:
     * - Unit status (maintenance, blocked)
     * - Website bookings
     * - Frontdesk registrations
     * - Inventory blocks (stop sell, CTA, CTD)
     * - Min/max stay restrictions
     */
    public function isAvailableForDates($checkIn, $checkOut, $ignoreBookingId = null)
    {
        $service = app(\Modules\Website\Services\RoomAvailabilityService::class);
        return $service->isUnitAvailable($this->id, $checkIn, $checkOut, $ignoreBookingId);
    }

    /**
     * Check if unit is currently occupied.
     */
    public function isCurrentlyOccupied()
    {
        $today = Carbon::today();
        return !$this->isAvailableForDates($today, $today->copy()->addDay());
    }

    /**
     * Get the current guest name (if occupied).
     */
    public function getCurrentGuestName()
    {
        $occupant = $this->currentOccupant;
        return $occupant ? $occupant->full_name : null;
    }

    /**
     * Get current status info for this unit.
     */
    public function getCurrentStatusInfo()
    {
        $service = app(\Modules\Website\Services\RoomAvailabilityService::class);
        return $service->getUnitCurrentStatus($this->id);
    }
}
