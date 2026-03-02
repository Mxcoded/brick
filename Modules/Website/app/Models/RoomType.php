<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Frontdeskcrm\Models\Registration;

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
     * Checks both website bookings and frontdesk registrations.
     */
    public function getAvailableUnitsForDates($checkIn, $checkOut)
    {
        $checkIn = \Carbon\Carbon::parse($checkIn);
        $checkOut = \Carbon\Carbon::parse($checkOut);

        return $this->units()
            ->where('status', '!=', 'maintenance')
            ->where('status', '!=', 'blocked')
            ->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->where('status', '!=', 'cancelled')
                    ->where(function ($sub) use ($checkIn, $checkOut) {
                        $sub->where('check_in_date', '<', $checkOut)
                            ->where('check_out_date', '>', $checkIn);
                    });
            })
            ->when(class_exists(Registration::class), function ($q) use ($checkIn, $checkOut) {
                $q->whereDoesntHave('registrations', function ($sub) use ($checkIn, $checkOut) {
                    $sub->whereIn('stay_status', ['checked_in', 'draft_by_guest', 'reserved'])
                        ->where(function ($inner) use ($checkIn, $checkOut) {
                            $inner->where('check_in', '<', $checkOut)
                                ->where('check_out', '>', $checkIn);
                        });
                });
            })
            ->get();
    }

    /**
     * Check if this room type has any available units for given dates.
     */
    public function hasAvailabilityForDates($checkIn, $checkOut)
    {
        return $this->getAvailableUnitsForDates($checkIn, $checkOut)->count() > 0;
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
}
