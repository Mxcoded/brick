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
        'billing_type',
        'home_address',
        'occupation',
        'company_name',
        'emergency_name',
        'emergency_contact',
        'emergency_relationship',
        'room_type',
        'room_allocation',
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
        'registration_date'
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'registration_date' => 'date',
        'birthdate' => 'date',
        'bed_breakfast' => 'boolean',
        'is_group_lead' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        // This event listener will now only calculate nights and total amount.
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
}
