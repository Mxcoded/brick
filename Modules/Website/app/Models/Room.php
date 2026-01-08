<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Frontdeskcrm\Models\Registration;
// use Modules\Website\Database\Factories\RoomFactory;

class Room extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'price',
        'capacity',
        'status',
        'description',
        'amenities',
        'image_url',
        'video_url',
        'is_featured',
        'bed_type',
        'size'
    ];

    protected $casts = [
        'amenities' => 'array',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Relationship: Online Website Bookings
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Relationship: Physical Frontdesk Check-ins
     * This allows us to check if a guest is currently in the room.
     */
    public function registrations()
    {
        // Check if the Frontdesk module is actually installed/enabled
        if (class_exists(Registration::class)) {
            return $this->hasMany(Registration::class, 'room_id');
        }

        // Fallback to prevent crashes if module is missing
        return $this->hasMany(Booking::class)->whereRaw('1 = 0');
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'amenity_room');
    }
    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }
    // protected static function newFactory(): RoomFactory
    // {
    //     // return RoomFactory::new();
    // }
}
