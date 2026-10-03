<?php

namespace Modules\Tasks\Services;

use App\Models\User;
use App\Notifications\TaskAssigned;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Staff\Models\Employee;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAssignment;
use Modules\Tasks\Models\TaskUpdate;
use Modules\Tasks\Support\TaskPermission;

/**
 * All of the task rules, in one place.
 *
 * These used to live in TasksController, which meant the Filament resource would
 * have had to reimplement them to avoid a second, subtly different set. Keeping
 * them here means the old controller and the new admin UI enforce identical
 * rules, and the interesting behaviour (the audit trail, notifications,
 * recurrence and who is allowed to do what) is testable without HTTP.
 */
class TaskService
{
    public function __construct(
        protected TaskRecurrenceService $recurrence,
        protected TaskNumberGenerator $taskNumbers
    ) {}

    /**
     * The next reference for a task raised on the given day.
     */
    public function generateTaskNumber(?Carbon $on = null): string
    {
        return $this->taskNumbers->next($on ?? Carbon::today());
    }

    /**
     * Raise a task, notify its assignees and record the first audit entry.
     */
    public function create(User $user, array $data): Task
    {
        $employeeId = TaskPermission::employeeIdFor($user);
        $canAssign = TaskPermission::can($user, TaskPermission::ASSIGN);
        $isRecurring = (bool) ($data['is_recurring'] ?? false);

        $assigneeIds = $this->resolveAssignees($user, $data['assignees'] ?? null, $canAssign, $employeeId);

        return DB::transaction(function () use ($user, $data, $isRecurring, $assigneeIds): Task {
            $task = Task::create([
                'task_number' => $this->generateTaskNumber(),
                'date' => Carbon::today()->toDateString(),
                'created_by' => $user->id,
                'description' => $data['description'],
                'priority' => $data['priority'],
                'deadline' => $data['deadline'],
                // Set explicitly rather than left to the column default, so the
                // in-memory record the UI redirects to already has a status.
                'status' => 'pending',
                'is_recurring' => $isRecurring,
                'recurrence_type' => $isRecurring ? ($data['recurrence_type'] ?? null) : null,
                'recurrence_end_date' => $isRecurring ? ($data['recurrence_end_date'] ?? null) : null,
            ]);

            $this->recordUpdate($task, $user, 'created', [
                'description' => $data['description'],
                'priority' => $data['priority'],
                'deadline' => $data['deadline'],
                'is_recurring' => $isRecurring,
            ]);

            $this->syncAssignees($task, $assigneeIds, $user, notify: true);

            return $task;
        });
    }

    /**
     * Edit a task. Only its creator may do this.
     *
     * All field and assignee changes are recorded as a single "updated" entry,
     * the way the old controller did, so the activity feed reads as one edit.
     *
     * @return array{task: Task, changes: array<string, mixed>}
     */
    public function update(Task $task, User $user, array $data): array
    {
        $this->assertCreator($task, $user, 'You can only edit tasks you created.');

        $isRecurring = (bool) ($data['is_recurring'] ?? false);
        $changes = $this->diff($task, $data, $isRecurring);

        DB::transaction(function () use ($task, $user, $data, $isRecurring, &$changes): void {
            $task->update([
                'description' => $data['description'],
                'priority' => $data['priority'],
                'deadline' => $data['deadline'],
                'is_recurring' => $isRecurring,
                'recurrence_type' => $isRecurring ? ($data['recurrence_type'] ?? null) : null,
                'recurrence_end_date' => $isRecurring ? ($data['recurrence_end_date'] ?? null) : null,
            ]);

            if (TaskPermission::can($user, TaskPermission::ASSIGN) && array_key_exists('assignees', $data)) {
                $incoming = collect($data['assignees'] ?? [])->filter()->values()->all();

                if ($this->assigneeDiffers($task, $incoming)) {
                    $before = $task->employees->pluck('name')->toArray();

                    $this->syncAssignees($task, $incoming, $user, notify: true);

                    $task->load('employees');

                    $changes['assignees'] = [
                        'from' => $before,
                        'to' => $task->employees->pluck('name')->toArray(),
                    ];
                }
            }

            if ($changes !== []) {
                $this->recordUpdate($task, $user, 'updated', $changes);
            }
        });

        return ['task' => $task->fresh(['employees']), 'changes' => $changes];
    }

    /**
     * Move a task to a status. The creator or any assignee may do this.
     */
    public function changeStatus(Task $task, User $user, string $status): Task
    {
        if (! $this->canActOn($task, $user)) {
            throw new AuthorizationException(
                'You cannot change the status of this task.'
            );
        }

        return $this->applyStatus($task, $user, $status, 'status_changed');
    }

    /**
     * Cycle a task through pending -> in progress -> completed -> pending.
     */
    public function toggleComplete(Task $task, User $user): Task
    {
        $this->assertCreator($task, $user, 'Only the creator can change task status.');

        $next = match ($task->status) {
            'pending' => 'in_progress',
            'in_progress' => 'completed',
            default => 'pending',
        };

        return $this->applyStatus($task, $user, $next, 'status_changed');
    }

