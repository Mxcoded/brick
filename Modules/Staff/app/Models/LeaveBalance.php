<?php

namespace Modules\Staff\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Staff\Database\Factories\LeaveBalanceFactory;

class LeaveBalance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_id',
        'leave_type',
        'total_days',
        'used_days',
        'year',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Remaining days
    public function getRemainingDaysAttribute()
    {
        return $this->total_days - $this->used_days;
    }

    // protected static function newFactory(): LeaveBalanceFactory
    // {
    //     // return LeaveBalanceFactory::new();
    // }
}
