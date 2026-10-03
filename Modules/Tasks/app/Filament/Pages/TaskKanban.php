<?php

namespace Modules\Tasks\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Modules\Staff\Models\Employee;
use Modules\Tasks\Filament\Resources\Tasks\TaskResource;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskService;
use UnitEnum;

/**
 * The task board, in the admin panel.
 *
 * This replaces the old Blade kanban. The columns and the drag behaviour are
 * the same, but every status change now goes through TaskService, so dropping a
 * card still stamps the completion date, writes the audit entry and creates the
 * next occurrence of a repeating task.
 */
class TaskKanban extends Page
{
    protected string $view = 'tasks::filament.pages.kanban';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static ?string $navigationLabel = 'Task Board';

    protected static ?string $title = 'Task Board';

    protected static ?string $slug = 'tasks/board';

    protected static ?int $navigationSort = 3;

    protected static UnitEnum|string|null $navigationGroup = 'Operations';

    public ?string $search = null;

    public ?string $assignee = null;

    /**
     * The columns, in workflow order.
     *
     * @return array<string, array{label: string, color: string}>
     */
    public function columns(): array
    {
        return [
            'pending' => ['label' => 'Pending', 'color' => 'warning'],
            'in_progress' => ['label' => 'In progress', 'color' => 'info'],
            'completed' => ['label' => 'Completed', 'color' => 'success'],
        ];
    }

    /**
     * The cards for one column, scoped to what this user is allowed to see.
     *
     * @return Collection<int, Task>
     */
    public function tasksFor(string $status): Collection
    {
        return app(TaskService::class)
            ->visibleTo(auth()->user(), array_filter([
                'status' => $status,
                'search' => $this->search,
                'assignee_id' => $this->assignee ?: null,
            ]))
            ->get();
    }

    public function countFor(string $status): int
    {
        return $this->tasksFor($status)->count();
    }

    /**
     * Staff a card can be filtered down to. Only active employees, matching the
     * form and the old filter.
     *
     * @return Collection<int, Employee>
     */
    public function employeeOptions(): Collection
    {
        return Employee::whereNull('end_date')->orderBy('name')->get();
    }

    /**
     * Move a card. Called by the drop handler and by the per-card buttons.
     */
    public function setStatus(int $taskId, string $status): void
    {
        if (! in_array($status, Task::STATUSES, true)) {
            return;
        }

        $task = app(TaskService::class)->visibleTo(auth()->user())->find($taskId);

        if (! $task) {
            return;
        }

        try {
            app(TaskService::class)->changeStatus($task, auth()->user(), $status);
        } catch (AuthorizationException) {
            Notification::make()
                ->danger()
                ->title('Not your task')
                ->body('You can only move tasks you raised or were assigned.')
                ->send();

            return;
        }

        $label = str_replace('_', ' ', $status);

        Notification::make()
            ->success()
            ->title("Moved to {$label}")
            ->send();
    }

    public function start(int $taskId): void
    {
        $this->setStatus($taskId, 'in_progress');
    }

    public function complete(int $taskId): void
    {
        $this->setStatus($taskId, 'completed');
    }

    public function reopen(int $taskId): void
    {
        $this->setStatus($taskId, 'pending');
    }

    public function canMove(int $taskId): bool
    {
        $task = app(TaskService::class)->visibleTo(auth()->user())->find($taskId);

        return $task !== null
            && app(TaskService::class)->canActOn($task, auth()->user());
    }

    public function viewUrl(int $taskId): string
    {
        return TaskResource::getUrl('view', ['record' => $taskId]);
    }

    public function deadlineLabel(Task $task): string
    {
        if (! $task->deadline) {
            return 'No deadline';
        }

        $label = $task->deadline->format('d M Y');

        if ($task->status !== 'completed' && $task->deadline->isBefore(Carbon::today())) {
            return $label.' · overdue';
        }

        return $label;
    }

    public function isOverdue(Task $task): bool
    {
        return $task->isOverdue();
    }

    public static function canAccess(): bool
    {
        return TaskResource::canViewAny();
    }
}
