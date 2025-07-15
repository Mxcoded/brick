<?php

namespace Modules\Staff\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Staff\Database\Factories\LeaveRequestFactory;

class LeaveRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_id',
        'staff_code', // Added staff_code to fillable attributes
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'admin_note',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Calculate number of leave days
    public function getDaysCountAttribute()
    {
        return (new \DateTime($this->start_date))->diff(new \DateTime($this->end_date))->days + 1;
    }

    // protected static function newFactory(): LeaveRequestFactory
    // {
    //     // return LeaveRequestFactory::new();
    // }
}
