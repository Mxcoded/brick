<?php

namespace Modules\Maintenance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MaintenanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'department',
        'priority',
        'complaint_datetime',
        'nature_of_complaint',
        'image',
        'lodged_by',
        'received_by',
        'cost_of_fixing',
        'completion_date',
        'status',
    ];

    protected $dates = [
        'complaint_datetime',
        'completion_date',
    ];

    protected $casts = [
        'complaint_datetime' => 'datetime',
        'completion_date' => 'date',
        'cost_of_fixing' => 'decimal:2',
    ];

    public const DEPARTMENTS = [
        'IT' => 'IT (Information Technology)',
        'Maintenance' => 'Maintenance (Facilities)',
        'Housekeeping' => 'Housekeeping',
        'Electrical' => 'Electrical',
        'Plumbing' => 'Plumbing',
        'HVAC' => 'HVAC',
        'Security' => 'Security',
        'Other' => 'Other',
    ];

    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return str_starts_with($this->image, 'http')
            ? $this->image
            : Storage::url($this->image);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('complaint_datetime', [$from, $to]);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['new', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}
