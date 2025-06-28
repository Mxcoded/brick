<?php

namespace Modules\Gym\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
// use Modules\Gym\Database\Factories\MembershipFactory;

class Membership extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'package_type',
        'subscription_plan',
        'personal_trainer',
        'sessions',
        'total_cost',
        'start_date',
        'next_billing_date',
        'created_by',
        'registration_date',
        'terms_agreed',
    ];

    protected $casts = [
        'start_date' => 'date',
        'next_billing_date' => 'date',
        'registration_date' => 'date',
        'terms_agreed' => 'boolean',
        'total_cost' => 'decimal:2',
        'personal_trainer' => 'string',
        'sessions' => 'integer',
    ];

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function trainerPayments()
    {
        return $this->hasMany(TrainerPayment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if subscription is due soon (within 7 days).
     */
    public function isDueSoon(): bool
    {
        return now()->addDays(7)->gte($this->next_billing_date);
    }

    // protected static function newFactory(): MembershipFactory
    // {
    //     // return MembershipFactory::new();
    // }
}
