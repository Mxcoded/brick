<?php

namespace Modules\Staff\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Staff\Database\Factories\LeaveBalanceFactory;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class LeaveBalance extends Model implements AuditableContract
{
    use HasFactory, Auditable;

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