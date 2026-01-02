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
        'slug',           // Critical for SEO links
        'description',
        'price',          // Critical: Fixes the 0 price issue
        'capacity',
        'size',
        'bed_type',
        'video_url',
        'amenities',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'amenities' => 'array',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'amenity_room');
    }
    public function images()
    {
        return $this->hasMany(RoomImage::class)->orderBy('order');
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
    // protected static function newFactory(): RoomFactory
    // {
    //     // return RoomFactory::new();
    // }
}
