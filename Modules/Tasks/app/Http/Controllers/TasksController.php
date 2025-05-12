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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::with('employees', 'creator')->get();
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
        $request->validate([
            'date' => 'required|date',
            'description' => 'required',
            'priority' => 'required|in:high,medium,low',
            'deadline' => 'required|date',
            'assignees' => 'required|array',
            'assignees.*' => 'exists:employees,id',
        ]);
        // Generate task number: TASK-DDMMYY-N
        $today = Carbon::today();
        $datePart = $today->format('dmy'); // e.g., 120525 for May 12, 2025
        $taskCount = Task::whereDate('created_at', $today)->count() + 1; // Increment for today’s tasks
        $taskNumber = sprintf('TASK-%s-%d', $datePart, $taskCount);

        $task = Task::create([
            'task_number' => $taskNumber,
            'date' => $request->date,
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
        }

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $task = Task::with('employees', 'creator')->findOrFail($id);
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
        $request->validate([
            'is_completed' => 'required|boolean',
            'completion_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'non_completion_reason' => 'nullable|string',
        ]);

        $task = Task::findOrFail($id);
        $task->update([
            'is_completed' => $request->is_completed,
            'completion_date' => $request->is_completed ? $request->completion_date : null,
            'notes' => $request->notes,
            'non_completion_reason' => $request->non_completion_reason,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    /**
     * Evaluate the specified task (for General Manager).
     */
    public function evaluate(Request $request, $id)
    {
        $request->validate([
            'is_successful' => 'required|boolean',
            'meets_expectations' => 'required|boolean',
            'gm_notes' => 'nullable|string',
        ]);

        $task = Task::findOrFail($id);
        $task->update([
            'is_successful' => $request->is_successful,
            'meets_expectations' => $request->meets_expectations,
            'gm_notes' => $request->gm_notes,
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
