<?php

namespace Modules\Tasks\Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Tasks\Support\TaskPermission;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Tasks is a working tool for everyone on staff, and a delegation tool only for
 * the roles that actually direct other people.
 *
 * The baseline is granted by the isStaff() blanket in AppServiceProvider rather
 * than per role, so these tests pin the two halves of that contract: staff can
 * always run their own work, and `tasks.assign` stays with the roles listed in
 * TaskPermission::DELEGATOR_ROLES and nowhere else.
 */
class TaskRoleMatrixTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();

        foreach (TaskPermission::all() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }

    #[Test]
    public function any_member_of_staff_can_track_their_own_work(): void
    {
        $user = $this->staff();

        $this->assertTrue($user->can(TaskPermission::DASHBOARD_ACCESS), 'staff should reach the module');
        $this->assertTrue($user->can(TaskPermission::READ), 'staff should see their tasks');
        $this->assertTrue($user->can(TaskPermission::CREATE), 'staff should raise tasks');
        $this->assertTrue($user->can(TaskPermission::UPDATE), 'staff should keep their tasks current');
    }

    #[Test]
    public function staff_cannot_delegate_to_somebody_else(): void
    {
        $user = $this->staff();

        $this->assertFalse($user->can(TaskPermission::ASSIGN));
    }

    #[Test]
    public function a_guest_gets_nothing(): void
    {
        $guest = User::factory()->create(['type' => 'guest', 'status' => 'active']);
        $guest->syncPermissions([]);

        foreach (TaskPermission::all() as $permission) {
            $this->assertFalse($guest->can($permission), "guest should not hold {$permission}");
        }
    }

    #[Test]
    public function every_internal_role_can_track_their_own_work(): void
    {
        foreach (Role::pluck('name') as $roleName) {
            if ($roleName === 'guest') {
                continue;
            }

            $user = $this->staff($roleName);

            $this->assertTrue(
                $user->can(TaskPermission::DASHBOARD_ACCESS),
                "role {$roleName} should reach the task module"
            );
            $this->assertTrue(
                $user->can(TaskPermission::UPDATE),
                "role {$roleName} should be able to update its own tasks"
            );
        }
    }

    #[Test]
    public function only_delegator_roles_can_assign_a_task_to_somebody_else(): void
    {
        $expected = TaskPermission::DELEGATOR_ROLES;

        foreach (Role::pluck('name') as $roleName) {
            $isAdmin = $roleName === 'admin';
            $shouldDelegate = $isAdmin || in_array($roleName, $expected, true);

            $user = $this->staff($roleName);

            $this->assertSame(
                $shouldDelegate,
                $user->can(TaskPermission::ASSIGN),
                "role {$roleName} assignment should be ".($shouldDelegate ? 'allowed' : 'denied')
            );
        }
    }

    #[Test]
    public function the_seeder_withdraws_assignment_from_a_role_removed_from_the_matrix(): void
    {
        $role = Role::where('name', 'waiter')->firstOrFail();
        $role->givePermissionTo(TaskPermission::ASSIGN);

        $this->assertTrue($role->fresh()->hasPermissionTo(TaskPermission::ASSIGN));

        (new RoleSeeder)->run();

        $this->assertFalse($role->fresh()->hasPermissionTo(TaskPermission::ASSIGN));
    }

    /**
     * A staff user on the given role, with no direct permissions of its own so
     * only the role and the staff blanket are in play.
     */
    protected function staff(?string $role = null): User
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $user->syncPermissions([]);

        if ($role !== null) {
            $user->assignRole(Role::findOrCreate($role, 'web'));
        }

        return $user->fresh();
    }
}
