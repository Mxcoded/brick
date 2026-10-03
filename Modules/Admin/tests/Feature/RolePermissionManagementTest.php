<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Admin\Filament\Resources\Permissions\PermissionResource;
use Modules\Admin\Filament\Resources\Roles\Pages\CreateRole;
use Modules\Admin\Filament\Resources\Roles\Pages\EditRole;
use Modules\Admin\Filament\Resources\Roles\RoleResource;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RolePermissionManagementTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $role = Role::firstOrCreate(['name' => RoleEnum::ADMIN->value, 'guard_name' => 'web']);

        foreach (['access_admin_dashboard', 'roles.manage', 'permissions.read'] as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            if (! $role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->admin->assignRole(RoleEnum::ADMIN->value);
        $this->actingAs($this->admin);
    }

    private function permission(string $name): Permission
    {
        return Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    public function test_role_form_renders_grouped_permission_matrix(): void
    {
        $this->permission('users.read');
        $this->permission('menu.create');

        Livewire::test(CreateRole::class)
            ->assertOk()
            ->assertSee('permissions selected')
            ->assertSee('Expand all')
            ->assertSee('Collapse all')
            ->assertSee('Admin')
            ->assertSee('Restaurant');
    }

    public function test_creating_role_with_matrix_syncs_permissions(): void
    {
        $usersRead = $this->permission('users.read');
        $access = $this->permission('access_admin_dashboard');
        $menuCreate = $this->permission('menu.create');

        Livewire::test(CreateRole::class)
            ->fillForm([
                'name' => 'catering_supervisor',
                'permissions' => [$usersRead->id, $access->id],
            ])
            ->call('create')
            ->assertHasNoErrors()
            ->assertRedirect();

        $role = Role::where('name', 'catering_supervisor')->firstOrFail();
        $this->assertTrue($role->hasPermissionTo($usersRead->name));
        $this->assertTrue($role->hasPermissionTo($access->name));
        $this->assertFalse($role->hasPermissionTo($menuCreate->name));
    }

    public function test_editing_role_replaces_permissions_via_matrix(): void
    {
        $usersRead = $this->permission('users.read');
        $menuDelete = $this->permission('menu.delete');

        $role = Role::create(['name' => 'catering_supervisor', 'guard_name' => 'web']);
        $role->syncPermissions([$usersRead->name]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Livewire::test(EditRole::class, ['record' => $role->getRouteKey()])
            ->assertOk()
            ->assertFormSet(['permissions' => [$usersRead->id]])
            ->assertSeeHtml('selected: JSON.parse(')
            ->assertSeeHtml((string) $usersRead->id)
            ->fillForm([
                'name' => 'catering_supervisor',
                'permissions' => [$menuDelete->id],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $role->refresh();
        $this->assertFalse($role->hasPermissionTo($usersRead->name));
        $this->assertTrue($role->hasPermissionTo($menuDelete->name));
    }

    public function test_staff_without_privilege_cannot_view_role_and_permission_resources(): void
    {
        $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $staff->assignRole($staffRole);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->actingAs($staff);

        // Neither screen redirects: role and permission management are admin-only
        // pages inside a panel this user can legitimately enter, so each one
        // refuses on its own with a 403.
        $this->get(RoleResource::getUrl('index'))->assertForbidden();
        $this->get(PermissionResource::getUrl('index'))->assertForbidden();
    }

    public function test_admin_can_view_role_and_permission_resources(): void
    {
        $this->get(RoleResource::getUrl('index'))->assertSuccessful();
        $this->get(PermissionResource::getUrl('index'))->assertSuccessful();
    }
}
