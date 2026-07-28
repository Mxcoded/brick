<?php

namespace Modules\Frontdeskcrm\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Website\Models\Booking;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Registration extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'guest_id',
        'reservation_code',
        'booking_id', // ✅ Verified: It's here
        'original_check_in_date',   // Immutable: original booking check-in
        'original_check_out_date',  // Immutable: original booking check-out
        'booking_group_id',         // Links registrations from same group (GRP reference)
        'dates_adjusted',           // Track if dates were modified from original
        'billing_policy',           // The policy used (strict/flexible)
        'guest_type_id',
        'booking_source_id',
        'parent_registration_id',
        'is_group_lead',
        'title',
        'full_name',
        'gender',
        'birthday',
        'contact_number',
        'email',
        'nationality',
        'billing_type', // ✅ Verified
        'home_address',
        'occupation',
        'company_name',
        'emergency_name',
        'emergency_contact',
        'emergency_relationship',
        'room_allocation',
        'room_id',           // Legacy: will be deprecated
        'room_type_id',      // NEW: The room type
        'room_unit_id',      // NEW: The assigned unit
        'room_rate',
        'bed_breakfast',
        'check_in',
        'check_out',
        'no_of_guests',
        'no_of_nights',
        'payment_method',
        'stay_status',
        'total_amount',
        'finalized_by_agent_id',
        'agreed_to_policies',
        'guest_signature',
        'registration_date',
        'actual_checkout_at', // ✅ Verified
        'checked_out_by_agent_id', // ✅ Verified
        'review_rating',
        'review_comment',
        'front_desk_agent',
        'checked_in_at',
        'rate_code_id',
        'nights_posted',
        'last_audit_date',
        'city_ledger_account_id',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'original_check_in_date' => 'date',
        'original_check_out_date' => 'date',
        'dates_adjusted' => 'boolean',
        'registration_date' => 'date',
        'actual_checkout_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'birthday' => 'date',
        'bed_breakfast' => 'boolean',
        'is_group_lead' => 'boolean',
        'nights_posted' => 'integer',
        'last_audit_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($registration) {
            if ($registration->check_in && $registration->check_out) {
                $registration->no_of_nights = $registration->check_in->diffInDays($registration->check_out);
            }
        });
    }

    // Relationships
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function bookingSource(): BelongsTo
    {
        return $this->belongsTo(BookingSource::class);
    }

    public function guestType(): BelongsTo
    {
        return $this->belongsTo(GuestType::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'parent_registration_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Registration::class, 'parent_registration_id');
    }

    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by_agent_id');
    }

    /**
     * Relationship: The room type.
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Relationship: The assigned room unit.
     */
    public function roomUnit(): BelongsTo
    {
        return $this->belongsTo(RoomUnit::class);
    }

    /**
     * Legacy: The room (backward compatibility).
     *
     * @deprecated Use roomType() and roomUnit() instead.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function rateCode(): BelongsTo
    {
        return $this->belongsTo(RateCode::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(RegistrationCharge::class);
    }

    public function folios(): HasMany
    {
        return $this->hasMany(Folio::class);
    }

    public function folio(): HasOne
    {
        return $this->hasOne(Folio::class)->latest();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the payment history for this registration.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(RegistrationPayment::class)->latest('payment_date');
    }

    /**
     * Helper to get total paid amount from the new table
     */
    public function getTotalPaidAttribute()
    {
        // Sum from local payments + any online booking payment
        $localTotal = $this->payments()->sum('amount');
        $onlineTotal = $this->booking ? $this->booking->amount_paid : 0;

        return $localTotal + $onlineTotal;
    }

    /**
     * Get all registrations in the same booking group.
     */
    public function groupRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'booking_group_id', 'booking_group_id');
    }

    /**
     * Check if this registration is part of a group booking.
     */
    public function isGroupBooking(): bool
    {
        return ! empty($this->booking_group_id);
    }

    /**
     * Check if dates have been modified from the original booking.
     */
    public function hasDateAdjustments(): bool
    {
        return $this->dates_adjusted ?? false;
    }

    /**
     * Get original dates display for read-only UI.
     */
    public function getOriginalDatesAttribute(): ?string
    {
        if ($this->original_check_in_date && $this->original_check_out_date) {
            return $this->original_check_in_date->format('M d, Y').' - '.$this->original_check_out_date->format('M d, Y');
        }

        return null;
    }

    public function cityLedgerAccount(): BelongsTo
    {
        return $this->belongsTo(CityLedgerAccount::class);
    }

    /**
     * Scope to find registrations by group ID.
     */
    public function scopeInGroup($query, string $groupId)
    {
        return $query->where('booking_group_id', $groupId);
    }

    /**
     * Scope to find registrations from web bookings (has booking_id).
     */
    public function scopeFromWebBooking($query)
    {
        return $query->whereNotNull('booking_id');
    }
}
