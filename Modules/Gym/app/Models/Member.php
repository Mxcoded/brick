<?php

namespace Modules\Gym\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Gym\Database\Factories\MemberFactory;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Member extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'membership_id',
        'full_name',
        'date_of_birth',
        'gender',
        'phone_number',
        'email_address',
        'home_address',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_number',
        'medical_conditions',
        'fitness_goals',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }
    // protected static function newFactory(): MemberFactory
    // {
    //     // return MemberFactory::new();
    // }
}