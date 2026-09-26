<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Support\PermissionAliases;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RbacReconciliationTest extends TestCase
{
    use DatabaseTransactions;

    private function seedRoles(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        \DB::table('model_has_permissions')->delete();
        \DB::table('role_has_permissions')->delete();
        \DB::table('permissions')->delete();

        $this->seed(RoleSeeder::class);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function test_every_enum_role_exists_after_core_seed(): void
    {
        $this->seedRoles();

        foreach (RoleEnum::cases() as $case) {
            $this->assertDatabaseHas('roles', ['name' => $case->value, 'guard_name' => 'web']);
        }
    }

    public function test_every_seeded_role_is_backed_by_the_enum(): void
    {
        $this->seedRoles();

        $enumNames = collect(RoleEnum::cases())->pluck('value')->all();

        foreach (Role::pluck('name')->all() as $name) {
            $this->assertContains($name, $enumNames, "Role '{$name}' exists in the DB but not in RoleEnum.");
        }
    }

    public function test_contracts_manager_role_is_seeded_and_backed_by_the_enum(): void
    {
        $this->seedRoles();

        $this->assertNotNull(RoleEnum::tryFrom('contracts_manager'));
        $this->assertDatabaseHas('roles', ['name' => RoleEnum::CONTRACTS_MANAGER->value, 'guard_name' => 'web']);
    }

    public function test_every_alias_target_is_a_seeded_permission(): void
    {
        $this->seedRoles();

        foreach (PermissionAliases::ALIASES as $granular) {
            foreach ($granular as $name) {
                $this->assertDatabaseHas('permissions', ['name' => $name, 'guard_name' => 'web']);
            }
        }
    }

    public function test_no_legacy_underscore_permissions_are_seeded(): void
    {
        $this->seedRoles();

        foreach (array_keys(PermissionAliases::ALIASES) as $legacy) {
            $this->assertDatabaseMissing('permissions', ['name' => $legacy, 'guard_name' => 'web']);
        }
    }

    public function test_granular_grant_satisfies_the_legacy_alias(): void
    {
        $this->seedRoles();

        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $user->givePermissionTo('menu.read');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->assertTrue($user->can('manage_menu'));
    }
}
