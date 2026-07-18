<?php

namespace Modules\Staff\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Staff\Models\Employee;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StaffEditActivityLogTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $role = Role::firstOrCreate(['name' => RoleEnum::ADMIN->value, 'guard_name' => 'web']);
        $perm = Permission::firstOrCreate(['name' => 'employees.update', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($perm)) {
            $role->givePermissionTo($perm);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->admin->assignRole(RoleEnum::ADMIN->value);
        $this->actingAs($this->admin);

        $this->employee = Employee::create([
            'name' => 'John Doe',
            'email' => 'john'.uniqid().'@example.com',
            'place_of_birth' => 'Lagos',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'nationality' => 'Nigerian',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'marital_status' => 'Single',
            'blood_group' => 'O+',
            'genotype' => 'AA',
            'phone_number' => '080'.rand(10000000, 99999999),
            'position' => 'Manager',
            'department' => 'Ops',
            'residential_address' => '123 Staff Road',
            'next_of_kin_name' => 'Jane Doe',
            'next_of_kin_phone' => '080'.rand(10000000, 99999999),
            'ice_contact_name' => 'Jane Doe',
            'ice_contact_phone' => '080'.rand(10000000, 99999999),
        ]);
    }

    /**
     * Editing a staff member (the user's reported "staff.edit" case) must log
     * exactly which employee was edited, not just a generic "update".
     */
    public function test_editing_a_staff_member_logs_the_affected_employee()
    {
        $response = $this->put(route('staff.update', $this->employee->id), [
            'name' => 'John Doe Updated',
            'email' => $this->employee->email,
            'place_of_birth' => 'Lagos',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'nationality' => 'Nigerian',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'marital_status' => 'Single',
            'blood_group' => 'O+',
            'genotype' => 'AA',
            'phone_number' => $this->employee->phone_number,
            'position' => 'Senior Manager',
            'department' => 'Ops',
            'residential_address' => '123 Staff Road',
            'next_of_kin_name' => 'Jane Doe',
            'next_of_kin_phone' => $this->employee->next_of_kin_phone,
            'ice_contact_name' => 'Jane Doe',
            'ice_contact_phone' => $this->employee->ice_contact_phone,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'staff.update',
            'model_type' => Employee::class,
            'model_id' => $this->employee->id,
        ]);

        $log = UserActivityLog::where('model_type', Employee::class)
            ->where('model_id', $this->employee->id)
            ->firstOrFail();

        // The description must name the affected employee, not just the route.
        $this->assertStringContainsString('John Doe', $log->description);
        $this->assertStringContainsString((string) $this->employee->getKey(), $log->description);
    }
}
