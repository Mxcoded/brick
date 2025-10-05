<?php

namespace Modules\Frontdeskcrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_id',
        'booking_source_id',
        'guest_type_id',
        'group_master_id',
        'is_group_lead',
        'title',
        'full_name',
        'nationality',
        'contact_number',
        'birthday',
        'email',
        'occupation',
        'company_name',
        'home_address',
        'room_type',
        'room_rate',
        'bed_breakfast',
        'check_in',
        'no_of_guests',
        'check_out',
        'no_of_nights',
        'payment_method',
        'emergency_name',
        'emergency_relationship',
        'emergency_contact',
        'agreed_to_policies',
        'guest_signature',
        'registration_date',
        'front_desk_agent',
        'stay_status',
        'total_amount',
        'checkout_date',
        'review_rating',
        'review_comment',
    ];

    protected $casts = [
        'birthday' => 'date',
        'check_in' => 'date',
        'check_out' => 'date',
        'registration_date' => 'date',
        'checkout_date' => 'date',
        'bed_breakfast' => 'boolean',
        'room_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'no_of_guests' => 'integer',
        'no_of_nights' => 'integer',
        'review_rating' => 'integer',
        'agreed_to_policies' => 'boolean',
        'is_group_lead' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($registration) {
            // Calculate nights if dates are set
            if ($registration->check_in && $registration->check_out) {
                $registration->no_of_nights = $registration->check_in->diffInDays($registration->check_out);
            }

            // Set registration date if empty
            if (empty($registration->registration_date)) {
                $registration->registration_date = Carbon::now()->toDateString();
            }

            // Auto-calc total_amount if room_rate and nights are set
            if ($registration->room_rate && $registration->no_of_nights && !$registration->total_amount) {
                $registration->total_amount = $registration->room_rate * $registration->no_of_nights;
            }

            // Default stay_status for new records (overridable)
            if (is_null($registration->stay_status)) {
                $registration->stay_status = 'checked_in';
            }
        });

        static::creating(function ($registration) {
            // Ensure defaults for drafts or new records
            if ($registration->stay_status === 'draft_by_guest') {
                $registration->room_type = $registration->room_type ?? 'Pending Assignment';
                $registration->room_rate = $registration->room_rate ?? 0;
                $registration->payment_method = $registration->payment_method ?? null;
                $registration->front_desk_agent = $registration->front_desk_agent ?? 'Guest Self-Submitted';
                $registration->total_amount = 0;
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

    public function groupMaster(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'group_master_id');
    }

    public function groupMembers(): HasMany
    {
        return $this->hasMany(Registration::class, 'group_master_id');
    }

    // Scopes
    public function scopeCheckedIn($query)
    {
        return $query->where('stay_status', 'checked_in');
    }

    public function scopeCheckedOut($query)
    {
        return $query->where('stay_status', 'checked_out');
    }

    public function scopeDraftByGuest($query)
    {
        return $query->where('stay_status', 'draft_by_guest');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('stay_status', ['checked_in', 'draft_by_guest']);
    }

    // Accessors/Mutators if needed
    public function getFullGuestNameAttribute()
    {
        return trim(($this->title ? $this->title . ' ' : '') . $this->full_name);
    }
}
