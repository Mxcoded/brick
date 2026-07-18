<?php

namespace Modules\Staff\Models;

use Illuminate\Database\Eloquent\Model;

class HikvisionAttendanceRecord extends Model
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
}
