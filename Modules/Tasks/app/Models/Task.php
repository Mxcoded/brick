<?php

namespace Modules\Tasks\Models;

use App\Models\Traits\HasProperty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Staff\Models\Employee;

class Task extends Model
{
    use HasFactory, HasProperty;

    protected $fillable = [
        'property_id',
        'task_number',
        'date',
        'created_by',
        'description',
        'priority',
        'deadline',
        'status',
        'completion_date',
        'notes',
        'non_completion_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'deadline' => 'date',
        'completion_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees()
    {
        return $this->hasMany(TaskAssignment::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'task_assignments', 'task_id', 'employee_id');
    }

    public function updates()
    {
        return $this->hasMany(TaskUpdate::class, 'task_id');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }

    public function scopeInProgress($q)
    {
        return $q->where('status', 'in_progress');
    }

    public function scopeCompleted($q)
    {
        return $q->where('status', 'completed');
    }

    public function scopeFilter($q, array $filters)
    {
        $q->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['priority'] ?? null, fn ($q, $v) => $q->where('priority', $v))
            ->when($filters['assignee_id'] ?? null, fn ($q, $v) => $q->whereHas('employees', fn ($q) => $q->where('employees.id', $v)))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('description', 'like', "%{$v}%")
                    ->orWhere('task_number', 'like', "%{$v}%");
            }))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
    }
}
