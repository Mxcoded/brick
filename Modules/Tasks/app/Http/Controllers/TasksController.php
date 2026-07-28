<?php

namespace Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\TaskAssigned;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Staff\Models\Employee;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAssignment;
use Modules\Tasks\Models\TaskUpdate;
use Modules\Tasks\Services\TaskRecurrenceService;

class TasksController extends Controller
{
    public function __construct(
        protected TaskRecurrenceService $recurrenceService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        $filters = $request->only(['status', 'priority', 'assignee_id', 'search', 'date_from', 'date_to']);
        $viewMode = $request->get('view', 'list');

        $assignedTaskIds = [];
        if ($employee) {
            $assignedTaskIds = TaskAssignment::where('employee_id', $employee->id)
                ->pluck('task_id')->toArray();
        }

        $createdTasksQuery = Task::with('employees', 'creator')
            ->where('created_by', $user->id)
            ->filter($filters);

        $assignedTasksQuery = Task::with('employees', 'creator')
            ->whereIn('id', $assignedTaskIds)
            ->where('created_by', '!=', $user->id)
            ->filter($filters);

        if ($viewMode === 'kanban') {
            $createdTasks = (clone $createdTasksQuery)->orderBy('deadline')->get();
            $assignedTasks = (clone $assignedTasksQuery)->orderBy('deadline')->get();
        } else {
            $createdTasks = (clone $createdTasksQuery)
                ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'completed')")
                ->orderBy('deadline')->paginate(20)->withQueryString();
            $assignedTasks = (clone $assignedTasksQuery)
                ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'completed')")
                ->orderBy('deadline')->paginate(20)->withQueryString();
        }

        $stats = [
            'total' => Task::count(),
            'pending' => Task::pending()->count(),
            'in_progress' => Task::inProgress()->count(),
            'completed' => Task::completed()->count(),
            'overdue' => Task::where('status', '!=', 'completed')->where('deadline', '<', Carbon::today())->count(),
        ];

        $employees = Employee::whereNull('end_date')->get();
        $canAssign = $user->can('tasks.assign');
        $currentAssigneeId = $employee?->id;

        return view('tasks::index', compact(
            'createdTasks', 'assignedTasks', 'employees', 'stats', 'filters',
            'viewMode', 'canAssign', 'currentAssigneeId'
        ));
    }

    public function create()
    {
        $user = Auth::user();
        $canAssign = $user->can('tasks.assign');
        $employees = $canAssign
            ? Employee::whereNull('end_date')->get()
            : collect();

        return view('tasks::create', compact('canAssign', 'employees'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $canAssign = $user->can('tasks.assign');
        $employee = Employee::where('user_id', $user->id)->first();

        $request->validate([
            'description' => 'required|string|max:5000',
            'priority' => 'required|in:high,medium,low',
            'deadline' => 'required|date|after_or_equal:'.Carbon::today()->toDateString(),
            'assignees' => $canAssign ? 'nullable|array' : 'nullable',
            'assignees.*' => $canAssign ? 'exists:employees,id' : '',
            'is_recurring' => 'nullable|boolean',
            'recurrence_type' => 'required_if:is_recurring,1|nullable|in:'.implode(',', Task::RECURRENCE_TYPES),
            'recurrence_end_date' => 'nullable|date|after_or_equal:deadline',
        ]);

        $today = Carbon::today();
        $datePart = $today->format('dmy');
        $taskCount = Task::whereDate('created_at', $today)->count() + 1;
        $taskNumber = sprintf('TASK-%s-%d', $datePart, $taskCount);

        $isRecurring = (bool) $request->input('is_recurring', false);

        $task = Task::create([
            'task_number' => $taskNumber,
            'date' => $today->toDateString(),
            'created_by' => $user->id,
            'description' => $request->description,
            'priority' => $request->priority,
            'deadline' => $request->deadline,
            'is_recurring' => $isRecurring,
            'recurrence_type' => $isRecurring ? $request->recurrence_type : null,
            'recurrence_end_date' => $isRecurring ? $request->recurrence_end_date : null,
        ]);

        TaskUpdate::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'action' => 'created',
            'changes' => [
                'description' => $request->description,
                'priority' => $request->priority,
                'deadline' => $request->deadline,
                'is_recurring' => $isRecurring,
            ],
        ]);

        $assignedEmployeeIds = [];
        if ($canAssign && ! empty($request->assignees)) {
            $assignedEmployeeIds = $request->assignees;
        } elseif ($employee) {
            $assignedEmployeeIds = [$employee->id];
        }

        foreach ($assignedEmployeeIds as $employeeId) {
            TaskAssignment::create(['task_id' => $task->id, 'employee_id' => $employeeId]);
        }

        $task->load('employees.user');
        foreach ($task->employees as $assigned) {
            if ($assigned->user) {
                $assigned->user->notify(new TaskAssigned($task));
            }
        }

        if (! empty($assignedEmployeeIds)) {
            TaskUpdate::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'action' => 'assignees_changed',
                'changes' => ['assigned' => $task->employees->pluck('name')->toArray()],
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function show($id)
    {
        $task = Task::with('employees', 'creator', 'updates.user', 'comments.user', 'parentTask', 'childTasks')->findOrFail($id);

        $user = Auth::user();
        $isCreator = $task->created_by === $user->id;
        $isAssignee = $task->employees->pluck('user_id')->contains($user->id);

        return view('tasks::show', compact('task', 'isCreator', 'isAssignee'));
    }

    public function edit($id)
    {
        $task = Task::with('employees')->findOrFail($id);

        if ($task->created_by !== Auth::id()) {
            return redirect()->route('tasks.index')->with('error', 'You can only edit tasks you created.');
        }

        $user = Auth::user();
        $canAssign = $user->can('tasks.assign');
        $employees = $canAssign
            ? Employee::whereNull('end_date')->get()
            : collect();

        return view('tasks::edit', compact('task', 'canAssign', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        if ($task->created_by !== Auth::id()) {
            return redirect()->route('tasks.index')->with('error', 'You can only update tasks you created.');
        }

        $user = Auth::user();
        $canAssign = $user->can('tasks.assign');

        $request->validate([
            'description' => 'required|string|max:5000',
            'priority' => 'required|in:high,medium,low',
            'deadline' => 'required|date',
            'assignees' => $canAssign ? 'nullable|array' : 'nullable',
            'assignees.*' => $canAssign ? 'exists:employees,id' : '',
            'is_recurring' => 'nullable|boolean',
            'recurrence_type' => 'required_if:is_recurring,1|nullable|in:'.implode(',', Task::RECURRENCE_TYPES),
            'recurrence_end_date' => 'nullable|date',
        ]);

        $changes = [];
        if ($task->description !== $request->description) {
            $changes['description'] = ['from' => $task->description, 'to' => $request->description];
        }
        if ($task->priority !== $request->priority) {
            $changes['priority'] = ['from' => $task->priority, 'to' => $request->priority];
        }
        if ($task->deadline->toDateString() !== $request->deadline) {
            $changes['deadline'] = ['from' => $task->deadline->toDateString(), 'to' => $request->deadline];
        }

        $isRecurring = (bool) $request->input('is_recurring', false);

        if ($task->is_recurring !== $isRecurring) {
            $changes['is_recurring'] = ['from' => $task->is_recurring, 'to' => $isRecurring];
        }
        if ($isRecurring && $task->recurrence_type !== $request->recurrence_type) {
            $changes['recurrence_type'] = ['from' => $task->recurrence_type, 'to' => $request->recurrence_type];
        }
        if ($isRecurring && $task->recurrence_end_date?->toDateString() !== $request->recurrence_end_date) {
            $changes['recurrence_end_date'] = [
                'from' => $task->recurrence_end_date?->toDateString(),
                'to' => $request->recurrence_end_date,
            ];
        }

        $task->update([
            'description' => $request->description,
            'priority' => $request->priority,
            'deadline' => $request->deadline,
            'is_recurring' => $isRecurring,
            'recurrence_type' => $isRecurring ? $request->recurrence_type : null,
            'recurrence_end_date' => $isRecurring ? $request->recurrence_end_date : null,
        ]);

        if ($canAssign && $request->has('assignees')) {
            $oldAssignees = $task->employees->pluck('id')->sort()->values()->toArray();
            $newAssignees = collect($request->assignees)->sort()->values()->toArray();
            if ($oldAssignees !== $newAssignees) {
                $changes['assignees'] = ['from' => $task->employees->pluck('name')->toArray()];
                $task->assignees()->delete();
                foreach ($request->assignees as $employeeId) {
                    TaskAssignment::create(['task_id' => $task->id, 'employee_id' => $employeeId]);
                }
                $task->load('employees.user');
                $changes['assignees']['to'] = $task->employees->pluck('name')->toArray();
                foreach ($task->employees as $assigned) {
                    if ($assigned->user) {
                        $assigned->user->notify(new TaskAssigned($task));
                    }
                }
            }
        }

        if (! empty($changes)) {
            TaskUpdate::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'action' => 'updated',
                'changes' => $changes,
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function toggleComplete($id)
    {
        $task = Task::findOrFail($id);

        if ($task->created_by !== Auth::id()) {
            return redirect()->route('tasks.index')->with('error', 'Only the creator can change task status.');
        }

        $next = match ($task->status) {
            'pending' => 'in_progress',
            'in_progress' => 'completed',
            'completed' => 'pending',
        };

        $oldStatus = $task->status;
        $task->update([
            'status' => $next,
            'completion_date' => $next === 'completed' ? Carbon::today() : ($next === 'pending' ? null : $task->completion_date),
        ]);

        TaskUpdate::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'action' => 'status_changed',
            'changes' => ['from' => $oldStatus, 'to' => $next],
        ]);

        if ($next === 'completed') {
            $this->recurrenceService->createNextOccurrence($task);
        }

        $label = match ($next) {
            'in_progress' => 'Task is now in progress.',
            'completed' => 'Task marked complete.',
            'pending' => 'Task reset to pending.',
        };

        return redirect()->back()->with('success', $label);
    }

    public function setStatus(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $user = Auth::user();
        $isCreator = $task->created_by === $user->id;
        $employee = Employee::where('user_id', $user->id)->first();
        $isAssignee = $employee && $task->employees->pluck('id')->contains($employee->id);

        if (! $isCreator && ! $isAssignee) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'You cannot change the status of this task.'], 403)
                : redirect()->route('tasks.index')->with('error', 'You cannot change the status of this task.');
        }

        $request->validate(['status' => 'required|in:pending,in_progress,completed']);

        $oldStatus = $task->status;
        $task->update([
            'status' => $request->status,
            'completion_date' => $request->status === 'completed' ? Carbon::today() : ($request->status === 'pending' ? null : $task->completion_date),
        ]);

        TaskUpdate::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'action' => 'status_changed',
            'changes' => ['from' => $oldStatus, 'to' => $request->status],
        ]);

        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            $this->recurrenceService->createNextOccurrence($task);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $task->status,
                'completion_date' => $task->completion_date?->toDateString(),
            ]);
        }

        return redirect()->back()->with('success', 'Task status updated.');
    }

    public function comment(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        $isAssignee = $employee && $task->employees->pluck('id')->contains($employee->id);
        $isCreator = $task->created_by === $user->id;

        if (! $isCreator && ! $isAssignee) {
            return redirect()->back()->with('error', 'You cannot comment on this task.');
        }

        $request->validate(['comment' => 'required|string|max:2000']);

        $task->comments()->create([
            'user_id' => $user->id,
            'comment' => $request->comment,
        ]);

        TaskUpdate::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'action' => 'comment_added',
            'changes' => ['comment_preview' => Str::limit($request->comment, 100)],
        ]);

        return redirect()->back()->with('success', 'Comment added.');
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $user = Auth::user();
        $count = 0;

        foreach ($request->task_ids as $id) {
            $task = Task::find($id);
            if (! $task || $task->created_by !== $user->id) {
                continue;
            }
            $oldStatus = $task->status;
            $task->update([
                'status' => $request->status,
                'completion_date' => $request->status === 'completed' ? Carbon::today() : ($request->status === 'pending' ? null : $task->completion_date),
            ]);
            TaskUpdate::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'action' => 'bulk_status_changed',
                'changes' => ['from' => $oldStatus, 'to' => $request->status],
            ]);

            if ($request->status === 'completed' && $oldStatus !== 'completed') {
                $this->recurrenceService->createNextOccurrence($task);
            }

            $count++;
        }

        return redirect()->route('tasks.index')->with('success', "{$count} task(s) updated to {$request->status}.");
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'employee_id' => 'required|exists:employees,id',
        ]);

        $user = Auth::user();
        $count = 0;

        foreach ($request->task_ids as $id) {
            $task = Task::find($id);
            if (! $task || $task->created_by !== $user->id) {
                continue;
            }
            $alreadyAssigned = $task->employees->pluck('id')->contains($request->employee_id);
            if (! $alreadyAssigned) {
                TaskAssignment::create(['task_id' => $task->id, 'employee_id' => $request->employee_id]);
                $task->load('employees.user');
                foreach ($task->employees as $assigned) {
                    if ($assigned->user && $assigned->id == $request->employee_id) {
                        $assigned->user->notify(new TaskAssigned($task));
                    }
                }
                TaskUpdate::create([
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'action' => 'assignee_added',
                    'changes' => ['employee_id' => $request->employee_id],
                ]);
                $count++;
            }
        }

        return redirect()->route('tasks.index')->with('success', "{$count} task(s) assigned.");
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        if ($task->created_by !== Auth::id()) {
            return redirect()->route('tasks.index')->with('error', 'You can only delete tasks you created.');
        }

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}
