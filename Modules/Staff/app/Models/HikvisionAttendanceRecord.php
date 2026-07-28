<?php

namespace Modules\Staff\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class HikvisionAttendanceRecord extends Model implements AuditableContract
{
    protected $fillable = [
        'original_id',
        'employee_id',
        'pin',
        'punch_time',
        'punch_type',
        'raw_data',
        'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'punch_time' => 'datetime',
            'imported_at' => 'datetime',
            'raw_data' => 'json',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    use Auditable;
}
