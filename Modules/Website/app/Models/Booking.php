<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Account\Models\OrderItems;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_reference',
        'room_id',
        'user_id',            // Optional: links to registered user
        'guest_profile_id',   // Optional: links to CRM profile

        // Guest Details (Snapshot for guest checking out as guest)
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
        'payment_status',     // pending, paid, failed, partial
        'payment_method',

        // Status & Notes
        'status',             // pending, confirmed, checked_in, cancelled, completed
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
     * ✅ AUTOMATION LOGIC
     * The "booted" method runs automatically whenever this Model is saved.
     */
    protected static function booted()
    {
        static::saving(function ($booking) {
            // 1. Auto-Confirm if Paid
            if ($booking->payment_status === 'paid' && $booking->status === 'pending') {
                $booking->status = 'confirmed';
            }

            // 2. Auto-Generate Reference if missing
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = 'BK-' . strtoupper(uniqid());
            }
        });
    }
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
        return $this->belongsTo(User::class);
    }

    public function orderItems() {
        return $this->morphMany(OrderItems::class, 'itemable');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ✅ FIXED: Changed from 'scopeIsAvailable' to 'public static function isAvailable'
     * This ensures it returns a Boolean (true/false), not a Query Builder.
     */
    public static function isAvailable($roomId, $checkIn, $checkOut, $ignoreBookingId = null)
    {
        // 1. Check Website Bookings (Existing Logic)
        $hasBookingConflict = self::where('room_id', $roomId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where('check_in_date', '<', $checkOut)
                    ->where('check_out_date', '>', $checkIn);
            })
            ->when($ignoreBookingId, function ($q) use ($ignoreBookingId) {
                $q->where('id', '!=', $ignoreBookingId);
            })
            ->exists();

        if ($hasBookingConflict) {
            return false;
        }

        // 2. Check Frontdesk Registrations (THE FIX)
        if (class_exists(Registration::class)) {
            $hasPhysicalConflict = Registration::where('room_id', $roomId)
                // ✅ FIX: Add 'reserved' to block future walk-ins too
                ->whereIn('stay_status', ['checked_in', 'draft_by_guest', 'reserved'])
                ->where(function ($q) use ($checkIn, $checkOut) {
                    $q->where('check_in', '<', $checkOut)
                        ->where('check_out', '>', $checkIn);
                })
                ->exists();

            if ($hasPhysicalConflict) {
                return false;
            }
        }

        return true;
    }
}
