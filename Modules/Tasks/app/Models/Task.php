<?php

namespace Modules\Tasks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Staff\Models\Employee;
use App\Models\User;
// use Modules\Tasks\Database\Factories\TaskFactory;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
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

    public function scopePending($q) { return $q->where('status', 'pending'); }
    public function scopeInProgress($q) { return $q->where('status', 'in_progress'); }
    public function scopeCompleted($q) { return $q->where('status', 'completed'); }
}