    /**
     * Change many tasks at once. Tasks the user does not own are skipped.
     *
     * @param  array<int, int>  $taskIds
     * @return int how many were actually changed
     */
    public function bulkChangeStatus(User $user, array $taskIds, string $status): int
    {
        $changed = 0;

        foreach (Task::whereIn('id', $taskIds)->get() as $task) {
            if ($task->created_by !== $user->id) {
                continue;
            }

            $this->applyStatus($task, $user, $status, 'bulk_status_changed');
            $changed++;
        }

        return $changed;
    }

    /**
     * Add one employee to many tasks.
     *
     * @param  array<int, int>  $taskIds
     * @return int how many were actually changed
     */
    public function bulkAssign(User $user, array $taskIds, int $employeeId): int
    {
        $count = 0;

        foreach (Task::whereIn('id', $taskIds)->get() as $task) {
            if ($task->created_by !== $user->id) {
                continue;
            }

            if ($task->employees->pluck('id')->contains($employeeId)) {
                continue;
            }

            DB::transaction(function () use ($task, $user, $employeeId): void {
                TaskAssignment::create(['task_id' => $task->id, 'employee_id' => $employeeId]);

                $employee = Employee::with('user')->find($employeeId);

                $employee?->user?->notify(new TaskAssigned($task));

                $this->recordUpdate($task, $user, 'assignee_added', ['employee_id' => $employeeId]);
            });

            $count++;
        }

        return $count;
    }

    /**
     * Comment on a task. The creator or any assignee may do this.
     */
    public function comment(Task $task, User $user, string $body): void
    {
        if (! $this->canActOn($task, $user)) {
            throw new AuthorizationException(
                'You cannot comment on this task.'
            );
        }

        DB::transaction(function () use ($task, $user, $body): void {
            $task->comments()->create(['user_id' => $user->id, 'comment' => $body]);

            $this->recordUpdate($task, $user, 'comment_added', [
                'comment_preview' => Str::limit($body, 100),
            ]);
        });
    }

    /**
     * Delete a task. Only its creator may do this.
     */
    public function delete(Task $task, User $user): void
    {
        $this->assertCreator($task, $user, 'You can only delete tasks you created.');

        $task->delete();
    }

    /**
     * Whether the user may change status or comment on this task.
     */
    public function canActOn(Task $task, User $user): bool
    {
        if ($task->created_by === $user->id) {
            return true;
        }

        $employeeId = TaskPermission::employeeIdFor($user);

        return $employeeId !== null
            && $task->employees->pluck('id')->contains($employeeId);
    }

    /**
     * Tasks a user can see: the ones they raised plus the ones they are assigned
     * to, without the overlap appearing twice.
     */
    public function visibleTo(User $user, array $filters = [])
    {
        return $this->visibleQuery($user)
            ->filter($filters)
            ->orderByRaw($this->statusOrderSql())
            ->orderBy('deadline');
    }

    /**
     * The same tasks as visibleTo(), but split the way the legacy index page
     * lays them out: the ones you raised, and the ones other people assigned to
     * you. Assigned tasks you raised yourself are not repeated in the second list.
     *
     * @return array{created: Builder, assigned: Builder}
     */
    public function splitByRole(User $user, array $filters = []): array
    {
        $employeeId = TaskPermission::employeeIdFor($user);

        $created = Task::query()
            ->with(['employees', 'creator'])
            ->where('created_by', $user->id)
            ->filter($filters);

        $assigned = Task::query()
            ->with(['employees', 'creator'])
            ->where('created_by', '!=', $user->id)
            ->filter($filters)
            // Without an employee record there is no assignment that could match,
            // so return nothing rather than every task other people raised.
            ->whereHas(
                'employees',
                fn ($sub) => $sub->where('employees.id', $employeeId ?? 0)
            );

        return ['created' => $created, 'assigned' => $assigned];
    }

    /**
     * Headline counts for the dashboard.
     *
     * Scoped to what the user can see rather than to the whole table. The old
     * page counted every task in the database, which both leaked totals across
     * departments and disagreed with the lists printed beside it.
     *
     * @return array<string, int>
     */
    public function statsFor(User $user): array
    {
        return [
            'total' => $this->visibleQuery($user)->count(),
            'pending' => $this->visibleQuery($user)->where('status', 'pending')->count(),
            'in_progress' => $this->visibleQuery($user)->where('status', 'in_progress')->count(),
            'completed' => $this->visibleQuery($user)->where('status', 'completed')->count(),
            'overdue' => $this->visibleQuery($user)
                ->where('status', '!=', 'completed')
                ->whereDate('deadline', '<', Carbon::today())
                ->count(),
        ];
    }

