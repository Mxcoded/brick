<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Booking extends Model
{
    use HasFactory;

    // FIX: Add all fields you want to save to this array
    protected $fillable = [
        'booking_reference',  // This was causing your error
        'room_id',
        'user_id',            // Optional: if logged in
        'guest_profile_id',   // Optional: link to CRM

        // Guest Details (Snapshot)
        'guest_name',
        'guest_email',
        'guest_phone',

        // Dates & Occupancy
        'check_in_date',
        'check_out_date',
        'adults',
        'children',

        // Financials
        'total_amount',
        'amount_paid',
        'payment_status',
        'payment_method',

        // Status
        'status',
        'confirmation_token',
        'special_requests',
        'admin_notes',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    /**
     * Relationship: The room being booked.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Relationship: The registered user (if applicable).
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
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
