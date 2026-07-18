<?php

namespace Modules\Staff\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Staff\Database\Factories\LeaveBalanceFactory;

class LeaveBalance extends Model
{
    use HasFactory, HasProperty;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_id',
        'leave_type',
        'total_days',
        'used_days',
        'year',
        'property_id',
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
