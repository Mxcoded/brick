<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'user_id',
        'check_in',
        'check_out',
        'guest_name',
        'guest_email',
        'guest_phone',
        'guest_company',
        'guest_address',
        'guest_nationality',
        'guest_id_type',
        'guest_id_number',
        'number_of_guests',
        'number_of_children',
        'special_requests',
        'status',
        'total_price',
        'deposit_amount',
        'payment_status',
        'payment_method',
        'source',
        'check_in_time',
        'check_out_time',
        'assigned_staff_id',
        'room_status',
        'confirmation_token',
        'booking_ref_number',
        'created_by',
        'updated_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $dates = ['check_in', 'check_out', 'cancelled_at'];

    protected $casts = [
        'total_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function invoice()
    // {
    //     return $this->belongsTo(Invoice::class);
    // }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
