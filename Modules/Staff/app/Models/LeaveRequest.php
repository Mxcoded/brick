<?php

namespace Modules\Staff\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Staff\Database\Factories\LeaveRequestFactory;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class LeaveRequest extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_id',
        'covered_by',
        'staff_code',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'admin_note',
        'days_count',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function coverEmployee()
    {
        return $this->belongsTo(Employee::class, 'covered_by');
    }

    // // Calculate number of leave days
    // public function getDaysCountAttribute()
    // {
    //     return (new \DateTime($this->start_date))->diff(new \DateTime($this->end_date))->days + 1;
    // }

    // protected static function newFactory(): LeaveRequestFactory
    // {
    //     // return LeaveRequestFactory::new();
    // }
}