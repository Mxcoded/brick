<?php

namespace Modules\Tasks\Services;

use Carbon\Carbon;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAssignment;
use Modules\Tasks\Models\TaskUpdate;

class TaskRecurrenceService
{
    public function createNextOccurrence(Task $completedTask): ?Task
    {
        if (! $completedTask->is_recurring || ! $completedTask->recurrence_type) {
            return null;
        }

        if (! $this->shouldContinueRecurring($completedTask)) {
            return null;
        }

        $nextDeadline = $this->calculateNextDeadline($completedTask->deadline, $completedTask->recurrence_type);

        $rootTask = $completedTask->parentTask ?? $completedTask;

        $today = Carbon::today();
        $datePart = $today->format('dmy');
        $taskCount = Task::whereDate('created_at', $today)->count() + 1;
        $taskNumber = sprintf('TASK-%s-%d', $datePart, $taskCount);

        $nextTask = Task::create([
            'task_number' => $taskNumber,
            'date' => $today->toDateString(),
            'created_by' => $completedTask->created_by,
            'description' => $completedTask->description,
            'priority' => $completedTask->priority,
            'deadline' => $nextDeadline,
            'status' => 'pending',
            'is_recurring' => true,
            'recurrence_type' => $completedTask->recurrence_type,
            'recurrence_end_date' => $rootTask->recurrence_end_date,
            'parent_task_id' => $completedTask->id,
        ]);

        TaskUpdate::create([
            'task_id' => $nextTask->id,
            'user_id' => $completedTask->created_by,
            'action' => 'created_from_recurring',
            'changes' => ['parent_task_number' => $completedTask->task_number],
        ]);

        $originalAssignees = $completedTask->employees;
        foreach ($originalAssignees as $employee) {
            TaskAssignment::create([
                'task_id' => $nextTask->id,
                'employee_id' => $employee->id,
            ]);
        }

        if ($originalAssignees->isNotEmpty()) {
            TaskUpdate::create([
                'task_id' => $nextTask->id,
                'user_id' => $completedTask->created_by,
                'action' => 'assignees_changed',
                'changes' => ['assigned' => $originalAssignees->pluck('name')->toArray()],
            ]);
        }

        return $nextTask;
    }

    public function shouldContinueRecurring(Task $task): bool
    {
        if (! $task->is_recurring || ! $task->recurrence_type) {
            return false;
        }

        $rootTask = $task->parentTask ?? $task;

        if ($rootTask->recurrence_end_date && Carbon::today()->gt($rootTask->recurrence_end_date)) {
            return false;
        }

        return true;
    }

    public function calculateNextDeadline(Carbon $currentDeadline, string $recurrenceType): Carbon
    {
        return match ($recurrenceType) {
            'daily' => $currentDeadline->copy()->addDay(),
            'weekly' => $currentDeadline->copy()->addWeek(),
            'biweekly' => $currentDeadline->copy()->addWeeks(2),
            'monthly' => $currentDeadline->copy()->addMonth(),
            default => $currentDeadline->copy()->addWeek(),
        };
    }
}
