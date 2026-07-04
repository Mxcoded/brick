<?php

namespace Modules\Frontdeskcrm\Models;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\RoomUnit;
use App\Models\Traits\HasProperty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Website\Models\Booking;

class Registration extends Model
{
    use HasFactory, HasProperty;

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
        // New deposit, security deposit, pre-auth, discount fields
        'deposit_required',
        'deposit_amount',
        'deposit_deadline',
        'security_deposit_amount',
        'security_deposit_collected_at',
        'security_deposit_refunded_at',
        'security_deposit_status',
        'pre_authorization_amount',
        'pre_authorization_reference',
        'pre_authorization_status',
        'pre_authorization_expires_at',
        'discount_type',
        'discount_value',
        'discount_percent',
        'discount_reason',
        'corporate_account_id',
        'billing_to_account',
        'special_requests',
        'estimated_arrival_at',
        'opt_in_marketing',
        'pre_arrival_token',
        'pre_arrival_completed_at',
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
        'deposit_required' => 'boolean',
        'deposit_deadline' => 'datetime',
        'security_deposit_collected_at' => 'datetime',
        'security_deposit_refunded_at' => 'datetime',
        'pre_authorization_expires_at' => 'datetime',
        'estimated_arrival_at' => 'datetime',
        'pre_arrival_completed_at' => 'datetime',
        'opt_in_marketing' => 'boolean',
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
    public function documents(): HasMany
    {
        return $this->hasMany(GuestDocument::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(GuestMessage::class);
    }

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

    /**
     * Get the payment history for this registration.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(RegistrationPayment::class)->latest('payment_date');
    }

    /**
     * Get the folio charges for this registration.
     */
    public function folioCharges(): HasMany
    {
        return $this->hasMany(FolioCharge::class);
    }

    public function loyaltyPoints(): HasMany
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function corporateAccount(): BelongsTo
    {
        return $this->belongsTo(CorporateAccount::class);
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
     * Helper to get total charges on the folio.
     */
    public function getTotalChargesAttribute()
    {
        return $this->folioCharges()->sum('amount');
    }

    /**
     * Helper to get outstanding balance (total charges - total paid).
     */
    public function getBalanceAttribute()
    {
        return $this->total_charges - $this->total_paid;
    }

    /**
     * Total discount applied to this registration.
     */
    public function getTotalDiscountAttribute()
    {
        if ($this->discount_type === 'percentage' && $this->discount_percent) {
            return round(($this->room_rate ?? 0) * ($this->discount_percent / 100), 2);
        }
        if ($this->discount_type === 'fixed' && $this->discount_value) {
            return $this->discount_value;
        }
        // Fallback to GuestType discount_rate if no per-registration discount set
        if ($this->guestType && $this->guestType->discount_rate > 0) {
            return round(($this->room_rate ?? 0) * ($this->guestType->discount_rate / 100), 2);
        }

        return 0;
    }

    /**
     * Room rate after discount per night.
     */
    public function getDiscountedRateAttribute()
    {
        return max(0, ($this->room_rate ?? 0) - $this->total_discount);
    }

    /**
     * Total deposit paid (payments with payment_type=deposit).
     */
    public function getTotalDepositPaidAttribute()
    {
        return (float) $this->payments()->where('payment_type', 'deposit')->sum('amount');
    }

    /**
     * Total security deposit collected.
     */
    public function getSecurityDepositCollectedAttribute()
    {
        return (float) $this->payments()->where('payment_type', 'security_deposit')->sum('amount');
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
