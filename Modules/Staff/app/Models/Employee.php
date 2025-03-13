<?php

namespace Modules\Staff\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Staff\Database\Factories\EmployeeFactory;

class Employee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'place_of_birth',
        'state_of_origin',
        'lga',
        'nationality',
        'gender',
        'date_of_birth',
        'marital_status',
        'blood_group',
        'genotype',
        'phone_number',
        'residential_address',
        'next_of_kin_name',
        'next_of_kin_phone',
        'ice_contact_name',
        'ice_contact_phone',
        'profile_image',
        'cv_path',
        'status',
    ];

    public function employmentHistories()
    {
        return $this->hasMany(EmploymentHistory::class);
    }

    public function educationalBackgrounds()
    {
        return $this->hasMany(EducationalBackground::class);
    }


    // protected static function newFactory(): EmployeeFactory
    // {
    //     // return EmployeeFactory::new();
    // }
}
