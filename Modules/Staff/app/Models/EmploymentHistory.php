<?php

namespace Modules\Staff\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Staff\Database\Factories\EmploymentHistoryFactory;

class EmploymentHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_id',
        'employer_name',
        'employer_contact',
        'position_held',
        'duration',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // protected static function newFactory(): EmploymentHistoryFactory
    // {
    //     // return EmploymentHistoryFactory::new();
    // }
}
