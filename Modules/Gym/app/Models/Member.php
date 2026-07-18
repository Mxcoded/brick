<?php

namespace Modules\Gym\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Gym\Database\Factories\MemberFactory;

class Member extends Model
{
    use HasFactory, HasProperty;

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
        'property_id',
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
