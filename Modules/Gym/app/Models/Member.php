<?php

namespace Modules\Gym\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Gym\Database\Factories\MemberFactory;

class Member extends Model
{
    use HasFactory;

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
