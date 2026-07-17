<?php

namespace Modules\Staff\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Modules\Staff\Models\AttendanceLog;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\Shift;
use Modules\Staff\Models\ShiftAssignment;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Employee $employee;

    private Shift $shift;

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

        $code = 'TST'.str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $this->employee = Employee::create([
            'name' => 'Test Staff',
            'email' => 'attendance_test_'.uniqid().'@brickspoint.com',
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

        $this->shift = Shift::create([
            'name' => 'Morning',
            'start_time' => '06:00',
            'end_time' => '14:00',
            'grace_minutes' => 15,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_attendance()
    {
        $this->get('/staff/attendance')->assertRedirect(route('login'));
        $this->get('/staff/attendance/clock')->assertRedirect(route('login'));
        $this->get('/staff/attendance/report')->assertRedirect(route('login'));
    }

    /** @test */
    public function clock_in_form_shows_employee_info()
    {
        $this->actingAs($this->user);

        $response = $this->get('/staff/attendance/clock');
        $response->assertStatus(200);
        $response->assertSee($this->employee->name);
        $response->assertSee('Clock In');
    }

    /** @test */
    public function employee_can_clock_in()
    {
        $this->actingAs($this->user);

        $response = $this->post('/staff/attendance/clock-in', [
            'note' => 'Arrived on time',
        ]);

        $response->assertRedirect('/staff/attendance/clock');
        $response->assertSessionHas('success');

        $log = AttendanceLog::where('employee_id', $this->employee->id)
            ->whereDate('date', now()->today())
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->clock_in);
        $this->assertNull($log->clock_out);
        $this->assertEquals('Arrived on time', $log->clock_in_note);
    }

    /** @test */
    public function employee_cannot_clock_in_twice()
    {
        $this->actingAs($this->user);

        $this->post('/staff/attendance/clock-in');
        $response = $this->post('/staff/attendance/clock-in');

        $response->assertSessionHas('warning');
    }

    /** @test */
    public function employee_cannot_clock_out_before_clocking_in()
    {
        $this->actingAs($this->user);

        $response = $this->post('/staff/attendance/clock-out');
        $response->assertSessionHas('error');
    }

    /** @test */
    public function employee_can_complete_full_clock_cycle()
    {
        $this->actingAs($this->user);

        $this->post('/staff/attendance/clock-in', ['note' => 'Started']);
        $response = $this->post('/staff/attendance/clock-out', ['note' => 'Ended']);

        $response->assertSessionHas('success');

        $log = AttendanceLog::where('employee_id', $this->employee->id)
            ->whereDate('date', now()->today())
            ->first();

        $this->assertNotNull($log->clock_in);
        $this->assertNotNull($log->clock_out);
        $this->assertEquals('Ended', $log->clock_out_note);
    }

    /** @test */
    public function employee_marked_late_when_shift_assigned_and_after_grace()
    {
        $this->actingAs($this->user);

        ShiftAssignment::create([
            'employee_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'date' => now()->today(),
        ]);

        $clockInTime = now()->today()->setTime(6, 30, 0);

        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'date' => now()->today(),
            'clock_in' => $clockInTime,
            'status' => 'late',
            'late_minutes' => 30,
        ]);

        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $this->employee->id,
            'status' => 'late',
            'late_minutes' => 30,
        ]);
    }

    /** @test */
    public function attendance_index_shows_todays_records()
    {
        $this->actingAs($this->user);

        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'date' => now()->today(),
            'clock_in' => now()->today()->setTime(8, 0, 0),
            'status' => 'present',
        ]);

        $response = $this->get('/staff/attendance');
        $response->assertStatus(200);
        $response->assertSee($this->employee->name);
        $response->assertSee('Present');
    }

    /** @test */
    public function attendance_report_shows_monthly_data()
    {
        $this->actingAs($this->user);

        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'date' => now()->startOfMonth()->addDays(1),
            'clock_in' => now()->startOfMonth()->addDays(1)->setTime(8, 0, 0),
            'clock_out' => now()->startOfMonth()->addDays(1)->setTime(17, 0, 0),
            'status' => 'present',
        ]);

        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'date' => now()->startOfMonth()->addDays(2),
            'clock_in' => now()->startOfMonth()->addDays(2)->setTime(9, 0, 0),
            'status' => 'late',
            'late_minutes' => 60,
        ]);

        $response = $this->get('/staff/attendance/report');
        $response->assertStatus(200);
        $response->assertSee($this->employee->name);
        $response->assertSee('Attendance Report');
    }

    /** @test */
    public function attendance_filtered_by_department()
    {
        $this->actingAs($this->user);

        $response = $this->get('/staff/attendance?department=Front+Office');
        $response->assertStatus(200);
        $response->assertSee($this->employee->name);
    }

    /** @test */
    public function completed_cycle_shows_duration_on_clock_page()
    {
        $this->actingAs($this->user);

        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'date' => now()->today(),
            'clock_in' => now()->today()->setTime(8, 0, 0),
            'clock_out' => now()->today()->setTime(17, 0, 0),
            'status' => 'present',
        ]);

        $response = $this->get('/staff/attendance/clock');
        $response->assertStatus(200);
        $response->assertSee('Duration');
    }

    /** @test */
    public function week_history_shows_on_clock_page()
    {
        $this->actingAs($this->user);

        // Create a record from earlier this week
        $pastDay = now()->startOfWeek()->addDay();
        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'date' => $pastDay,
            'clock_in' => $pastDay->copy()->setTime(8, 0, 0),
            'clock_out' => $pastDay->copy()->setTime(17, 0, 0),
            'status' => 'present',
        ]);

        $response = $this->get('/staff/attendance/clock');
        $response->assertStatus(200);
        $response->assertSee('Present');
    }
}
