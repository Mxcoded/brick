<?php

namespace Modules\Tasks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Staff\Models\Employee;
// use Modules\Tasks\Database\Factories\TaskAssignmentFactory;

class TaskAssignment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['task_id', 'employee_id'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // protected static function newFactory(): TaskAssignmentFactory
    // {
    //     // return TaskAssignmentFactory::new();
    // }
}
