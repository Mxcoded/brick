<?php

namespace Modules\Website\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Booking extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'booking_reference',
        'booking_group_id',   // NEW: Links multiple bookings from a single cart transaction
        'room_id',            // Legacy: will be deprecated
        'room_type_id',       // NEW: The room type booked
        'room_unit_id',       // NEW: Assigned unit (nullable until check-in)
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
        'source',
        'follow_up_sent_at',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'follow_up_sent_at' => 'datetime',
        'deleted_at' => 'datetime',
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
                $booking->booking_reference = 'BK-'.strtoupper(uniqid());
            }
        });
    }

    /**
     * Relationship: The room type being booked.
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Relationship: The assigned room unit (assigned at check-in).
     */
    public function roomUnit()
    {
        return $this->belongsTo(RoomUnit::class);
    }

    /**
     * Legacy: The room being booked (backward compatibility).
     *
     * @deprecated Use roomType() and roomUnit() instead.
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

    /**
     * Link to the detailed CRM Guest Profile.
     */
    public function guest()
    {
        // Links 'guest_profile_id' in bookings table to 'id' in guests table
        return $this->belongsTo(Guest::class, 'guest_profile_id');
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