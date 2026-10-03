<?php

namespace Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Staff\Models\Employee;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskService;
use Modules\Tasks\Support\TaskPermission;

/**
 * The original task pages.
 *
 * Every rule that used to live here now lives in TaskService, so the Filament
 * resource and these pages cannot drift apart. The routes and Blade views stay
 * because the kanban board, the print/PDF entry points and the audit history all
 * still point at them, and because removing them would break anyone with a
 * bookmarked task URL.
 */
class TasksController extends Controller
{
    public function __construct(
        protected TaskService $tasks
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        $filters = $request->only(['status', 'priority', 'assignee_id', 'search', 'date_from', 'date_to']);
        $viewMode = $request->get('view', 'list');

        $lists = $this->tasks->splitByRole($user, $filters);

        if ($viewMode === 'kanban') {
            $createdTasks = (clone $lists['created'])->orderBy('deadline')->get();
            $assignedTasks = (clone $lists['assigned'])->orderBy('deadline')->get();
        } else {
            $createdTasks = (clone $lists['created'])
                ->orderByRaw($this->statusOrderSql())
                ->orderBy('deadline')->paginate(20)->withQueryString();
            $assignedTasks = (clone $lists['assigned'])
                ->orderByRaw($this->statusOrderSql())
                ->orderBy('deadline')->paginate(20)->withQueryString();
        }

        $stats = $this->tasks->statsFor($user);

        $employees = Employee::whereNull('end_date')->get();
        $canAssign = $user->can(TaskPermission::ASSIGN);
        $currentAssigneeId = $employee?->id;

        return view('tasks::index', compact(
            'createdTasks', 'assignedTasks', 'employees', 'stats', 'filters',
            'viewMode', 'canAssign', 'currentAssigneeId'
        ));
    }

    public function create()
    {
        $user = Auth::user();
        $canAssign = $user->can(TaskPermission::ASSIGN);
        $employees = $canAssign
            ? Employee::whereNull('end_date')->get()
            : collect();

        return view('tasks::create', compact('canAssign', 'employees'));
    }

    public function store(Request $request)
    {
        $canAssign = $request->user()->can(TaskPermission::ASSIGN);

        $data = $request->validate([
            'description' => 'required|string|max:5000',
            'priority' => 'required|in:high,medium,low',
            'deadline' => 'required|date|after_or_equal:'.Carbon::today()->toDateString(),
            'assignees' => $canAssign ? 'nullable|array' : 'nullable',
            'assignees.*' => $canAssign ? 'exists:employees,id' : '',
            'is_recurring' => 'nullable|boolean',
            'recurrence_type' => 'required_if:is_recurring,1|nullable|in:'.implode(',', Task::RECURRENCE_TYPES),
            'recurrence_end_date' => 'nullable|date|after_or_equal:deadline',
        ]);

        $this->tasks->create($request->user(), $data);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function show($id)
    {
        $task = Task::with('employees', 'creator', 'updates.user', 'comments.user', 'parentTask', 'childTasks')->findOrFail($id);

        $isCreator = $task->created_by === Auth::id();
        $isAssignee = $this->tasks->canActOn($task, Auth::user());

        return view('tasks::show', compact('task', 'isCreator', 'isAssignee'));
    }

    public function edit($id)
    {
        $task = Task::with('employees')->findOrFail($id);

        if ($task->created_by !== Auth::id()) {
            return redirect()->route('tasks.index')->with('error', 'You can only edit tasks you created.');
        }

        $user = Auth::user();
        $canAssign = $user->can(TaskPermission::ASSIGN);
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

        $canAssign = $request->user()->can(TaskPermission::ASSIGN);

        $data = $request->validate([
            'description' => 'required|string|max:5000',
            'priority' => 'required|in:high,medium,low',
            'deadline' => 'required|date',
            'assignees' => $canAssign ? 'nullable|array' : 'nullable',
            'assignees.*' => $canAssign ? 'exists:employees,id' : '',
            'is_recurring' => 'nullable|boolean',
            'recurrence_type' => 'required_if:is_recurring,1|nullable|in:'.implode(',', Task::RECURRENCE_TYPES),
            'recurrence_end_date' => 'nullable|date',
        ]);

        $this->tasks->update($task, $request->user(), $data);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function toggleComplete($id)
    {
        $task = Task::findOrFail($id);

        if ($task->created_by !== Auth::id()) {
            return redirect()->route('tasks.index')->with('error', 'Only the creator can change task status.');
        }

        $task = $this->tasks->toggleComplete($task, Auth::user());

        $label = match ($task->status) {
            'in_progress' => 'Task is now in progress.',
            'completed' => 'Task marked complete.',
            'pending' => 'Task reset to pending.',
            default => 'Task status updated.',
        };

        return redirect()->back()->with('success', $label);
    }

    public function setStatus(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $request->validate(['status' => 'required|in:pending,in_progress,completed']);

        try {
            $task = $this->tasks->changeStatus($task, $request->user(), $request->status);
        } catch (AuthorizationException) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'You cannot change the status of this task.'], 403)
                : redirect()->route('tasks.index')->with('error', 'You cannot change the status of this task.');
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

        $data = $request->validate(['comment' => 'required|string|max:2000']);

        try {
            $this->tasks->comment($task, $request->user(), $data['comment']);
        } catch (AuthorizationException) {
            return redirect()->back()->with('error', 'You cannot comment on this task.');
        }

        return redirect()->back()->with('success', 'Comment added.');
    }

    public function bulkStatus(Request $request)
    {
        $data = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $count = $this->tasks->bulkChangeStatus($request->user(), $data['task_ids'], $data['status']);

        return redirect()->route('tasks.index')->with('success', "{$count} task(s) updated to {$data['status']}.");
    }

    public function bulkAssign(Request $request)
    {
        $data = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'employee_id' => 'required|exists:employees,id',
        ]);

        $count = $this->tasks->bulkAssign($request->user(), $data['task_ids'], (int) $data['employee_id']);

        return redirect()->route('tasks.index')->with('success', "{$count} task(s) assigned.");
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        if ($task->created_by !== Auth::id()) {
            return redirect()->route('tasks.index')->with('error', 'You can only delete tasks you created.');
        }

        $this->tasks->delete($task, Auth::user());

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    protected function statusOrderSql(): string
    {
        return "CASE status WHEN 'pending' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'completed' THEN 3 ELSE 4 END";
    }
}
