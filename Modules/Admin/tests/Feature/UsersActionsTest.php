<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Mail\AccountCreated;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Modules\Admin\Filament\Pages\CreateUserFromEmployee;
use Modules\Admin\Filament\Resources\Users\Pages\ListUsers;
use Modules\Staff\Models\Employee;
use Spatie\Permission\Models\Role;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tests\TestCase;

class UsersActionsTest extends TestCase
{
    use DatabaseTransactions;

    private function adminUser(): User
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $user->assignRole(RoleEnum::ADMIN->value);

        return $user;
    }

    private function createEmployee(string $name = 'John Doe'): Employee
    {
        return Employee::create([
            'name' => $name,
            'email' => 'john.doe@example.com',
            'place_of_birth' => 'Abuja',
            'state_of_origin' => 'FCT',
            'lga' => 'Municipal',
            'nationality' => 'Nigeria',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'marital_status' => 'single',
            'blood_group' => 'O+',
            'genotype' => 'AA',
            'phone_number' => '08000000000',
            'position' => 'Front Desk Officer',
            'residential_address' => 'Test Address, Abuja',
            'department' => 'Front Office',
            'next_of_kin_name' => 'Jane Doe',
            'next_of_kin_phone' => '08000000001',
            'ice_contact_name' => 'Jane Doe',
            'ice_contact_phone' => '08000000001',
            'start_date' => '2024-01-01',
        ]);
    }

    public function test_set_status_requires_reason_when_suspending()
    {
        $this->actingAs($this->adminUser());

        $target = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        Livewire::test(ListUsers::class)
            ->callTableAction('setStatus', $target->getKey(), ['status' => 'suspended'])
            ->assertHasTableActionErrors(['suspension_reason']);

        $this->assertEquals('active', $target->fresh()->status);
    }

    public function test_set_status_suspends_user_with_reason()
    {
        $this->actingAs($this->adminUser());

        $target = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        Livewire::test(ListUsers::class)
            ->callTableAction('setStatus', $target->getKey(), [
                'status' => 'suspended',
                'suspension_reason' => 'Policy violation',
            ]);

        $target->refresh();

        $this->assertEquals('suspended', $target->status);
        $this->assertNotNull($target->suspended_at);
        $this->assertEquals('Policy violation', $target->suspension_reason);
    }

    public function test_set_status_reactivates_user_and_clears_reason()
    {
        $this->actingAs($this->adminUser());

        $target = User::factory()->create([
            'type' => 'staff',
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspension_reason' => 'Policy violation',
        ]);

        Livewire::test(ListUsers::class)
            ->callTableAction('setStatus', $target->getKey(), ['status' => 'active']);

        $target->refresh();

        $this->assertEquals('active', $target->status);
        $this->assertNull($target->suspended_at);
        $this->assertNull($target->suspension_reason);
    }

    public function test_resend_credentials_is_hidden_for_guests()
    {
        $this->actingAs($this->adminUser());

        $guest = User::factory()->create(['type' => 'guest', 'status' => 'active']);
        $staff = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('resendCredentials', $guest->getKey())
            ->assertTableActionVisible('resendCredentials', $staff->getKey());
    }

    public function test_resend_credentials_updates_password_and_sends_email()
    {
        $this->actingAs($this->adminUser());

        Mail::fake();

        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        Livewire::test(ListUsers::class)
            ->callTableAction('resendCredentials', $user->getKey());

        $this->assertNotEquals($user->password, $user->fresh()->password);

        Mail::assertSent(AccountCreated::class, function (AccountCreated $mail) use ($user): bool {
            return $mail->hasTo($user->email);
        });
    }

    public function test_create_user_from_employee_creates_linked_user()
    {
        $this->actingAs($this->adminUser());

        Mail::fake();

        $employee = $this->createEmployee('Ada Obi');
        $role = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        Livewire::test(CreateUserFromEmployee::class)
            ->fillForm([
                'employee_id' => $employee->getKey(),
                'email' => 'ada.obi@brickspoint.ng',
                'password' => 'secret-pass-1234',
                'password_confirmation' => 'secret-pass-1234',
                'role' => $role->name,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'ada.obi@brickspoint.ng')->first();

        $this->assertNotNull($user);
        $this->assertEquals('Ada Obi', $user->name);
        $this->assertEquals('staff', $user->type);
        $this->assertTrue($user->hasRole('staff'));
        $this->assertEquals($user->id, $employee->fresh()->user_id);

        Mail::assertSent(AccountCreated::class, function (AccountCreated $mail) use ($user): bool {
            return $mail->hasTo($user->email);
        });
    }

    public function test_create_user_from_employee_requires_unique_email()
    {
        $this->actingAs($this->adminUser());

        $employee = $this->createEmployee('Ada Obi');

        User::factory()->create(['email' => 'ada.obi@brickspoint.ng']);

        Livewire::test(CreateUserFromEmployee::class)
            ->fillForm([
                'employee_id' => $employee->getKey(),
                'email' => 'ada.obi@brickspoint.ng',
                'password' => 'secret-pass-1234',
                'password_confirmation' => 'secret-pass-1234',
                'role' => 'staff',
            ])
            ->call('save')
            ->assertHasFormErrors(['email']);
    }

    public function test_legacy_user_sub_action_routes_are_removed()
    {
        $this->expectException(RouteNotFoundException::class);

        route('admin.users.assign-role');
    }
}
