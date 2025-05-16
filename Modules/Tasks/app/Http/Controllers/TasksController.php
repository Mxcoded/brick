<?php

namespace Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAssignment;
use Modules\Tasks\Models\TaskUpdate;
use Modules\Staff\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskAssigned;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) { // Replace with your role check
            $tasks = Task::with('employees', 'creator')->get();
        } else {
            $employee = Employee::where('user_id', $user->id)->first();
            $tasks = Task::whereHas('employees', function ($query) use ($employee) {
                $query->where('employee_id', $employee->id);
            })->with('employees', 'creator')->get();
        }
        return view('tasks::index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::whereIn('position', ['Manager', 'Supervisor'])->get();
        return view('tasks::create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $creationDate = Carbon::today()->toDateString();

        $request->validate([
            'description' => 'required',
            'priority' => 'required|in:high,medium,low',
            'deadline' => 'required|date|after_or_equal:' . $creationDate,
            'assignees' => 'required|array',
            'assignees.*' => 'exists:employees,id',
        ]);

        // Generate task number: TASK-DDMMYY-N
        $today = Carbon::today();
        $datePart = $today->format('dmy');
        $taskCount = Task::whereDate('created_at', $today)->count() + 1;
        $taskNumber = sprintf('TASK-%s-%d', $datePart, $taskCount);

        $task = Task::create([
            'task_number' => $taskNumber,
            'date' => $creationDate,
            'created_by' => Auth::id(),
            'description' => $request->description,
            'priority' => $request->priority,
            'deadline' => $request->deadline,
        ]);

        foreach ($request->assignees as $employeeId) {
            TaskAssignment::create([
                'task_id' => $task->id,
                'employee_id' => $employeeId,
            ]);
            // Notify the assignee
            $employee = Employee::find($employeeId);
            if ($employee->user) {
                Notification::send($employee->user, new TaskAssigned($task));
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $task = Task::with('employees', 'creator', 'updates.user')->findOrFail($id);
        return view('tasks::show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $task = Task::with('employees')->findOrFail($id);
        return view('tasks::edit', compact('task'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        // Prevent updates if task is evaluated as successful
        if ($task->is_successful) {
            return redirect()->route('tasks.index')->with('error', 'This task has been evaluated as successful and cannot be updated.');
        }

        $request->validate([
            'is_completed' => 'required|boolean',
            'completion_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'non_completion_reason' => 'nullable|string',
        ]);

        $changes = [
            'is_completed' => $request->is_completed,
            'completion_date' => $request->is_completed ? $request->completion_date : null,
            'notes' => $request->notes,
            'non_completion_reason' => $request->non_completion_reason,
        ];

        $task->update($changes);

        // Log the update
        TaskUpdate::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'action' => 'updated_completion',
            'changes' => $changes,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    /**
     * Evaluate the specified task (for General Manager).
     */
    public function evaluate(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $request->validate([
            'is_successful' => 'required|boolean',
            'meets_expectations' => 'required|boolean',
            'gm_notes' => 'nullable|string',
        ]);

        $changes = [
            'is_successful' => $request->is_successful,
            'meets_expectations' => $request->meets_expectations,
            'gm_notes' => $request->gm_notes,
        ];

        $task->update($changes);

        // Log the evaluation
        TaskUpdate::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'action' => 'evaluated',
            'changes' => $changes,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task evaluated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}
