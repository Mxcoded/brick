<?php

namespace Modules\Staff\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\EmployeeSkill;
use Modules\Staff\Models\PerformanceReview;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $department = $request->department;
        $period = $request->period;
        $status = $request->status;

        $reviews = PerformanceReview::with(['employee', 'reviewer'])
            ->when($department, fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('department', $department)))
            ->when($period, fn ($q) => $q->where('review_period', $period))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        $departments = Employee::where('status', 'approved')
            ->whereNotNull('department')
            ->distinct('department')
            ->pluck('department')
            ->sort();

        $summary = (object) [
            'total' => PerformanceReview::count(),
            'submitted' => PerformanceReview::where('status', 'submitted')->count(),
            'draft' => PerformanceReview::where('status', 'draft')->count(),
            'avg_score' => round(PerformanceReview::where('status', 'submitted')->avg('overall_score') ?? 0, 1),
        ];

        return view('staff::performance.index', compact('reviews', 'departments', 'department', 'period', 'status', 'summary'));
    }

    public function create()
    {
        $employees = Employee::where('status', 'approved')->orderBy('name')->get();

        return view('staff::performance.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'review_date' => 'required|date',
            'review_period' => 'required|in:quarterly,annual,probation',
            'rating_punctuality' => 'required|integer|min:1|max:5',
            'rating_teamwork' => 'required|integer|min:1|max:5',
            'rating_communication' => 'required|integer|min:1|max:5',
            'rating_quality' => 'required|integer|min:1|max:5',
            'rating_initiative' => 'required|integer|min:1|max:5',
            'strengths' => 'nullable|string|max:5000',
            'areas_for_improvement' => 'nullable|string|max:5000',
            'comments' => 'nullable|string|max:5000',
        ]);

        $avg = collect([
            $validated['rating_punctuality'],
            $validated['rating_teamwork'],
            $validated['rating_communication'],
            $validated['rating_quality'],
            $validated['rating_initiative'],
        ])->avg();

        $validated['overall_score'] = round($avg, 2);
        $validated['reviewer_id'] = auth()->user()?->employee?->id;
        $validated['status'] = 'submitted';

        PerformanceReview::create($validated);

        return redirect()->route('staff.performance.index')
            ->with('success', 'Performance review created successfully.');
    }

    public function show(PerformanceReview $performanceReview)
    {
        $performanceReview->load(['employee', 'reviewer']);

        return view('staff::performance.show', compact('performanceReview'));
    }

    public function edit(PerformanceReview $performanceReview)
    {
        $employees = Employee::where('status', 'approved')->orderBy('name')->get();

        return view('staff::performance.edit', compact('performanceReview', 'employees'));
    }

    public function update(Request $request, PerformanceReview $performanceReview)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'review_date' => 'required|date',
            'review_period' => 'required|in:quarterly,annual,probation',
            'rating_punctuality' => 'required|integer|min:1|max:5',
            'rating_teamwork' => 'required|integer|min:1|max:5',
            'rating_communication' => 'required|integer|min:1|max:5',
            'rating_quality' => 'required|integer|min:1|max:5',
            'rating_initiative' => 'required|integer|min:1|max:5',
            'strengths' => 'nullable|string|max:5000',
            'areas_for_improvement' => 'nullable|string|max:5000',
            'comments' => 'nullable|string|max:5000',
        ]);

        $avg = collect([
            $validated['rating_punctuality'],
            $validated['rating_teamwork'],
            $validated['rating_communication'],
            $validated['rating_quality'],
            $validated['rating_initiative'],
        ])->avg();

        $validated['overall_score'] = round($avg, 2);
        $performanceReview->update($validated);

        return redirect()->route('staff.performance.index')
            ->with('success', 'Performance review updated successfully.');
    }

    public function skillsIndex(Request $request)
    {
        $category = $request->category;
        $department = $request->department;

        $skills = EmployeeSkill::with('employee')
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($department, fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('department', $department)))
            ->get()
            ->groupBy('category');

        $departments = Employee::where('status', 'approved')
            ->whereNotNull('department')
            ->distinct('department')
            ->pluck('department')
            ->sort();

        return view('staff::performance.skills', compact('skills', 'category', 'department', 'departments'));
    }

    public function skillsCreate()
    {
        $employees = Employee::where('status', 'approved')->orderBy('name')->get();

        return view('staff::performance.skills-create', compact('employees'));
    }

    public function skillsStore(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'skill_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employee_skills')->where(fn ($q) => $q->where('employee_id', $request->employee_id)),
            ],
            'category' => 'required|in:technical,soft,language,certification,other',
            'proficiency_level' => 'required|in:beginner,intermediate,advanced,expert',
            'years_experience' => 'nullable|numeric|min:0|max:50',
            'last_used_date' => 'nullable|date',
            'is_certified' => 'nullable|boolean',
            'notes' => 'nullable|string|max:5000',
        ]);

        $validated['is_certified'] = $request->boolean('is_certified');

        EmployeeSkill::create($validated);

        return redirect()->route('staff.performance.skills')
            ->with('success', 'Skill added successfully.');
    }

    public function skillsDestroy(EmployeeSkill $employeeSkill)
    {
        $employeeSkill->delete();

        return back()->with('success', 'Skill removed successfully.');
    }
}