    /**
     * The visibility rule on its own, with no filters or ordering applied.
     */
    protected function visibleQuery(User $user): Builder
    {
        $employeeId = TaskPermission::employeeIdFor($user);

        return Task::query()
            ->with(['employees', 'creator'])
            ->where(function (Builder $query) use ($user, $employeeId): void {
                $query->where('created_by', $user->id);

                if ($employeeId !== null) {
                    $query->orWhereHas(
                        'employees',
                        fn ($q) => $q->where('employees.id', $employeeId)
                    );
                }
            });
    }

    /**
     * Only staff who can assign may hand a task to someone else; everyone else
     * gets the task assigned to themselves when they leave it blank.
     *
     * @return array<int, int>
     */
    protected function resolveAssignees(User $user, mixed $requested, bool $canAssign, ?int $employeeId): array
    {
        if ($canAssign && ! empty($requested)) {
            return collect($requested)->filter()->unique()->values()->all();
        }

        return $employeeId !== null ? [$employeeId] : [];
    }

    /**
     * @param  array<int, int>  $employeeIds
     */
    protected function syncAssignees(Task $task, array $employeeIds, User $user, bool $notify = false): void
    {
        $task->assignees()->delete();

        foreach ($employeeIds as $employeeId) {
            TaskAssignment::create(['task_id' => $task->id, 'employee_id' => $employeeId]);
        }

        $task->load('employees.user');

        if ($employeeIds === []) {
            return;
        }

        $this->recordUpdate($task, $user, 'assignees_changed', [
            'assigned' => $task->employees->pluck('name')->toArray(),
        ]);

        if (! $notify) {
            return;
        }

        foreach ($task->employees as $assigned) {
            // A person can be an employee without a login, so there may be
            // nobody to notify.
            $assigned->user?->notify(new TaskAssigned($task));
        }
    }

    protected function applyStatus(Task $task, User $user, string $status, string $action): Task
    {
        $oldStatus = $task->status;

        DB::transaction(function () use ($task, $user, $status, $oldStatus, $action): void {
            $task->update([
                'status' => $status,
                'completion_date' => $this->completionDateFor($task, $status),
            ]);

            $this->recordUpdate($task, $user, $action, ['from' => $oldStatus, 'to' => $status]);

            if ($status === 'completed' && $oldStatus !== 'completed') {
                $this->recurrence->createNextOccurrence($task);
            }
        });

        return $task;
    }

    /**
     * Completed tasks are dated today; reopening clears the date, but pushing
     * back into progress leaves it alone so the original completion is not lost.
     */
    protected function completionDateFor(Task $task, string $status): ?Carbon
    {
        return match ($status) {
            'completed' => Carbon::today(),
            'pending' => null,
            default => $task->completion_date,
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function diff(Task $task, array $data, bool $isRecurring): array
    {
        $changes = [];

        foreach (['description', 'priority'] as $field) {
            if ($task->{$field} !== $data[$field]) {
                $changes[$field] = ['from' => $task->{$field}, 'to' => $data[$field]];
            }
        }

        if ($task->deadline?->toDateString() !== $data['deadline']) {
            $changes['deadline'] = [
                'from' => $task->deadline?->toDateString(),
                'to' => $data['deadline'],
            ];
        }

        if ((bool) $task->is_recurring !== $isRecurring) {
            $changes['is_recurring'] = ['from' => $task->is_recurring, 'to' => $isRecurring];
        }

        if ($isRecurring) {
            if ($task->recurrence_type !== ($data['recurrence_type'] ?? null)) {
                $changes['recurrence_type'] = [
                    'from' => $task->recurrence_type,
                    'to' => $data['recurrence_type'] ?? null,
                ];
            }

            if ($task->recurrence_end_date?->toDateString() !== ($data['recurrence_end_date'] ?? null)) {
                $changes['recurrence_end_date'] = [
                    'from' => $task->recurrence_end_date?->toDateString(),
                    'to' => $data['recurrence_end_date'] ?? null,
                ];
            }
        }

        return $changes;
    }

    /**
     * @param  array<int, int>  $employeeIds
     */
    protected function assigneeDiffers(Task $task, array $employeeIds): bool
    {
        $current = $task->employees->pluck('id')->sort()->values()->all();
        $incoming = collect($employeeIds)->sort()->values()->all();

        return $current !== $incoming;
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    protected function recordUpdate(Task $task, User $user, string $action, array $changes): void
    {
        TaskUpdate::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'action' => $action,
            'changes' => $changes,
        ]);
    }

    protected function assertCreator(Task $task, User $user, string $message): void
    {
        if ($task->created_by !== $user->id) {
            throw new AuthorizationException($message);
        }
    }

    /**
     * Keep the kanban columns in workflow order: pending, in progress, then
     * completed. Written as a CASE so it does not depend on MySQL's FIELD().
     */
    protected function statusOrderSql(): string
    {
        return "CASE status WHEN 'pending' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'completed' THEN 3 ELSE 4 END";
    }
}
