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
        'is_completed',
        'completion_date',
        'notes',
        'non_completion_reason',
        'is_successful',
        'meets_expectations',
        'gm_notes',
    ];

    protected $casts = [
        'date' => 'date',
        'deadline' => 'date',
        'completion_date' => 'date',
        'is_completed' => 'boolean',
        'is_successful' => 'boolean',
        'meets_expectations' => 'boolean',
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

    // protected static function newFactory(): TaskFactory
    // {
    //     // return TaskFactory::new();
    // }
}
