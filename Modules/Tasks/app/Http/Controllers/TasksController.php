<?php

namespace Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAssignment;
use Modules\Staff\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TasksController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        $createdTasks = Task::with('employees')
            ->where('created_by', $user->id)
            ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'completed')")
            ->orderBy('deadline', 'asc')
            ->paginate(10);

        $assignedTaskIds = [];
        if ($employee) {
            $assignedTaskIds = TaskAssignment::where('employee_id', $employee->id)
                ->pluck('task_id')
                ->toArray();
        }

        $assignedTasks = Task::with('creator', 'employees')
            ->whereIn('id', $assignedTaskIds)
            ->where('created_by', '!=', $user->id)
            ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'completed')")
            ->orderBy('deadline', 'asc')
            ->paginate(10);

        $employees = Employee::whereNull('end_date')->get();

        return view('tasks::index', compact('createdTasks', 'assignedTasks', 'employees'));
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
            'deadline' => 'required|date|after_or_equal:' . Carbon::today()->toDateString(),
            'assignees' => $canAssign ? 'nullable|array' : 'nullable',
            'assignees.*' => $canAssign ? 'exists:employees,id' : '',
        ]);

        $today = Carbon::today();
        $datePart = $today->format('dmy');
        $taskCount = Task::whereDate('created_at', $today)->count() + 1;
        $taskNumber = sprintf('TASK-%s-%d', $datePart, $taskCount);

        $task = Task::create([
            'task_number' => $taskNumber,
            'date' => $today->toDateString(),
            'created_by' => $user->id,
            'description' => $request->description,
            'priority' => $request->priority,
            'deadline' => $request->deadline,
        ]);

        if ($canAssign && !empty($request->assignees)) {
            foreach ($request->assignees as $employeeId) {
                TaskAssignment::create([
                    'task_id' => $task->id,
                    'employee_id' => $employeeId,
                ]);
            }
        } elseif ($employee) {
            TaskAssignment::create([
                'task_id' => $task->id,
                'employee_id' => $employee->id,
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function show($id)
    {
        $task = Task::with('employees', 'creator')->findOrFail($id);

        $user = Auth::user();
        $isCreator = $task->created_by === $user->id;

        return view('tasks::show', compact('task', 'isCreator'));
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
        ]);

        $task->update([
            'description' => $request->description,
            'priority' => $request->priority,
            'deadline' => $request->deadline,
        ]);

        if ($canAssign && $request->has('assignees')) {
            $task->assignees()->delete();
            foreach ($request->assignees as $employeeId) {
                TaskAssignment::create([
                    'task_id' => $task->id,
                    'employee_id' => $employeeId,
                ]);
            }
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
            'pending'     => 'in_progress',
            'in_progress' => 'completed',
            'completed'   => 'pending',
        };

        $task->update([
            'status' => $next,
            'completion_date' => $next === 'completed' ? Carbon::today() : ($next === 'pending' ? null : $task->completion_date),
        ]);

        $label = match ($next) {
            'in_progress' => 'Task is now in progress.',
            'completed'   => 'Task marked complete.',
            'pending'     => 'Task reset to pending.',
        };

        return redirect()->back()->with('success', $label);
    }

    public function setStatus(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        if ($task->created_by !== Auth::id()) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Only the creator can change task status.'], 403)
                : redirect()->route('tasks.index')->with('error', 'Only the creator can change task status.');
        }

        $request->validate(['status' => 'required|in:pending,in_progress,completed']);

        $task->update([
            'status' => $request->status,
            'completion_date' => $request->status === 'completed' ? Carbon::today() : ($request->status === 'pending' ? null : $task->completion_date),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $task->status,
                'completion_date' => $task->completion_date?->toDateString(),
            ]);
        }

        return redirect()->back()->with('success', 'Task status updated.');
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
