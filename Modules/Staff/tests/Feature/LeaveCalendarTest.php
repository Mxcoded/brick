<?php

namespace Modules\Staff\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\LeaveBalance;
use Modules\Staff\Models\LeaveRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeaveCalendarTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Employee $employee;

    private Employee $coverage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ValidateCsrfToken::class,
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'access_staff_dashboard', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'employees.read', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'leaves.approve', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'leaves.apply-for-others', 'guard_name' => 'web']);

        $this->user = User::factory()->create([
            'type' => 'staff', 'status' => 'active',
            'password' => Hash::make('password'),
        ]);
        $this->user->givePermissionTo(['access_staff_dashboard', 'employees.read', 'leaves.approve', 'leaves.apply-for-others']);

        $makeEmployee = fn (string $name) => Employee::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).'_'.uniqid().'@brickspoint.com',
            'phone_number' => '080'.random_int(10000000, 99999999),
            'position' => 'Staff',
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
            'residential_address' => '123 Test St',
            'next_of_kin_name' => 'Kin',
            'next_of_kin_phone' => '080'.random_int(10000000, 99999999),
            'ice_contact_name' => 'Ice',
            'ice_contact_phone' => '080'.random_int(10000000, 99999999),
            'start_date' => now()->subYear(),
            'staff_code' => 'EMP'.str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT),
            'user_id' => $this->user->id,
        ]);

        $this->employee = $makeEmployee('Alice OnLeave');
        $this->coverage = $makeEmployee('Bob Covering');

        // Link user to first employee
        $this->employee->user_id = $this->user->id;
        $this->employee->save();

        LeaveBalance::create([
            'employee_id' => $this->employee->id,
            'leave_type' => 'Annual',
            'total_days' => 30,
            'used_days' => 5,
            'year' => now()->year,
        ]);
    }

    #[Test]
    public function calendar_page_loads()
    {
        $this->actingAs($this->user);

        $response = $this->get('/staff/leaves/calendar');
        $response->assertStatus(200);
        $response->assertSee('Leave Calendar');
        $response->assertSee('Sun');
        $response->assertSee('Mon');
    }

    #[Test]
    public function calendar_shows_approved_leaves()
    {
        $this->actingAs($this->user);

        LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => 'Annual',
            'start_date' => now()->startOfMonth()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->startOfMonth()->addDays(7)->format('Y-m-d'),
            'status' => 'approved',
            'days_count' => 3,
        ]);

        $response = $this->get('/staff/leaves/calendar');
        $response->assertStatus(200);
        $response->assertSee('Alice OnLeave');
    }

    #[Test]
    public function calendar_hides_pending_or_rejected_leaves()
    {
        $this->actingAs($this->user);

        LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => 'Sick',
            'start_date' => now()->startOfMonth()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->startOfMonth()->addDays(12)->format('Y-m-d'),
            'status' => 'pending',
            'days_count' => 3,
        ]);

        $response = $this->get('/staff/leaves/calendar');
        $response->assertDontSee('Alice OnLeave');
    }

    #[Test]
    public function admin_apply_form_shows_coverage_dropdown()
    {
        $this->actingAs($this->user);

        $response = $this->get('/staff/leaves/admin/apply');
        $response->assertStatus(200);
        $response->assertSee('Covered By');
        $response->assertSee('Bob Covering');
        $response->assertSee('Alice OnLeave');
    }

    #[Test]
    public function leave_request_can_be_created_with_coverage()
    {
        $this->actingAs($this->user);

        $start = now()->addDays(5)->format('Y-m-d');
        $end = now()->addDays(7)->format('Y-m-d');

        $response = $this->post('/staff/leaves/admin/apply', [
            'employee_id' => $this->employee->id,
            'leave_type' => 'Annual',
            'start_date' => $start,
            'end_date' => $end,
            'reason' => 'Vacation',
            'covered_by' => $this->coverage->id,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $this->employee->id,
            'covered_by' => $this->coverage->id,
            'leave_type' => 'Annual',
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function leave_request_rejects_same_employee_as_coverage()
    {
        $this->actingAs($this->user);

        $start = now()->addDays(5)->format('Y-m-d');
        $end = now()->addDays(7)->format('Y-m-d');

        $response = $this->post('/staff/leaves/admin/apply', [
            'employee_id' => $this->employee->id,
            'leave_type' => 'Annual',
            'start_date' => $start,
            'end_date' => $end,
            'reason' => 'Vacation',
            'covered_by' => $this->employee->id, // same as employee_id!
        ]);

        $response->assertSessionHasErrors(['covered_by']);
    }

    #[Test]
    public function calendar_can_be_filtered_by_department()
    {
        $this->actingAs($this->user);

        $response = $this->get('/staff/leaves/calendar?department=Front+Office');
        $response->assertStatus(200);
        $response->assertSee('Leave Calendar');
    }

    #[Test]
    public function calendar_navigates_previous_and_next_month()
    {
        $this->actingAs($this->user);

        $response = $this->get('/staff/leaves/calendar?month=1&year=2026');
        $response->assertStatus(200);
        $response->assertSee('January 2026');
    }
}
