<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
// use Modules\Website\Database\Factories\BookingFactory;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'room_id',
        'check_in',
        'check_out',
        'guest_name',
        'guest_email',
        'guest_phone',
        'status',
        'user_id',
        'confirmation_token',
        'booking_ref_number',
    ];

    protected $dates = ['check_in', 'check_out'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // protected static function newFactory(): BookingFactory
    // {
    //     // return BookingFactory::new();
    // }
}
