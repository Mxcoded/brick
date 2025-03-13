<?php

namespace Modules\Staff\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\EmploymentHistory;
use Modules\Staff\Models\EducationalBackground;
use Illuminate\Support\Str;

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

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'place_of_birth' => 'required|string|max:255',
            'state_of_origin' => 'required|string|max:255',
            'lga' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female,Other',
            'date_of_birth' => 'required|date',
            'marital_status' => 'required|string|in:Single,Married,Divorced,Widowed',
            'blood_group' => 'required|string|max:255',
            'genotype' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
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
        ]);

        // Handle file uploads
        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        }

        $cvPath = null;
        if ($request->hasFile('cv_path')) {
            $cvPath = $request->file('cv_path')->store('cvs', 'public');
        }
        // Generate a unique staff code
        $staffCode = Str::upper(Str::random(4));

        // Create the employee
        $employee = Employee::create([
            'name' => $request->name,
            'place_of_birth' => $request->place_of_birth,
            'state_of_origin' => $request->state_of_origin,
            'lga' => $request->lga,
            'nationality' => $request->nationality,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'marital_status' => $request->marital_status,
            'blood_group' => $request->blood_group,
            'genotype' => $request->genotype,
            'phone_number' => $request->phone_number,
            'residential_address' => $request->residential_address,
            'next_of_kin_name' => $request->next_of_kin_name,
            'next_of_kin_phone' => $request->next_of_kin_phone,
            'ice_contact_name' => $request->ice_contact_name,
            'ice_contact_phone' => $request->ice_contact_phone,
            'profile_image' => $profileImagePath,
            'cv_path' => $cvPath,
            'staff_code' => $staffCode,
            'status' => 'draft', // Default status
        ]);

        // Save Employment History (if provided)
        if ($request->employment_history) {
            foreach ($request->employment_history as $history) {
                $employee->employmentHistories()->create([
                    'employer_name' => $history['employer_name'],
                    'employer_contact' => $history['employer_contact'],
                    'position_held' => $history['position_held'],
                    'duration' => $history['duration'],
                ]);
            }
        }

        // Save Educational Background (if provided)
        if ($request->educational_background) {
            foreach ($request->educational_background as $index => $education) {
                $certificatePath = null;
                if (isset($education['certificate_path']) && $education['certificate_path']) {
                    $certificatePath = $education['certificate_path']->store('certificates', 'public');
                }

                $employee->educationalBackgrounds()->create([
                    'school_name' => $education['school_name'],
                    'qualification' => $education['qualification'],
                    'start_date' => $education['start_date'],
                    'end_date' => $education['end_date'],
                    'certificate_path' => $certificatePath,
                ]);
            }
        }

        // Redirect with success message and staff code
        return redirect()->route('staff.index')
            ->with('success', 'Employee created successfully. Staff Code: ' . $staffCode);
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'place_of_birth' => 'required|string|max:255',
            'state_of_origin' => 'required|string|max:255',
            'lga' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female,Other',
            'date_of_birth' => 'required|date',
            'marital_status' => 'required|string|in:Single,Married,Divorced,Widowed',
            'blood_group' => 'required|string|max:255',
            'genotype' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
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
        ]);

        // Find the employee
        $employee = Employee::findOrFail($id);

        // Handle file uploads
        if ($request->hasFile('profile_image')) {
            // Delete old profile image if it exists
            if ($employee->profile_image) {
                Storage::disk('public')->delete($employee->profile_image);
            }
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            $employee->profile_image = $profileImagePath;
        }

        if ($request->hasFile('cv_path')) {
            // Delete old CV if it exists
            if ($employee->cv_path) {
                Storage::disk('public')->delete($employee->cv_path);
            }
            $cvPath = $request->file('cv_path')->store('cvs', 'public');
            $employee->cv_path = $cvPath;
        }

        // Update the employee
        $employee->update([
            'name' => $request->name,
            'place_of_birth' => $request->place_of_birth,
            'state_of_origin' => $request->state_of_origin,
            'lga' => $request->lga,
            'nationality' => $request->nationality,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'marital_status' => $request->marital_status,
            'blood_group' => $request->blood_group,
            'genotype' => $request->genotype,
            'phone_number' => $request->phone_number,
            'residential_address' => $request->residential_address,
            'next_of_kin_name' => $request->next_of_kin_name,
            'next_of_kin_phone' => $request->next_of_kin_phone,
            'ice_contact_name' => $request->ice_contact_name,
            'ice_contact_phone' => $request->ice_contact_phone,
            'status' => 'draft',
        ]);

        // Update Employment History
        if ($request->employment_history) {
            $employee->employmentHistories()->delete(); // Delete existing records
            foreach ($request->employment_history as $history) {
                $employee->employmentHistories()->create([
                    'employer_name' => $history['employer_name'],
                    'employer_contact' => $history['employer_contact'],
                    'position_held' => $history['position_held'],
                    'duration' => $history['duration'],
                ]);
            }
        }

        // Update Educational Background
        if ($request->educational_background) {
            $employee->educationalBackgrounds()->delete(); // Delete existing records
            foreach ($request->educational_background as $index => $education) {
                $certificatePath = null;
                if (isset($education['certificate_path']) && $education['certificate_path']) {
                    $certificatePath = $education['certificate_path']->store('certificates', 'public');
                }

                $employee->educationalBackgrounds()->create([
                    'school_name' => $education['school_name'],
                    'qualification' => $education['qualification'],
                    'start_date' => $education['start_date'],
                    'end_date' => $education['end_date'],
                    'certificate_path' => $certificatePath,
                ]);
            }
        }

        // Redirect with success message
        return redirect()->route('staff.index')->with('success', 'Employee updated successfully.');
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
    // Add other methods like show, edit, update, destroy as needed
}
