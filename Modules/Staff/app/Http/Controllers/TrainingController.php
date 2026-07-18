<?php

namespace Modules\Staff\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\TrainingRecord;

class TrainingController extends Controller
{
    public function index(Request $request)
    {
        $department = $request->department;
        $status = $request->status;
        $type = $request->type;

        $records = TrainingRecord::with('employee')
            ->when($department, fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('department', $department)))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($type, fn ($q) => $q->where('training_type', $type))
            ->latest()
            ->paginate(20);

        $departments = Employee::where('status', 'approved')
            ->whereNotNull('department')
            ->distinct('department')
            ->pluck('department')
            ->sort();

        $summary = (object) [
            'total' => TrainingRecord::count(),
            'completed' => TrainingRecord::where('status', 'completed')->count(),
            'in_progress' => TrainingRecord::where('status', 'in_progress')->count(),
            'expiring_soon' => TrainingRecord::expiringSoon()->count(),
            'expired' => TrainingRecord::expired()->count(),
        ];

        return view('staff::training.index', compact('records', 'departments', 'department', 'status', 'type', 'summary'));
    }

    public function create()
    {
        $employees = Employee::where('status', 'approved')->orderBy('name')->get();

        return view('staff::training.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'course_name' => 'required|string|max:255',
            'provider' => 'nullable|string|max:255',
            'training_type' => 'required|in:internal,external,certification',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'duration_hours' => 'nullable|numeric|min:0',
            'status' => 'required|in:enrolled,in_progress,completed,cancelled',
            'certification_name' => 'nullable|string|max:255',
            'certification_url' => 'nullable|url|max:1024',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:5000',
        ]);

        TrainingRecord::create($validated);

        return redirect()->route('staff.training.index')
            ->with('success', 'Training record created successfully.');
    }

    public function edit(TrainingRecord $trainingRecord)
    {
        $trainingRecord->load('employee');
        $employees = Employee::where('status', 'approved')->orderBy('name')->get();

        return view('staff::training.edit', compact('trainingRecord', 'employees'));
    }

    public function update(Request $request, TrainingRecord $trainingRecord)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'course_name' => 'required|string|max:255',
            'provider' => 'nullable|string|max:255',
            'training_type' => 'required|in:internal,external,certification',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'duration_hours' => 'nullable|numeric|min:0',
            'status' => 'required|in:enrolled,in_progress,completed,cancelled',
            'certification_name' => 'nullable|string|max:255',
            'certification_url' => 'nullable|url|max:1024',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:5000',
        ]);

        $trainingRecord->update($validated);

        return redirect()->route('staff.training.index')
            ->with('success', 'Training record updated successfully.');
    }

    public function destroy(TrainingRecord $trainingRecord)
    {
        $trainingRecord->delete();

        return back()->with('success', 'Training record deleted successfully.');
    }
}
