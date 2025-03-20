<?php

namespace Modules\Staff\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\EmploymentHistory;
use Modules\Staff\Models\EducationalBackground;
use Modules\Staff\Models\LeaveRequest;
use Modules\Staff\Models\LeaveBalance;
use Illuminate\Support\Facades\DB;




class StaffController extends Controller
{
    public function index()
    {
        $employees = Employee::all();
        return view('staff::index', compact('employees'));
    }

    public function create()
    {
        return view('staff::create');
    }
    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        return view('staff::edit', ['employee' => $employee]);
    }
    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        return view('staff::show', compact('employee'));
    }
    public function store(Request $request)
    {
        // Validate request with new fields
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'place_of_birth' => 'required|string|max:255',
            'state_of_origin' => 'required|string|max:255',
            'lga' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female,Other',
            'date_of_birth' => 'required|date',
            'marital_status' => 'required|string|in:Single,Married,Divorced,Widowed',
            'blood_group' => 'required|string|max:255',
            'genotype' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:employees,phone_number',
            'position' => 'required|string|max:255',
            'residential_address' => 'required|string',
            'next_of_kin_name' => 'required|string|max:255',
            'next_of_kin_phone' => 'required|string|max:255',
            'ice_contact_name' => 'required|string|max:255',
            'ice_contact_phone' => 'required|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cv_path' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'employment_history' => 'nullable|array',
            'employment_history.*.employer_name' => 'nullable|string|max:255',
            'employment_history.*.employer_contact' => 'nullable|string|max:255',
            'employment_history.*.position_held' => 'nullable|string|max:255',
            'employment_history.*.duration' => 'nullable|string|max:255',
            'educational_background' => 'nullable|array',
            'educational_background.*.school_name' => 'nullable|string|max:255',
            'educational_background.*.qualification' => 'nullable|string|max:255',
            'educational_background.*.start_date' => 'nullable|date',
            'educational_background.*.end_date' => 'nullable|date',
            'educational_background.*.certificate_path' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'start_date' => 'required|date',                         // New: Date employed
            'end_date' => 'nullable|date|after_or_equal:start_date', // New: Date departed (optional)
            'note_for_leaving' => 'nullable|string|max:1000',        // New: Note for leaving
            'leaving_reason' => 'nullable|in:resignation,sack,transfer', // New: Reason for leaving
            'branch_name' => 'nullable|string|max:255',              // New: Branch name
            'resignation_letter' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // New: Resignation letter upload
        ]);

        // Use transaction for data integrity
        DB::transaction(function () use ($request, $validatedData) {
            $profileImagePath = $request->hasFile('profile_image')
                ? $request->file('profile_image')->store('profile_images', 'public')
                : null;

            $cvPath = $request->hasFile('cv_path')
                ? $request->file('cv_path')->store('cvs', 'public')
                : null;

            $resignationLetterPath = $request->hasFile('resignation_letter')
                ? $request->file('resignation_letter')->store('resignation_letters', 'public')
                : null;

            $staffCode = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

            // Create employee with new fields
            $employee = Employee::create(array_merge($validatedData, [
                'profile_image' => $profileImagePath,
                'cv_path' => $cvPath,
                'resignation_letter' => $resignationLetterPath,
                'staff_code' => $staffCode,
                'status' => 'draft',
            ]));

            // Save Employment History
            if (!empty($request->employment_history)) {
                foreach ($request->employment_history as $history) {
                    $employee->employmentHistories()->create($history);
                }
            }

            // Save Educational Background
            if (!empty($request->educational_background)) {
                foreach ($request->educational_background as $education) {
                    $certificatePath = isset($education['certificate_path'])
                        ? $education['certificate_path']->store('certificates', 'public')
                        : null;

                    $employee->educationalBackgrounds()->create(array_merge($education, [
                        'certificate_path' => $certificatePath,
                    ]));
                }
            }
        });

        return redirect()->route('staff.index')->with('success', 'Employee created successfully.');
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        // Validate request with new fields
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'place_of_birth' => 'required|string|max:255',
            'state_of_origin' => 'required|string|max:255',
            'lga' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female,Other',
            'date_of_birth' => 'required|date',
            'marital_status' => 'required|string|in:Single,Married,Divorced,Widowed',
            'blood_group' => 'required|string|max:255',
            'genotype' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:employees,phone_number,' . $employee->id,
            'position' => 'required|string|max:255',
            'residential_address' => 'required|string',
            'next_of_kin_name' => 'required|string|max:255',
            'next_of_kin_phone' => 'required|string|max:255',
            'ice_contact_name' => 'required|string|max:255',
            'ice_contact_phone' => 'required|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:12096',
            'cv_path' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'employment_history' => 'nullable|array',
            'employment_history.*.employer_name' => 'nullable|string|max:255',
            'employment_history.*.employer_contact' => 'nullable|string|max:255',
            'employment_history.*.position_held' => 'nullable|string|max:255',
            'employment_history.*.duration' => 'nullable|string|max:255',
            'educational_background' => 'nullable|array',
            'educational_background.*.school_name' => 'nullable|string|max:255',
            'educational_background.*.qualification' => 'nullable|string|max:255',
            'educational_background.*.start_date' => 'nullable|date',
            'educational_background.*.end_date' => 'nullable|date',
            'educational_background.*.certificate_path' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'start_date' => 'required|date',                         // New: Date employed
            'end_date' => 'nullable|date|after_or_equal:start_date', // New: Date departed (optional)
            'note_for_leaving' => 'nullable|string|max:1000',        // New: Note for leaving
            'leaving_reason' => 'nullable|in:resignation,sack,transfer', // New: Reason for leaving
            'branch_name' => 'nullable|string|max:255',              // New: Branch name
            'resignation_letter' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // New: Resignation letter upload
        ]);

        DB::transaction(function () use ($employee, $request, $validatedData) {
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                if ($employee->profile_image) {
                    Storage::disk('public')->delete($employee->profile_image);
                }
                $employee->profile_image = $request->file('profile_image')->store('profile_images', 'public');
            }

            // Handle CV upload
            if ($request->hasFile('cv_path')) {
                if ($employee->cv_path) {
                    Storage::disk('public')->delete($employee->cv_path);
                }
                $employee->cv_path = $request->file('cv_path')->store('cvs', 'public');
            }

            // Handle resignation letter upload
            if ($request->hasFile('resignation_letter')) {
                if ($employee->resignation_letter) {
                    Storage::disk('public')->delete($employee->resignation_letter);
                }
                $employee->resignation_letter = $request->file('resignation_letter')->store('resignation_letters', 'public');
            }

            // Generate staff code if null
            if (is_null($employee->staff_code)) {
                do {
                    $staffCode = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
                } while (Employee::where('staff_code', $staffCode)->exists());
                $employee->staff_code = $staffCode;
            }

            // Remove file fields from validatedData
            unset($validatedData['profile_image'], $validatedData['cv_path'], $validatedData['resignation_letter']);

            // Update employee with validated data
            $employee->update($validatedData);

            // Update related records
            $employee->employmentHistories()->delete();
            if (!empty($request->employment_history)) {
                $employee->employmentHistories()->createMany($request->employment_history);
            }

            $employee->educationalBackgrounds()->delete();
            if (!empty($request->educational_background)) {
                $employee->educationalBackgrounds()->createMany($request->educational_background);
            }
        });

        return redirect()->route('staff.index')->with('success', 'Employee updated successfully.');
    }

    // Optionally, update destroy method to clean up resignation letter
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        // Delete related records and files
        $employee->employmentHistories()->delete();
        $employee->educationalBackgrounds()->delete();
        if ($employee->profile_image) {
            Storage::disk('public')->delete($employee->profile_image);
        }
        if ($employee->cv_path) {
            Storage::disk('public')->delete($employee->cv_path);
        }
        if ($employee->resignation_letter) {
            Storage::disk('public')->delete($employee->resignation_letter);
        }

        $employee->delete();

        return redirect()->route('staff.index')
            ->with('success', 'Employee deleted successfully.');
    }
    public function showCompleteRegistrationForm()
    {
        return view('staff::complete-registration');
    }

    public function completeRegistration(Request $request)
    {
        $request->validate([
            'staff_code' => 'required|string|exists:employees,staff_code',
        ]);

        // Find the employee by staff code
        $employee = Employee::where('staff_code', $request->staff_code)->first();

        // Redirect to the edit page for the employee
        return redirect()->route('staff.edit', $employee->id);
    }


    public function approvalIndex()
    {
        // Fetch employees with 'pending' status
        $employees = Employee::where('status', 'pending')->get();
        return view('staff::approvals.index', compact('employees'));
    }

    public function approve($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['status' => 'approved']);

        return redirect()->route('staff.approvals.index')
            ->with('success', 'Employee approved successfully.');
    }

    public function reject($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['status' => 'rejected']);

        return redirect()->route('staff.approvals.index')
            ->with('success', 'Employee rejected successfully.');
    }
    // Employee Leave Dashboard
    public function leaveIndex()
    {
        $employee = auth()->user()->employee; // Assuming employees are linked to users
        $leaveRequests = $employee->leaveRequests()->latest()->get();
        $leaveBalances = $employee->leaveBalances()->where('year', date('Y'))->get();
        return view('staff::leaves.index', compact('employee', 'leaveRequests', 'leaveBalances'));
    }

    // Leave Request Form
    public function leaveRequestForm()
    {
        $employee = auth()->user()->employee;
        $leaveBalances = $employee->leaveBalances()->where('year', date('Y'))->get();
        return view('staff::leaves.request', compact('employee', 'leaveBalances'));
    }

    // Submit Leave Request
    public function submitLeaveRequest(Request $request)
    {
        $employee = auth()->user()->employee;
        $validated = $request->validate([
            'leave_type' => 'required|string|in:Vacation,Sick,Maternity', // Add more types as needed
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        // Check leave balance
        $leaveBalance = $employee->leaveBalances()
            ->where('leave_type', $validated['leave_type'])
            ->where('year', date('Y'))
            ->firstOrFail();
        $daysRequested = (new \DateTime($validated['start_date']))->diff(new \DateTime($validated['end_date']))->days + 1;

        if ($leaveBalance->remaining_days < $daysRequested) {
            return redirect()->back()->withErrors(['leave_type' => 'Insufficient leave balance.']);
        }

        LeaveRequest::create(array_merge($validated, ['employee_id' => $employee->id]));

        return redirect()->route('staff.leaves.index')->with('success', 'Leave request submitted successfully.');
    }

    // Admin Leave Management
    public function leaveAdminIndex()
    {
        $leaveRequests = LeaveRequest::with('employee')->where('status', 'pending')->latest()->get();
        return view('staff::leaves.admin', compact('leaveRequests'));
    }

    // Approve Leave
    public function approveLeave($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->update(['status' => 'approved']);

        // Update leave balance
        $leaveBalance = $leaveRequest->employee->leaveBalances()
            ->where('leave_type', $leaveRequest->leave_type)
            ->where('year', date('Y'))
            ->first();
        $leaveBalance->increment('used_days', $leaveRequest->days_count);

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

        return redirect()->route('staff.leaves.admin')->with('success', 'Leave request rejected.');
    }

    // Leave Report
    public function leaveReport(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $employees = Employee::with(['leaveRequests' => fn($q) => $q->whereYear('start_date', $year)])
            ->with(['leaveBalances' => fn($q) => $q->where('year', $year)])
            ->get();

        return view('staff::leaves.report', compact('employees', 'year'));
    }
    // Add other methods like show, edit, update, destroy as needed
}
