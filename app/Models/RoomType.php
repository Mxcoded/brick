<?php

namespace App\Models;

use App\Models\Traits\HasProperty;
use App\Services\RoomAvailabilityService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Models\Amenity;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomTypeImage;

class RoomType extends Model
{
    use HasFactory, HasProperty, SoftDeletes;

    protected $fillable = [
        'property_id',
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

    public function units()
    {
        return $this->hasMany(RoomUnit::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'amenity_room_type');
    }

    public function images()
    {
        return $this->hasMany(RoomTypeImage::class);
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

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getTotalUnitsAttribute()
    {
        return $this->units()->count();
    }

    public function getAvailableUnitsCountAttribute()
    {
        return $this->units()
            ->whereIn('status', ['available', 'occupied'])
            ->count();
    }

    // ==========================================
    // AVAILABILITY METHODS
    // ==========================================

    public function getAvailableUnitsForDates($checkIn, $checkOut, $ignoreBookingId = null)
    {
        $service = app(RoomAvailabilityService::class);

        return $service->getAvailableUnits($this->id, $checkIn, $checkOut, $ignoreBookingId);
    }

    public function hasAvailabilityForDates($checkIn, $checkOut)
    {
        $service = app(RoomAvailabilityService::class);
        $result = $service->checkRoomTypeAvailability($this->id, $checkIn, $checkOut);

        return $result['available'];
    }

    public function getAvailabilityCountForDates($checkIn, $checkOut)
    {
        return $this->getAvailableUnitsForDates($checkIn, $checkOut)->count();
    }

    public function getFirstAvailableUnitForDates($checkIn, $checkOut)
    {
        return $this->getAvailableUnitsForDates($checkIn, $checkOut)->first();
    }

    public function getAvailabilityInfo($checkIn, $checkOut)
    {
        $service = app(RoomAvailabilityService::class);

        return $service->checkRoomTypeAvailability($this->id, $checkIn, $checkOut);
    }
}
