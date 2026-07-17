<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Services\RoomAvailabilityService;

class RoomType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'capacity',
        'size',
        'bed_type',
        'description',
        'image_url',
        'video_url',
        'is_featured',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get all units (physical rooms) of this type.
     */
    public function units()
    {
        return $this->hasMany(RoomUnit::class);
    }

    /**
     * Get amenities for this room type.
     */
    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'amenity_room_type');
    }

    /**
     * Get gallery images for this room type.
     */
    public function images()
    {
        return $this->hasMany(RoomTypeImage::class);
    }

    /**
     * Get all bookings for this room type.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get all registrations for this room type.
     */
    public function registrations()
    {
        if (class_exists(Registration::class)) {
            return $this->hasMany(Registration::class);
        }

        return $this->hasMany(Booking::class)->whereRaw('1 = 0');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Only featured room types.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Only active room types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get total number of units.
     */
    public function getTotalUnitsAttribute()
    {
        return $this->units()->count();
    }

    /**
     * Get number of available units (not in maintenance/blocked).
     */
    public function getAvailableUnitsCountAttribute()
    {
        return $this->units()
            ->whereIn('status', ['available', 'occupied']) // occupied can be available for future dates
            ->count();
    }

    // ==========================================
    // AVAILABILITY METHODS
    // ==========================================

    /**
     * Get units available for specific dates.
     * Uses the unified RoomAvailabilityService for comprehensive checking:
     * - Website Bookings
     * - Frontdesk Registrations
     * - Inventory Blocks (Stop Sell, Maintenance, Manual)
     * - Room Unit Status
     * - Stay Restrictions
     */
    public function getAvailableUnitsForDates($checkIn, $checkOut, $ignoreBookingId = null)
    {
        $service = app(RoomAvailabilityService::class);

        return $service->getAvailableUnits($this->id, $checkIn, $checkOut, $ignoreBookingId);
    }

    /**
     * Check if this room type has any available units for given dates.
     * Returns false if stop sell, CTA, or other restrictions apply.
     */
    public function hasAvailabilityForDates($checkIn, $checkOut)
    {
        $service = app(RoomAvailabilityService::class);
        $result = $service->checkRoomTypeAvailability($this->id, $checkIn, $checkOut);

        return $result['available'];
    }

    /**
     * Get count of available units for dates.
     */
    public function getAvailabilityCountForDates($checkIn, $checkOut)
    {
        return $this->getAvailableUnitsForDates($checkIn, $checkOut)->count();
    }

    /**
     * Get a single available unit for dates (for auto-assignment).
     */
    public function getFirstAvailableUnitForDates($checkIn, $checkOut)
    {
        return $this->getAvailableUnitsForDates($checkIn, $checkOut)->first();
    }

    /**
     * Get detailed availability info including restrictions.
     */
    public function getAvailabilityInfo($checkIn, $checkOut)
    {
        $service = app(RoomAvailabilityService::class);

        return $service->checkRoomTypeAvailability($this->id, $checkIn, $checkOut);
    }
}
