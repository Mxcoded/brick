<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Website\Database\Factories\RoomFactory;

class Room extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'price_per_night',
        'image',
        'video',
        'featured',
        'capacity',
        'size',
    ];

    protected $casts = [
        'featured' => 'boolean',
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
    // protected static function newFactory(): RoomFactory
    // {
    //     // return RoomFactory::new();
    // }
}
