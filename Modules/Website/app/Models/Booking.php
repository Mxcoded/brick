<?php

namespace Modules\Website\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\RateCode;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Emails\BookingCancellation;
use Modules\Website\Emails\BookingConfirmation;
use Modules\Website\Emails\BookingStatusUpdate;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Booking extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes {
        Auditable::resolveUser as auditResolveUser;
    }

    protected $fillable = [
        'booking_reference',
        'booking_group_id',   // NEW: Links multiple bookings from a single cart transaction
        'room_id',            // Legacy: will be deprecated
        'room_type_id',       // NEW: The room type booked
        'room_unit_id',       // NEW: Assigned unit (nullable until check-in)
        'rate_code_id',       // Rate code used for pricing
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
     * Resolve the actor for the audit trail.
     *
     * Authenticated admins / logged-in guests are captured by the default
     * resolver. Anonymous website bookings have no authenticated user, so we
     * fall back to a registered guest whose email matches the booking, keeping
     * the audit trail attributable instead of showing "System".
     */
    protected function resolveUser()
    {
        $user = $this->auditResolveUser();

        if ($user) {
            return $user;
        }

        if (! empty($this->guest_email)) {
            return User::where('email', $this->guest_email)->first();
        }

        return null;
    }

    /**
     * Tag audits with the guest email so anonymous bookings remain traceable
     * even when no registered user account exists.
     */
    public function generateTags(): array
    {
        $tags = [];
        if (! empty($this->guest_email)) {
            $tags[] = 'guest:'.$this->guest_email;
        }

        return array_values(array_unique($tags));
    }

    /**
     * Relationship: The room type being booked.
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Relationship: The rate code used for pricing.
     */
    public function rateCode()
    {
        return $this->belongsTo(RateCode::class);
    }

    /**
     * Add-ons / upsells attached to this booking (line-item snapshot).
     */
    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'booking_addon')
            ->withPivot(['name', 'price', 'is_per_night', 'quantity', 'total'])
            ->withTimestamps();
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
    public static function isAvailable($roomUnitId, $checkIn, $checkOut, $ignoreBookingId = null)
    {
        $hasBookingConflict = self::where('room_unit_id', $roomUnitId)
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

        if (class_exists(Registration::class)) {
            $hasPhysicalConflict = Registration::where('room_unit_id', $roomUnitId)
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

    /**
     * Send email notification to guest and reservation staff for a status change.
     *
     * @param  string|null  $statusLabel  Override the status label (e.g. "Checked In", "Checkout Complete")
     * @param  bool  $skipGuest  Skip sending to guest (e.g. when guest initiated the action)
     */
    public function sendNotification(?string $statusLabel = null, bool $skipGuest = false): void
    {
        $label = $statusLabel ?? ucfirst(str_replace('_', ' ', $this->status));

        try {
            // Send to guest
            if (! $skipGuest && ! empty($this->guest_email)) {
                $this->sendGuestNotification($label);
            }

            // Send staff copy
            $this->sendStaffNotification($label);
        } catch (\Throwable $e) {
            Log::error('Booking notification failed for '.$this->booking_reference.': '.$e->getMessage());
        }
    }

    /**
     * Send the appropriate email to the guest based on status.
     */
    protected function sendGuestNotification(string $label): void
    {
        if ($this->status === 'cancelled') {
            Mail::to($this->guest_email)->send(new BookingCancellation($this));
        } elseif (in_array($this->status, ['pending', 'confirmed'])) {
            // For new/confirmed bookings, use the dedicated confirmation Mailable
            Mail::to($this->guest_email)->send(new BookingConfirmation($this));
        } else {
            // For checked_in, completed, and other status changes
            Mail::to($this->guest_email)->send(new BookingStatusUpdate($this, $label));
        }
    }

    /**
     * Send staff copy to the reservations team.
     */
    protected function sendStaffNotification(string $label): void
    {
        $reservationsEmail = config('mail.reservations_email');
        if (empty($reservationsEmail)) {
            return;
        }

        if ($this->status === 'cancelled') {
            Mail::to($reservationsEmail)->send(new BookingCancellation($this, true));
        } elseif (in_array($this->status, ['pending', 'confirmed'])) {
            Mail::to($reservationsEmail)->send(new BookingConfirmation($this, true));
        } else {
            Mail::to($reservationsEmail)->send(new BookingStatusUpdate($this, $label, true));
        }
    }
}
