<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Models\Amenity;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomImage;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

class Room extends Model implements AuditableInterface
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'capacity',
        'status',
        'description',
        'image_url',
        'video_url',
        'is_featured',
        'bed_type',
        'size',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'amenity_room');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }

    public function registrations()
    {
        if (class_exists(Registration::class)) {
            return $this->hasMany(Registration::class, 'room_id');
        }

        return $this->hasMany(Booking::class)->whereRaw('1 = 0');
    }
}
