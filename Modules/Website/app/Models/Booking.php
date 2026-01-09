<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Modules\Frontdeskcrm\Models\Registration;

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

    /**
     * Scope: Check if a room is available for a specific date range.
     * Checks both WEBSITE BOOKINGS and FRONTDESK REGISTRATIONS.
     * * @param Builder $query
     * @param int $roomId
     * @param string $checkIn
     * @param string $checkOut
     * @param int|null $ignoreBookingId (Optional: ID to ignore for updates)
     * @return bool
     */
    public function scopeIsAvailable($query, $roomId, $checkIn, $checkOut, $ignoreBookingId = null)
    {
        // 1. Check for overlapping Online Bookings
        $hasBookingConflict = self::where('room_id', $roomId)
            ->where('status', '!=', 'cancelled') // Ignore cancelled bookings
            ->where(function ($q) use ($checkIn, $checkOut) {
                // Overlap Logic: (StartA < EndB) and (EndA > StartB)
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

        // 2. Check for overlapping Frontdesk Registrations (Physical Guests)
        // We use class_exists to ensure the module is enabled without crashing
        if (class_exists(Registration::class)) {
            $hasPhysicalConflict = Registration::where('room_id', $roomId)
                ->whereIn('status', ['checked_in', 'reserved', 'staying']) // Active statuses
                ->where(function ($q) use ($checkIn, $checkOut) {
                    // Adjust column names if your Registration table uses different names
                    $q->where('check_in_date', '<', $checkOut)
                        ->where('check_out_date', '>', $checkIn);
                })
                ->exists();

            if ($hasPhysicalConflict) {
                return false;
            }
        }

        return true; // No conflicts found
    }
}
