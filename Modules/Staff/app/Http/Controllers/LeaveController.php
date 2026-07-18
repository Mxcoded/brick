<?php

namespace Modules\Staff\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Modules\Staff\Emails\LeaveRequestStatusUpdated;
use Modules\Staff\Emails\LeaveRequestSubmitted;
// Other 'use' statements...
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\LeaveBalance;
use Modules\Staff\Models\LeaveRequest;

class LeaveController extends Controller
{
    // Employee Leave Dashboard
    public function leaveIndex()
    {
        $user = Auth::user();
        $employee = $user->employee;
        if (! $employee) {
            return redirect()->back()->with('error', 'You do not have an employee profile.');
        }
        $leaveRequests = $employee->leaveRequests()->latest()->get();
        $leaveBalances = $employee->leaveBalances()->where('year', date('Y'))->get();

        // --- NEW LOGIC TO FETCH UPCOMING LEAVE ---
        $upcomingLeave = $employee->leaveRequests()
            ->where('status', 'approved')
            ->where('start_date', '>=', now()) // Find leaves starting today or in the future
            ->orderBy('start_date', 'asc')     // Get the soonest one
            ->first();

        return view('staff::leaves.index', compact(
            'employee',
            'leaveRequests',
            'leaveBalances',
            'upcomingLeave' // Pass the new variable to the view
        ));
    }

    // Leave Request Form
    public function leaveRequestForm()
    {
        $user = Auth::user();
        $employee = $user->employee;
        if (! $employee) {
            return redirect()->back()->with('error', 'You do not have an employee profile.');
        }
        $leaveBalances = $employee->leaveBalances()->where('year', date('Y'))->get();

        return view('staff::leaves.request', compact('employee', 'leaveBalances'));
    }

