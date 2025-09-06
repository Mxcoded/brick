<?php

namespace Modules\Staff\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\LeaveBalance;
use Modules\Staff\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Modules\Staff\Emails\LeaveRequestSubmitted;
use Modules\Staff\Emails\LeaveRequestStatusUpdated;

// Other 'use' statements...
use Illuminate\Support\Facades\Mail;


class LeaveController extends Controller
{
    // Employee Leave Dashboard
    public function leaveIndex()
    {
        $user = Auth::user();
        $employee = $user->employee;
        if (!$employee) {
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
        if (!$employee) {
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
     * @param int $id The ID of the leave request.
     * @return \Illuminate\Http\RedirectResponse
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
    // Submit Leave Request
    // public function submitLeaveRequest(Request $request)
    // {
    //     $user = Auth::user();
    //     $employee = $user->employee;
    //     if (!$employee) {
    //         return redirect()->back()->with('error', 'You do not have an employee profile.');
    //     }

    //     // Validate leave request
    //     $validated = $request->validate([
    //         'staff_code' => 'required|integer|exists:employees,staff_code',
    //         'leave_type' => 'required|string|in:Annual,Casual,Compassionate,Sick,Paternity,Maternity',
    //         'start_date' => 'required|date|after_or_equal:today',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //         'reason' => 'nullable|string|max:1000',
    //     ]);
    //     $startDate = new \DateTime($validated['start_date']);
    //     $endDate = new \DateTime($validated['end_date']);
    //     $employeeId = Employee::where('staff_code', $validated['staff_code'])->value('id');
    //     $overlappingLeave = LeaveRequest::where('employee_id', $employeeId)
    //         ->where(function ($query) use ($startDate, $endDate) {
    //             $query->whereBetween('start_date', [$startDate, $endDate])
    //                 ->orWhereBetween('end_date', [$startDate, $endDate])
    //                 ->orWhere(function ($query) use ($startDate, $endDate) {
    //                     $query->where('start_date', '<=', $startDate)
    //                         ->where('end_date', '>=', $endDate);
    //                 });
    //         })
    //         ->whereIn('status', ['pending', 'approved'])
    //         ->exists();

    //     if ($overlappingLeave) {
    //         return redirect()->back()->withErrors([
    //             'start_date' => 'You already have a leave request that overlaps with this date range.'
    //         ]);
    //     }

    //     $leaveBalance = LeaveBalance::where('employee_id', $employeeId)
    //         ->where('leave_type', $validated['leave_type'])
    //         ->where('year', date('Y'))
    //         ->first();

    //     if (!$leaveBalance) {
    //         return redirect()->back()->withErrors(['leave_type' => 'No leave balance available for this type.']);
    //     }

    //     $daysRequested = $startDate->diff($endDate)->days + 1;

    //     if ($leaveBalance->remaining_days < $daysRequested) {
    //         return redirect()->back()->withErrors(['leave_type' => 'Insufficient leave balance.']);
    //     }

    //     LeaveRequest::create(array_merge($validated, [
    //         'employee_id' => $employeeId,
    //         'staff_code' => $validated['staff_code'],
    //         'days_count' => $daysRequested,
    //         'status' => 'pending',
    //     ]));

    //     return redirect()->route('staff.leaves.index')->with('success', 'Leave request submitted successfully.');
    // }

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
        $leaveRequest->update(['status' => 'approved']);

        $leaveBalance = $leaveRequest->employee->leaveBalances()
            ->where('leave_type', $leaveRequest->leave_type)
            ->where('year', date('Y'))
            ->first();

        if ($leaveBalance) {
            $daysCount = $leaveRequest->days_count ?? (new \DateTime($leaveRequest->start_date))
                ->diff(new \DateTime($leaveRequest->end_date))->days + 1;
            $leaveBalance->increment('used_days', $daysCount);
          
        }
        // Send notification email to the employee
        Mail::to($leaveRequest->employee->email)->send(new LeaveRequestStatusUpdated($leaveRequest));

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
        Mail::to($leaveRequest->employee->email)->send(new LeaveRequestStatusUpdated($leaveRequest));

        return redirect()->route('staff.leaves.admin')->with('success', 'Leave request rejected.');
    }

    // Leave Report
    public function leaveReport(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $employees = Employee::with(['leaveRequests' => fn($q) => $q->whereYear('start_date', $year)])
            ->with(['leaveBalances' => fn($q) => $q->where('year', $year)])
            ->get();

        return view('staff::leaves.admin.report', compact('employees', 'year'));
    }

    // Leave Balance Form
    public function leaveBalance()
    {
        $user = Auth::user();
        $employee = $user->employee;
        return view('staff::leaves.admin.balances', compact('employee'));
    }

    // Submit Leave Balance
    public function leaveBalanceSubmit(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;
        if (!$employee) {
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
     * Show the form for an admin to apply leave on behalf of an employee.
     *
     * @return \Illuminate\View\View
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
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
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
        ]);

        $employee = Employee::find($validated['employee_id']);

        // Call our reusable private method to handle the core logic
        return $this->processLeaveRequest($validated, $employee);
    }

    /**
     * Reusable private method to process a leave request.
     * This avoids code duplication between self-service and admin submissions.
     *
     * @param array $validatedData
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse
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
            ->exists();

        if ($overlappingLeave) {
            return redirect()->back()->withInput()->withErrors([
                'start_date' => 'This employee already has an overlapping leave request in this date range.'
            ]);
        }

        // 3. Check if the employee has sufficient leave balance
        $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type', $leaveType)
            ->where('year', $year)
            ->first();

        if (!$leaveBalance) {
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
            'days_count' => $leaveDaysCount, // Use our newly calculated count
            'status' => 'pending',
        ]);
        // 5. Send notification email to HR/Admin
        $adminEmail = 'hr@brickspoint.com'; // It's best to store this in a config file this is just for brickspoint.
        Mail::to($adminEmail)->send(new LeaveRequestSubmitted($leaveRequest));


        // return redirect()->route('staff.leaves.admin')->with('success', "Leave request for {$employee->name} has been submitted successfully.");
        // --- NEW CONDITIONAL REDIRECT LOGIC ---
        // Check if the logged-in user has permission to apply leave for others.
        if (Auth::user()->can('apply-leave-for-others')) {
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
        if (!$employee) {
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
     * @param Request $request
     * @return \Illuminate\View\View
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
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateBalanceAdmin(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type'  => 'required|string|max:255',
            'total_days'  => 'required|numeric|min:0',
            'year'        => 'required|digits:4',
        ]);

        // This command finds a balance matching the criteria or creates a new one,
        // then updates it with the provided total_days. It's perfect for this task.
        $balance = LeaveBalance::updateOrCreate(
            [
                'employee_id' => $validated['employee_id'],
                'leave_type'  => $validated['leave_type'],
                'year'        => $validated['year'],
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
}
