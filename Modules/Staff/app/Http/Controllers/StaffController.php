<?php

namespace Modules\Staff\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Staff\Exports\StaffExport;
use Modules\Staff\Mail\WelcomeMail;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\LeaveRequest;
use Modules\Staff\Models\StaffSetting;
use Modules\Frontdeskcrm\Rules\ValidEmail;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        Log::info('Middleware: ', app('router')->getMiddleware());

        // Get branch filter from query string
        $branchFilter = $request->query('branch');

        // Build query with optional branch filter
        $query = Employee::query();

        if ($branchFilter) {
            $query->whereRaw('LOWER(branch_name) = ?', [strtolower($branchFilter)]);
        }

        $employees = $query->get();

        // Get all employees for stats (unfiltered)
        $allEmployees = Employee::all();

        // Total approved staff
        $totalApprovedStaff = $allEmployees->where('status', 'approved')->count();

        // Staff currently on leave
        $currentDate = now();
        $staffOnLeaveCount = LeaveRequest::where('status', 'approved')
            ->where('start_date', '<=', $currentDate)
            ->where('end_date', '>=', $currentDate)
            ->distinct('employee_id')
            ->count('employee_id');

        // Staff on leave with details
        $staffOnLeave = LeaveRequest::with('employee')
            ->where('status', 'approved')
            ->where('start_date', '<=', $currentDate)
            ->where('end_date', '>=', $currentDate)
            ->get();

        // Active staff (total approved minus staff on leave)
        $activeStaffCount = $totalApprovedStaff - $staffOnLeaveCount;

        // Branch-based staff counts (approved staff only)
        $asokoroStaffCount = $allEmployees->where('status', 'approved')
            ->filter(fn ($e) => strtolower($e->branch_name ?? '') === 'asokoro')
            ->count();

        $wuseStaffCount = $allEmployees->where('status', 'approved')
            ->filter(fn ($e) => strtolower($e->branch_name ?? '') === 'wuse')
            ->count();

        return view('staff::index', compact(
            'employees',
            'totalApprovedStaff',
            'staffOnLeaveCount',
            'staffOnLeave',
            'activeStaffCount',
            'asokoroStaffCount',
            'wuseStaffCount',
            'branchFilter'
        ));
    }

    public function dashboard()
    {
        $now = now();
        $employees = Employee::all();

        // Core counts
        $totalEmployees = $employees->where('status', 'approved')->count();
        $exitedEmployees = $employees->where('status', 'rejected')->count();
        $pendingApprovals = $employees->where('status', 'draft')->count();

        // Currently on leave
        $currentDate = $now;
        $onLeaveCount = LeaveRequest::where('status', 'approved')
            ->where('start_date', '<=', $currentDate)
            ->where('end_date', '>=', $currentDate)
            ->distinct('employee_id')
            ->count('employee_id');

        $activeAtWork = $totalEmployees - $onLeaveCount;

        $staffOnLeave = LeaveRequest::with('employee')
            ->where('status', 'approved')
            ->where('start_date', '<=', $currentDate)
            ->where('end_date', '>=', $currentDate)
            ->get();

        // New hires this month
        $newHiresThisMonth = Employee::where('status', 'approved')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // Recent hires (last 5)
        $recentHires = Employee::where('status', 'approved')
            ->latest()
            ->take(5)
            ->get();

        // Department distribution
        $departmentStats = Employee::where('status', 'approved')
            ->select('department', DB::raw('count(*) as count'))
            ->groupBy('department')
            ->orderByDesc('count')
            ->get();

        // Branch breakdown
        $asokoroCount = $employees->where('status', 'approved')
            ->filter(fn ($e) => strtolower($e->branch_name ?? '') === 'asokoro')->count();
        $wuseCount = $employees->where('status', 'approved')
            ->filter(fn ($e) => strtolower($e->branch_name ?? '') === 'wuse')->count();
        $otherBranchCount = $totalEmployees - $asokoroCount - $wuseCount;

        // Gender breakdown
        $maleCount = $employees->where('status', 'approved')
            ->where('gender', 'Male')->count();
        $femaleCount = $employees->where('status', 'approved')
            ->where('gender', 'Female')->count();
        $otherGenderCount = $employees->where('status', 'approved')
            ->where('gender', 'Other')->count();

        // Pending leave requests
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();

        // Upcoming birthdays this month
        $upcomingBirthdays = Employee::where('status', 'approved')
            ->whereMonth('date_of_birth', $now->month)
            ->orderByRaw('DAY(date_of_birth)')
            ->get();

        $userRoles = session('user_roles', []);

        return view('staff::dashboard', compact(
            'totalEmployees',
            'activeAtWork',
            'onLeaveCount',
            'staffOnLeave',
            'pendingApprovals',
            'exitedEmployees',
            'newHiresThisMonth',
            'recentHires',
            'departmentStats',
            'asokoroCount',
            'wuseCount',
            'otherBranchCount',
            'maleCount',
            'femaleCount',
            'otherGenderCount',
            'pendingLeaves',
            'upcomingBirthdays',
            'userRoles',
        ));
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
    // public function show($id)
    // {
    //     $employee = Employee::findOrFail($id);
    //     return view('staff::show', compact('employee'));
    // }

    public function show(Employee $staff)
    {
        // $staff is already fetched!
        return view('staff::show', ['employee' => $staff]);
    }

    public function store(Request $request)
    {
        // Validate request with new fields
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:employees,email', new ValidEmail],
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
            'department' => 'required|string|max:255',
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
            'leaving_reason' => 'nullable|in:resignation,sack,transfer,absconded', // New: Reason for leaving
            'branch_name' => 'nullable|string|max:255',              // New: Branch name
            'resignation_letter' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // New: Resignation letter upload
            'nin' => ['nullable', 'digits:11'], // New: National Identification number
            'bvn' => ['nullable', 'digits:11'], // New: Bank Verification number
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
            if (! empty($request->employment_history)) {
                foreach ($request->employment_history as $history) {
                    $employee->employmentHistories()->create($history);
                }
            }

            // Save Educational Background
            if (! empty($request->educational_background)) {
                foreach ($request->educational_background as $education) {
                    $certificatePath = isset($education['certificate_path'])
                        ? $education['certificate_path']->store('certificates', 'public')
                        : null;

                    $employee->educationalBackgrounds()->create(array_merge($education, [
                        'certificate_path' => $certificatePath,
                    ]));
                }
            }

            // Add default leave balances
            $defaultBalances = [
                ['leave_type' => 'Annual', 'total_days' => 21],
                ['leave_type' => 'Casual', 'total_days' => 5],
                ['leave_type' => 'Compassionate', 'total_days' => 3],
                ['leave_type' => 'Sick', 'total_days' => 3],
                ['leave_type' => 'Paternity', 'total_days' => 14],
                ['leave_type' => 'Maternity', 'total_days' => 84],
            ];

            foreach ($defaultBalances as $balance) {
                $employee->leaveBalances()->create([
                    'leave_type' => $balance['leave_type'],
                    'year' => date('Y'),
                    'total_days' => $balance['total_days'],
                    'used_days' => 0,
                    'remaining_days' => $balance['total_days'],
                ]);
            }
        });

        $employee = Employee::latest()->first();

        try {
            Mail::to($employee->email)->send(new WelcomeMail($employee));
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email for employee '.$employee->id.' ('.$employee->email.'): '.$e->getMessage());

            return redirect()->route('staff.index')->with('warning', 'Staff record created successfully, but the welcome email could not be delivered to '.$employee->email.'. Please verify the email address.');
        }

        return redirect()->route('staff.index')->with('success', 'Staff record created successfully. A welcome email has been sent to '.$employee->email.'.');
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        // Validate request with new fields
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,'.$employee->id,
            'place_of_birth' => 'required|string|max:255',
            'state_of_origin' => 'required|string|max:255',
            'lga' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female,Other',
            'date_of_birth' => 'required|date',
            'marital_status' => 'required|string|in:Single,Married,Divorced,Widowed',
            'blood_group' => 'required|string|max:255',
            'genotype' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:employees,phone_number,'.$employee->id,
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
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
            'leaving_reason' => 'nullable|in:resignation,sack,transfer,absconded', // New: Reason for leaving
            'branch_name' => 'nullable|string|max:255',              // New: Branch name
            'resignation_letter' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // New: Resignation letter upload
            'nin' => ['nullable', 'digits:11'], // New: Update National identification Number
            'bvn' => ['nullable', 'digits:11'], // New Updat Bank Verification Number
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
            // Check if end_date is provided in the request
            if ($request->has('end_date') && ! empty($request->input('end_date'))) {
                $validatedData['status'] = 'rejected'; // Or another status like 'terminated'
            } else {
                $validatedData['status'] = 'draft'; // Revert to 'draft' if end_date is not supplied or is empty
            }

            // Remove file fields from validatedData
            unset($validatedData['profile_image'], $validatedData['cv_path'], $validatedData['resignation_letter']);

            // Update employee with validated data
            $employee->update($validatedData);

            // Update related records
            $employee->employmentHistories()->delete();
            if (! empty($request->employment_history)) {
                $employee->employmentHistories()->createMany($request->employment_history);
            }

            $employee->educationalBackgrounds()->delete();
            if (! empty($request->educational_background)) {
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

    public function pending()
    {
        return view('staff::pending');
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

    // Staff Approval Functions
    public function approvalIndex(Request $request)
    {
        $query = Employee::where('status', 'draft');

        // Filter by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->input('search').'%');
        }

        // Sort
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        $query->orderBy($sort, $direction);

        $employees = $query->get();

        return view('staff::approvals.index', compact('employees'));
    }

    public function approve($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['status' => 'approved']);

        return redirect()->route('staff.approvals.index')
            ->with('success', 'Employee approved successfully.');
    }

    // Reject Staff
    public function reject(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $employee->update([
            'status' => 'rejected',
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        // Optional: Log the action
        Log::info("Employee {$employee->id} rejected by ".Auth::user()->name, [
            'reason' => $request->input('rejection_reason'),
        ]);

        return redirect()->route('staff.approvals.index')
            ->with('success', 'Employee marked as exited successfully.');
    }

    public function birthdays(Request $request)
    {
        // 1. Get all active employees
        $employees = Employee::where('status', 'approved')->get();

        // 2. Calculate next birthday logic (Same as before)
        $sortedBirthdays = $employees->map(function ($employee) {
            $dob = Carbon::parse($employee->date_of_birth);
            $nextBirthday = $dob->copy()->year(now()->year);

            if ($nextBirthday->isPast() && ! $nextBirthday->isToday()) {
                $nextBirthday->addYear();
            }

            $employee->next_birthday = $nextBirthday;
            $employee->turning_age = $nextBirthday->diffInYears($dob);

            return $employee;
        })->sortBy('next_birthday'); // Sort by the calculated date

        // 3. MANUAL PAGINATION CONFIGURATION
        $perPage = 12; // How many cards per page
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // Slice the collection to get items for the current page
        $currentPageItems = $sortedBirthdays->slice(($currentPage - 1) * $perPage, $perPage)->values();

        // Create the Paginator instance
        $paginatedBirthdays = new LengthAwarePaginator(
            $currentPageItems,
            $sortedBirthdays->count(),
            $perPage,
            $currentPage,
            ['path' => RequestFacade::url(), 'query' => $request->query()]
        );

        return view('staff::birthdays', compact('paginatedBirthdays'));
    }

    public function settings()
    {
        $message = StaffSetting::get('birthday_sms_message')
            ?? config('staff.birthday_sms_message');

        return view('staff::settings', compact('message'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'birthday_sms_message' => 'required|string|max:160',
        ]);

        StaffSetting::set('birthday_sms_message', $request->input('birthday_sms_message'));

        return redirect()->route('staff.settings')
            ->with('success', 'Birthday SMS message updated successfully.');
    }

    /**
     * Export staff data to Excel.
     */
    public function export(Request $request)
    {
        $branchFilter = $request->query('branch');
        $statusFilter = $request->query('status');

        // Build filename
        $filename = 'staff-data';
        if ($branchFilter) {
            $filename .= '-'.strtolower($branchFilter);
        }
        if ($statusFilter) {
            $filename .= '-'.strtolower($statusFilter);
        }
        $filename .= '-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(
            new StaffExport($branchFilter, $statusFilter),
            $filename
        );
    }

    /**
     * Show the public staff verification form.
     * Anyone can access this to verify if a staff code is active.
     */
    public function verifyForm()
    {
        return view('staff::verify');
    }

    /**
     * Look up a staff code and return the verification result.
     */
    public function verifyLookup(Request $request)
    {
        $request->validate([
            'staff_code' => 'required|string|max:20',
        ]);

        $employee = Employee::where('staff_code', $request->staff_code)->first();

        if (! $employee) {
            return back()->with('error', 'No staff record found with that code.')->withInput();
        }

        $isActive = is_null($employee->end_date) || $employee->end_date > now();

        return back()->with('verified', [
            'name' => $employee->name,
            'staff_code' => $employee->staff_code,
            'department' => $employee->department,
            'position' => $employee->position,
            'status' => $isActive ? 'active' : 'inactive',
            'employed_since' => $employee->created_at->format('F Y'),
        ]);
    }
}
