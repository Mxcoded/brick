<?php

namespace Modules\Staff\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ShiftAssignment extends Model implements AuditableContract
{
    protected $fillable = [
        'employee_id', 'shift_id', 'date', 'note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendance()
    {
        return $this->hasOne(AttendanceLog::class);
    }

    use Auditable;
}
