<?php

namespace Modules\Staff\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Modules\Staff\Models\AttendanceLog;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\LeaveRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ValidateCsrfToken::class,
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'access_staff_dashboard', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'employees.read', 'guard_name' => 'web']);

        $this->user = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);
        $this->user->givePermissionTo(['access_staff_dashboard', 'employees.read']);

        $code = 'RPT'.str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $this->employee = Employee::create([
            'name' => 'Report Test Staff',
            'email' => 'report_test_'.uniqid().'@brickspoint.com',
            'phone_number' => '080'.random_int(10000000, 99999999),
            'position' => 'Front Desk Officer',
            'department' => 'Front Office',
            'branch_name' => 'Asokoro',
            'status' => 'approved',
            'place_of_birth' => 'Abuja',
            'state_of_origin' => 'FCT',
            'lga' => 'Abuja Municipal',
            'nationality' => 'Nigerian',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-15',
            'marital_status' => 'Single',
            'blood_group' => 'O+',
            'genotype' => 'AA',
            'residential_address' => '123 Test Street, Abuja',
            'next_of_kin_name' => 'John Doe',
            'next_of_kin_phone' => '080'.random_int(10000000, 99999999),
            'ice_contact_name' => 'Jane Doe',
            'ice_contact_phone' => '080'.random_int(10000000, 99999999),
            'start_date' => now()->subYear(),
            'staff_code' => $code,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_reports_page_loads()
    {
        $this->actingAs($this->user)
            ->get(route('staff.reports.index'))
            ->assertStatus(200)
            ->assertSee('Advanced Reports')
            ->assertSee('Headcount Trend')
            ->assertSee('Turnover')
            ->assertSee('Absenteeism')
            ->assertSee('Leave Utilization');
    }

    public function test_reports_page_shows_headcount_trend()
    {
        Employee::create([
            'name' => 'New Hire',
            'email' => 'newhire_'.uniqid().'@brickspoint.com',
            'phone_number' => '080'.random_int(10000000, 99999999),
            'department' => 'Housekeeping',
            'status' => 'approved',
            'start_date' => now()->startOfMonth(),
            'staff_code' => 'RPT'.str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT),
            'position' => 'Staff',
            'place_of_birth' => 'Abuja',
            'state_of_origin' => 'FCT',
            'lga' => 'Abuja Municipal',
            'nationality' => 'Nigerian',
            'gender' => 'Female',
            'date_of_birth' => '1992-05-20',
            'marital_status' => 'Married',
            'blood_group' => 'A+',
            'genotype' => 'AS',
            'residential_address' => '456 Test Avenue, Abuja',
            'next_of_kin_name' => 'Jane Doe',
            'next_of_kin_phone' => '080'.random_int(10000000, 99999999),
            'ice_contact_name' => 'John Doe',
            'ice_contact_phone' => '080'.random_int(10000000, 99999999),
        ]);

        $this->actingAs($this->user)
            ->get(route('staff.reports.index'))
            ->assertStatus(200)
            ->assertSee('Active Employees');
    }

    public function test_reports_page_shows_absenteeism()
    {
        $today = '2026-07-04';
        $yesterday = '2026-07-03';

        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'date' => $today,
            'clock_in' => $today.' 08:00:00',
            'status' => 'present',
        ]);

        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'date' => $yesterday,
            'clock_in' => $yesterday.' 08:00:00',
            'status' => 'absent',
        ]);

        $this->actingAs($this->user)
            ->get(route('staff.reports.index'))
            ->assertStatus(200);
    }

    public function test_reports_page_shows_leave_utilization()
    {
        LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => 'Annual',
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->startOfMonth()->addDays(4)->format('Y-m-d'),
            'reason' => 'Vacation',
            'status' => 'approved',
            'days_count' => 5,
        ]);

        $this->actingAs($this->user)
            ->get(route('staff.reports.index'))
            ->assertStatus(200)
            ->assertSee('Annual');
    }

    public function test_reports_page_respects_year_filter()
    {
        $this->actingAs($this->user)
            ->get(route('staff.reports.index', ['year' => 2023]))
            ->assertStatus(200)
            ->assertSee('2023');
    }

    public function test_reports_page_requires_authentication()
    {
        $this->get(route('staff.reports.index'))
            ->assertRedirect(route('login'));
    }

    public function test_reports_page_requires_employees_read_permission()
    {
        $userWithoutPerm = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);
        $userWithoutPerm->givePermissionTo(['access_staff_dashboard']);

        $this->actingAs($userWithoutPerm)
            ->get(route('staff.reports.index'))
            ->assertStatus(403);
    }

    public function test_reports_handles_no_data_gracefully()
    {
        $this->actingAs($this->user)
            ->get(route('staff.reports.index', ['year' => 2020]))
            ->assertStatus(200)
            ->assertSee('Advanced Reports');
    }

    public function test_reports_shows_department_absenteeism()
    {
        $deptEmployee = Employee::create([
            'name' => 'Dept Staff',
            'email' => 'dept_'.uniqid().'@brickspoint.com',
            'phone_number' => '080'.random_int(10000000, 99999999),
            'department' => 'Housekeeping',
            'status' => 'approved',
            'start_date' => now()->subYear(),
            'staff_code' => 'RPT'.str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT),
            'position' => 'Staff',
            'place_of_birth' => 'Abuja',
            'state_of_origin' => 'FCT',
            'lga' => 'Abuja Municipal',
            'nationality' => 'Nigerian',
            'gender' => 'Female',
            'date_of_birth' => '1992-05-20',
            'marital_status' => 'Married',
            'blood_group' => 'A+',
            'genotype' => 'AS',
            'residential_address' => '789 Test Lane, Abuja',
            'next_of_kin_name' => 'Jane Doe',
            'next_of_kin_phone' => '080'.random_int(10000000, 99999999),
            'ice_contact_name' => 'John Doe',
            'ice_contact_phone' => '080'.random_int(10000000, 99999999),
        ]);

        AttendanceLog::create([
            'employee_id' => $deptEmployee->id,
            'date' => '2026-07-04',
            'clock_in' => '2026-07-04 08:00:00',
            'status' => 'absent',
        ]);

        $this->actingAs($this->user)
            ->get(route('staff.reports.index'))
            ->assertStatus(200)
            ->assertSee('Housekeeping');
    }

    public function test_turnover_shows_hires_and_departures()
    {
        Employee::create([
            'name' => 'Recent Departure',
            'email' => 'depart_'.uniqid().'@brickspoint.com',
            'phone_number' => '080'.random_int(10000000, 99999999),
            'department' => 'Housekeeping',
            'status' => 'approved',
            'start_date' => now()->subYear(),
            'end_date' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
            'staff_code' => 'RPT'.str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT),
            'position' => 'Staff',
            'place_of_birth' => 'Abuja',
            'state_of_origin' => 'FCT',
            'lga' => 'Abuja Municipal',
            'nationality' => 'Nigerian',
            'gender' => 'Male',
            'date_of_birth' => '1988-11-10',
            'marital_status' => 'Single',
            'blood_group' => 'O+',
            'genotype' => 'AA',
            'residential_address' => '101 Test Road, Abuja',
            'next_of_kin_name' => 'Jane Doe',
            'next_of_kin_phone' => '080'.random_int(10000000, 99999999),
            'ice_contact_name' => 'John Doe',
            'ice_contact_phone' => '080'.random_int(10000000, 99999999),
        ]);

        $this->actingAs($this->user)
            ->get(route('staff.reports.index'))
            ->assertStatus(200)
            ->assertSee('Departures');
    }
}
