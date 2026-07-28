<?php

namespace Modules\Staff\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class AttendanceLog extends Model implements AuditableContract
{
    protected $fillable = [
        'employee_id', 'shift_assignment_id', 'date',
        'clock_in', 'clock_out', 'status',
        'late_minutes', 'overtime_minutes',
        'clock_in_note', 'clock_out_note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'late_minutes' => 'integer',
            'overtime_minutes' => 'integer',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shiftAssignment()
    {
        return $this->belongsTo(ShiftAssignment::class);
    }

    public function getDurationAttribute(): ?string
    {
        if (! $this->clock_in || ! $this->clock_out) {
            return null;
        }

        return $this->clock_in->diffInMinutes($this->clock_out);
    }

    public function getDurationFormattedAttribute(): ?string
    {
        $minutes = $this->duration;
        if ($minutes === null) {
            return null;
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return "{$hours}h {$mins}m";
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeToday($query)
    {
        return $query->forDate(now()->today());
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    use Auditable;
}