    public function cancelLeaveRequest($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        // Security Check 1: Ensure the request is still pending.
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'This leave request cannot be cancelled as it has already been processed.');
        }

        // Security Check 2: Ensure the authenticated user owns this request.
        if ($leaveRequest->employee_id !== Auth::user()->employee->id) {
            // This is a security measure to prevent users from cancelling others' requests.
            // In a real scenario, this check might trigger an alert.
            abort(403, 'Unauthorized action.');
        }

        // Update the status to 'cancelled'.
        $leaveRequest->status = 'cancelled';
        $leaveRequest->save();

        return back()->with('success', 'Your leave request has been successfully cancelled.');
    }

    /**
     * Allows an Admin to cancel any leave request (pending or approved).
     * If the request was approved, it returns the leave days to the employee's balance.
     *
     * @param  int  $id  The ID of the leave request.
     * @return RedirectResponse
     */
    public function adminCancelLeaveRequest($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        // Check if the leave was previously approved to recalculate the balance.
        if ($leaveRequest->status === 'approved') {
            $leaveBalance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                ->where('leave_type', $leaveRequest->leave_type)
                ->where('year', Carbon::parse($leaveRequest->start_date)->year)
                ->first();

            if ($leaveBalance) {
                // Add the days back to the employee's balance
                $leaveBalance->decrement('used_days', $leaveRequest->days_count);
            }
        }

        // Now, update the leave request status to 'cancelled'.
        $leaveRequest->status = 'cancelled';
        $leaveRequest->save();

        return back()->with('success', 'The leave request has been successfully cancelled.');
    }

    // Admin Leave Management
    public function leaveAdminIndex()
    {
        $leaveRequests = LeaveRequest::with('employee')->where('status', 'pending')->latest()->get();

        return view('staff::leaves.admin.index', compact('leaveRequests'));
    }

    // Approve Leave
    public function approveLeave($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        // It's safer to ensure days_count exists before approving.
        if (is_null($leaveRequest->days_count)) {
            return redirect()->back()->with('error', 'ERROR: Cannot approve leave because days_count is not calculated. Please run the backfill command or have the user re-submit the request.');
        }

        $leaveRequest->update(['status' => 'approved']);

        // BUG FIX: Use the year from the leave request's start_date, NOT the current year.
        $leaveBalance = $leaveRequest->employee->leaveBalances()
            ->where('leave_type', $leaveRequest->leave_type)
            ->where('year', Carbon::parse($leaveRequest->start_date)->year)
            ->first();

        if ($leaveBalance) {
            // We now only use the reliable days_count from the database.
            // No more fallback to a buggy calculation.
            $leaveBalance->increment('used_days', $leaveRequest->days_count);
        }

        Mail::to($leaveRequest->employee->email)->queue(new LeaveRequestStatusUpdated($leaveRequest));

        return redirect()->route('staff.leaves.admin')->with('success', 'Leave request approved.');
    }

    // Reject Leave
    public function rejectLeave(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->update([
            'status' => 'rejected',
            'admin_note' => $request->input('admin_note'),
        ]);
        // Send notification email to the employee
        Mail::to($leaveRequest->employee->email)->queue(new LeaveRequestStatusUpdated($leaveRequest));

        return redirect()->route('staff.leaves.admin')->with('success', 'Leave request rejected.');
    }

    // Leave Report
    public function leaveReport(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));
        $prevYear = $year - 1;
        $department = $request->department;

        $employeeBase = Employee::where('status', 'approved')
            ->when($department, fn ($q) => $q->where('department', $department));

        $employees = (clone $employeeBase)
            ->with(['leaveRequests' => fn ($q) => $q->whereYear('start_date', $year)])
            ->with(['leaveBalances' => fn ($q) => $q->where('year', $year)])
            ->withCount([
                'leaveRequests as approved_annual_count' => fn ($q) => $q->whereYear('start_date', $year)->where('status', 'approved')->where('leave_type', 'Annual'),
                'leaveRequests as approved_sick_count' => fn ($q) => $q->whereYear('start_date', $year)->where('status', 'approved')->where('leave_type', 'Sick'),
                'leaveRequests as approved_casual_count' => fn ($q) => $q->whereYear('start_date', $year)->where('status', 'approved')->where('leave_type', 'Casual'),
                'leaveRequests as approved_compassionate_count' => fn ($q) => $q->whereYear('start_date', $year)->where('status', 'approved')->where('leave_type', 'Compassionate'),
                'leaveRequests as approved_maternity_count' => fn ($q) => $q->whereYear('start_date', $year)->where('status', 'approved')->where('leave_type', 'Maternity'),
                'leaveRequests as approved_paternity_count' => fn ($q) => $q->whereYear('start_date', $year)->where('status', 'approved')->where('leave_type', 'Paternity'),
            ])
            ->get();

        $departments = Employee::where('status', 'approved')
            ->whereNotNull('department')
            ->distinct('department')
            ->pluck('department')
            ->sort();

        $years = range(now()->year - 5, now()->year + 1);

        // ---- Summary Stats ----
        $totalEmployees = $employees->count();
        $totalBalanceEntries = $employees->sum(fn ($e) => $e->leaveBalances->count());

        $allRequests = $employees->pluck('leaveRequests')->flatten();
        $totalLeaveRequests = $allRequests->count();
        $totalApprovedRequests = $allRequests->where('status', 'approved')->count();
        $totalPendingRequests = $allRequests->where('status', 'pending')->count();
        $totalRejectedRequests = $allRequests->where('status', 'rejected')->count();
        $totalCancelledRequests = $allRequests->where('status', 'cancelled')->count();

        $totalLeaveDays = LeaveRequest::whereYear('start_date', $year)
            ->when($department, fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('department', $department)))
            ->where('status', 'approved')
            ->sum('days_count');

        // ---- Leave Type Distribution ----
        $leaveTypeStats = LeaveRequest::whereYear('start_date', $year)
            ->when($department, fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('department', $department)))
            ->selectRaw("leave_type, COUNT(*) as total_requests, SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count, SUM(CASE WHEN status = 'approved' THEN days_count ELSE 0 END) as days_used")
            ->groupBy('leave_type')
            ->get()
            ->keyBy('leave_type');

        $leaveTypes = ['Annual', 'Casual', 'Sick', 'Compassionate', 'Paternity', 'Maternity'];

        // ---- Monthly Breakdown (approved days per month) ----
        $monthlyDays = LeaveRequest::whereYear('start_date', $year)
            ->where('status', 'approved')
            ->when($department, fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('department', $department)))
            ->selectRaw('MONTH(start_date) as month, SUM(days_count) as days')
            ->groupBy('month')
            ->pluck('days', 'month');

        $monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyData = [];
        $maxMonthlyDays = 1;
        foreach (range(1, 12) as $m) {
            $d = (int) ($monthlyDays[$m] ?? 0);
            $monthlyData[] = $d;
            if ($d > $maxMonthlyDays) {
                $maxMonthlyDays = $d;
            }
        }

        // ---- Department Summary ----
        $deptStats = (clone $employeeBase)->get()->groupBy('department')->map(function ($emps) use ($year) {
            $empIds = $emps->pluck('id');
            $requests = LeaveRequest::whereIn('employee_id', $empIds)->whereYear('start_date', $year);
            $approved = (clone $requests)->where('status', 'approved');

            return [
                'employee_count' => $emps->count(),
                'total_requests' => (clone $requests)->count(),
                'approved_requests' => (clone $approved)->count(),
                'days_used' => (clone $approved)->sum('days_count'),
                'pending_requests' => (clone $requests)->where('status', 'pending')->count(),
            ];
        })->sortByDesc('days_used');

        // ---- Previous Year Comparison ----
        $prevDaysUsed = LeaveRequest::whereYear('start_date', $prevYear)
            ->when($department, fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('department', $department)))
            ->where('status', 'approved')
            ->sum('days_count');

        // ---- Averages ----
        $avgDaysPerRequest = $totalApprovedRequests > 0
            ? round($totalLeaveDays / $totalApprovedRequests, 1) : 0;
        $avgDaysPerEmployee = $totalEmployees > 0
            ? round($totalLeaveDays / $totalEmployees, 1) : 0;

        // ---- Most requested leave type ----
        $mostUsedType = $leaveTypeStats->sortByDesc('days_used')->keys()->first();

        // ---- Pending requests requiring approval ----
        $pendingRequests = LeaveRequest::with('employee')
            ->whereYear('start_date', $year)
            ->where('status', 'pending')
            ->when($department, fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('department', $department)))
            ->latest()
            ->limit(10)
            ->get();

        return view('staff::leaves.admin.report', compact(
            'employees', 'year', 'prevYear', 'department', 'departments', 'years',
            'totalEmployees', 'totalBalanceEntries', 'totalLeaveRequests',
            'totalApprovedRequests', 'totalPendingRequests', 'totalRejectedRequests',
            'totalCancelledRequests', 'totalLeaveDays', 'prevDaysUsed',
            'leaveTypeStats', 'leaveTypes', 'monthlyData', 'monthlyLabels',
            'maxMonthlyDays', 'deptStats', 'avgDaysPerRequest', 'avgDaysPerEmployee',
            'mostUsedType', 'pendingRequests',
        ));
    }

    // Leave Balance Form
    public function leaveBalance()
    {
        $user = Auth::user();
        $employee = $user->employee;

        $currentYear = date('Y');

        if (! $employee) {
            return redirect()->route('staff.dashboard')
                ->with('error', 'No employee record linked to your account.');
        }

        $employees = Employee::with(['leaveBalances' => fn ($q) => $q->where('year', $currentYear)])
            ->where('id', $employee->id)
            ->paginate(15);

        return view('staff::leaves.admin.balances', compact('employees', 'currentYear'));
    }

    // Submit Leave Balance
    public function leaveBalanceSubmit(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;
        if (! $employee) {
            return redirect()->back()->with('error', 'You do not have an employee profile.');
        }

        $validated = $request->validate([
            'leave_type' => 'required|string|in:Annual,Casual,Compassionate,Sick,Paternity,Maternity',
            'staff_code' => 'required|integer|exists:employees,staff_code',
            'total_days' => 'required|integer|min:1',
        ]);
        $employeeId = Employee::where('staff_code', $validated['staff_code'])->value('id');
        LeaveBalance::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'leave_type' => $validated['leave_type'],
                'year' => date('Y'),
            ],
            [
                'total_days' => $validated['total_days'],
                'used_days' => 0, // Reset used days on update
            ]
        );

        // Bug Fix: Corrected the redirect route name from 'staff.leave.balance' to 'staff.leaves.balance'
        return redirect()->route('staff.leaves.balance')->with('success', 'Leave balance created/updated successfully.');
    }

    /**
     * Reset an employee's used leave days to zero.
     */
    public function resetBalance($id)
    {
        $balance = LeaveBalance::findOrFail($id);
        $balance->used_days = 0;
        $balance->save();

        return back()->with('success', 'Leave balance has been successfully reset.');
    }

    /**
     * Delete a leave type from an employee's balance record.
     */
    public function deleteBalance($id)
    {
        $balance = LeaveBalance::findOrFail($id);
        $balance->delete();

        return back()->with('success', 'Leave type has been successfully deleted.');
    }

    /**
     * Show the form for an admin to apply leave on behalf of an employee.
     *
     * @return View
     */
    public function showApplyForOtherForm()
    {
        // Fetch all active employees to populate the dropdown in the view
        $employees = Employee::where('status', 'approved')->orderBy('name')->get();

        return view('staff::leaves.admin.apply', compact('employees'));
    }

    /**
     * Let an admin submit a leave request on behalf of another employee.
     *
     * @return RedirectResponse
     */
    public function submitLeaveForOther(Request $request)
    {
        // Validate the admin's input, including the selected employee
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id', // This is the key difference
            'leave_type' => 'required|string|in:Annual,Casual,Compassionate,Sick,Paternity,Maternity',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
            'covered_by' => 'nullable|exists:employees,id|different:employee_id',
        ]);

        $employee = Employee::find($validated['employee_id']);

        // Call our reusable private method to handle the core logic
        return $this->processLeaveRequest($validated, $employee);
    }

    /**
     * Reusable private method to process a leave request.
     * This avoids code duplication between self-service and admin submissions.
     *
     * @return RedirectResponse
     */
    private function processLeaveRequest(array $validatedData, Employee $employee)
    {
        $startDate = Carbon::parse($validatedData['start_date']);
        $endDate = Carbon::parse($validatedData['end_date']);
        $leaveType = $validatedData['leave_type'];

        // --- START OF NEW LOGIC ---

        // 1. Calculate the number of business days requested
        $leaveDaysCount = 0;
        $year = $startDate->year;
        $publicHolidays = config("holidays.{$year}", []); // Get holidays from our new config file

        // Create a period to iterate over each day in the requested range
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            // Check if the day is a weekend (Saturday or Sunday)
            if ($date->isWeekend()) {
                continue; // Skip this day
            }
            // Check if the day is a public holiday
            if (in_array($date->format('m-d'), $publicHolidays)) {
                continue; // Skip this day
            }
            // If it's not a weekend or holiday, count it as a leave day
            $leaveDaysCount++;
        }

        // If no working days are selected (e.g., applying for leave on a weekend)
        if ($leaveDaysCount <= 0) {
            return redirect()->back()->withInput()->withErrors(['end_date' => 'The selected date range does not contain any working days.']);
        }

        // --- END OF NEW LOGIC ---

        // 2. Check for overlapping leave requests for the same employee
        $overlappingLeave = LeaveRequest::where('employee_id', $employee->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $endDate)->where('end_date', '>=', $startDate);
                });
            })
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($overlappingLeave) {
            $conflictDates = Carbon::parse($overlappingLeave->start_date)->format('M d, Y')
                .' to '.Carbon::parse($overlappingLeave->end_date)->format('M d, Y');

            return redirect()->back()->withInput()->withErrors([
                'start_date' => "Overlapping leave request found: {$overlappingLeave->leave_type} ({$conflictDates}) — Status: {$overlappingLeave->status}.",
            ]);
        }

        // 3. Check if the employee has sufficient leave balance
        $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type', $leaveType)
            ->where('year', $year)
            ->first();

        if (! $leaveBalance) {
            return redirect()->back()->withInput()->withErrors(['leave_type' => 'No leave balance is configured for this employee and leave type.']);
        }

        if ($leaveBalance->remaining_days < $leaveDaysCount) {
            return redirect()->back()->withInput()->withErrors(['end_date' => "Insufficient leave balance. Remaining days: {$leaveBalance->remaining_days}, Requested: {$leaveDaysCount}."]);
        }

        // 4. Create the leave request with the accurately calculated days
        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'staff_code' => $employee->staff_code,
            'leave_type' => $leaveType,
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'reason' => $validatedData['reason'],
            'days_count' => $leaveDaysCount,
            'covered_by' => $validatedData['covered_by'] ?? null,
            'status' => 'pending',
        ]);
        // 5. Send notification email to HR/Admin
        $adminEmail = config('staff.hr_email', 'hr@brickspoint.com');
        Mail::to($adminEmail)->queue(new LeaveRequestSubmitted($leaveRequest));

        // return redirect()->route('staff.leaves.admin')->with('success', "Leave request for {$employee->name} has been submitted successfully.");
        // --- NEW CONDITIONAL REDIRECT LOGIC ---
        // Check if the logged-in user has permission to apply leave for others.
        if (Auth::user()->can('leaves.apply-for-others')) {
            // If they can, they are likely HR/Admin. Redirect to the admin page.
            return redirect()->route('staff.leaves.admin')->with('success', "Leave request for {$employee->name} has been submitted successfully.");
        } else {
            // Otherwise, they are a regular employee. Redirect to their own dashboard.
            return redirect()->route('staff.leaves.index')->with('success', 'Your leave request has been submitted successfully.');
        }
    }

    // You can now refactor the original `submitLeaveRequest` to use this new private method too!
    public function submitLeaveRequest(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;
        if (! $employee) {
            return redirect()->back()->with('error', 'You do not have an employee profile.');
        }

        $validated = $request->validate([
            // staff_code is not needed if we get the employee from Auth
            'leave_type' => 'required|string|in:Annual,Casual,Compassionate,Sick,Paternity,Maternity',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        return $this->processLeaveRequest($validated, $employee);
    }

    /**
     * Show the admin page for managing all employee leave balances.
     *
     * @return View
     */
    public function showBalancesAdmin(Request $request)
    {
        $currentYear = date('Y');

        // Start a query for employees
        $query = Employee::with(['leaveBalances' => function ($query) use ($currentYear) {
            $query->where('year', $currentYear);
        }])->where('status', 'approved');

        // If a search term is provided, filter by name or staff code
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('staff_code', 'like', "%{$searchTerm}%");
            });
        }

        // Paginate the results to handle large lists
        $employees = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('staff::leaves.admin.balances', compact('employees', 'currentYear'));
    }

    /**
     * Handle the submission for creating or updating an employee's leave balance.
     *
     * @return RedirectResponse
     */
    public function updateBalanceAdmin(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|string|max:255',
            'total_days' => 'required|numeric|min:0',
            'year' => 'required|digits:4',
        ]);

        // This command finds a balance matching the criteria or creates a new one,
        // then updates it with the provided total_days. It's perfect for this task.
        $balance = LeaveBalance::updateOrCreate(
            [
                'employee_id' => $validated['employee_id'],
                'leave_type' => $validated['leave_type'],
                'year' => $validated['year'],
            ],
            [
                'total_days' => $validated['total_days'],
            ]
        );

        // // We also need to recalculate remaining_days in case used_days is not zero
        // $balance->remaining_days = $balance->total_days - $balance->used_days;
        // $balance->save();

        return back()->with('success', 'Leave balance updated successfully.');
    }

    public function showLeaveHistory(Request $request)
    {
        // Fetch all employees to populate the filter dropdown
        $employees = Employee::where('status', 'approved')->orderBy('name')->get();

        // Start with the base query, eager-loading the employee relationship for efficiency
        $query = LeaveRequest::with('employee')->latest();

        // Apply filters based on request input
        // Filter by a specific employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by leave type
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        // Filter by status (pending, approved, rejected)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by a date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        }

        // Paginate the final results and append the query string to the links
        $leaveHistory = $query->paginate(20)->withQueryString();

        return view('staff::leaves.admin.history', compact('leaveHistory', 'employees'));
    }

    public function leaveCalendar(Request $request)
    {
        $month = (int) ($request->month ?? now()->month);
        $year = (int) ($request->year ?? now()->year);
        $department = $request->department;

        $employees = Employee::where('status', 'approved')
            ->when($department, fn ($q) => $q->where('department', $department))
            ->pluck('id');

        $firstOfMonth = now()->setYear($year)->setMonth($month)->startOfMonth();
        $lastOfMonth = $firstOfMonth->copy()->endOfMonth();

        $leaves = LeaveRequest::with('employee')
            ->whereIn('employee_id', $employees)
            ->where('status', 'approved')
            ->where('start_date', '<=', $lastOfMonth)
            ->where('end_date', '>=', $firstOfMonth)
            ->get();

        // Build date-indexed map
        $dateMap = [];
        foreach ($leaves as $leave) {
            $period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($period as $date) {
                $key = $date->format('Y-m-d');
                if (! isset($dateMap[$key])) {
                    $dateMap[$key] = collect([]);
                }
                $dateMap[$key]->push($leave);
            }
        }

        $departments = Employee::where('status', 'approved')
            ->whereNotNull('department')
            ->distinct('department')
            ->pluck('department')
            ->sort();

        $startOfCalendar = $firstOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar = $lastOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        return view('staff::leaves.admin.calendar', compact(
            'dateMap', 'month', 'year', 'department', 'departments',
            'firstOfMonth', 'lastOfMonth', 'startOfCalendar', 'endOfCalendar',
        ));
    }
}
