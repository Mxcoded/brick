<?php

namespace Modules\Admin\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Support\PermissionCatalog;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PermissionCatalogTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        PermissionCatalog::flush();

        DB::table('role_has_permissions')->delete();
        DB::table('model_has_permissions')->delete();
        DB::table('permissions')->delete();
    }

    protected function createPermissions(array $names): void
    {
        foreach ($names as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    public function test_every_permission_is_assigned_to_a_group(): void
    {
        $this->createPermissions([
            'users.create',
            'access_admin_dashboard',
            'orders.create',
            'website.bookings.read',
            'inventory.restock',
            'finance.post_journal',
            'payment_gateways.manage',
            'guests.check_in',
        ]);

        PermissionCatalog::flush();

        foreach (Permission::pluck('name') as $name) {
            $this->assertNotSame('', PermissionCatalog::groupFor($name), "Missing group for {$name}");
            $this->assertContains(PermissionCatalog::groupFor($name), array_keys(PermissionCatalog::groupOptions()), "Unknown group for {$name}");
        }
    }

    public function test_crud_permissions_are_grouped_by_module_and_resource(): void
    {
        $this->createPermissions([
            'users.create', 'users.read', 'users.update', 'users.delete', 'users.manage',
            'menu.create', 'menu.read', 'menu.update', 'menu.delete',
        ]);

        $groups = collect(PermissionCatalog::groups())->keyBy('key');

        $this->assertArrayHasKey('admin', $groups->all());
        $this->assertArrayHasKey('restaurant', $groups->all());

        $adminResources = collect($groups['admin']['resources'])->keyBy('name');
        $this->assertArrayHasKey('Users', $adminResources->all());

        $userActions = collect($adminResources['Users']['permissions'])->pluck('action')->all();
        $this->assertEqualsCanonicalizing(['Create', 'View', 'Edit', 'Delete', 'Manage All'], $userActions);
    }

    public function test_permissions_are_mapped_to_modules(): void
    {
        $this->createPermissions(['access_admin_dashboard', 'access_contracts_dashboard', 'users.manage', 'orders.create', 'payment_gateways.manage', 'guests.check_in', 'login_logs.read', 'activity_logs.read', 'audit_trails.read']);

        $this->assertSame('admin', PermissionCatalog::groupFor('access_admin_dashboard'));
        $this->assertSame('contracts', PermissionCatalog::groupFor('access_contracts_dashboard'));
        $this->assertSame('admin', PermissionCatalog::groupFor('users.manage'));
        $this->assertSame('admin', PermissionCatalog::groupFor('payment_gateways.manage'));
        $this->assertSame('admin', PermissionCatalog::groupFor('login_logs.read'));
        $this->assertSame('admin', PermissionCatalog::groupFor('activity_logs.read'));
        $this->assertSame('admin', PermissionCatalog::groupFor('audit_trails.read'));
        $this->assertSame('restaurant', PermissionCatalog::groupFor('orders.create'));
        $this->assertSame('frontdesk', PermissionCatalog::groupFor('guests.check_in'));
    }

    public function test_module_groups_are_derived_from_the_module_tree(): void
    {
        $this->createPermissions([
            'access_admin_dashboard',
            'access_frontdesk_dashboard',
            'access_staff_dashboard',
            'access_restaurant_dashboard',
            'access_gym_dashboard',
            'access_inventory_dashboard',
            'access_maintenance_dashboard',
            'access_tasks_dashboard',
            'access_banquet_dashboard',
            'access_website_dashboard',
            'access_contracts_dashboard',
            'finance.view_coa',
            'procurement.create_request',
        ]);

        $options = PermissionCatalog::groupOptions();

        // Derived from the active modules plus virtual groups.
        foreach (['admin', 'frontdesk', 'staff', 'restaurant', 'gym', 'inventory', 'maintenance', 'tasks', 'banquet', 'website', 'finance', 'contracts', 'procurement'] as $key) {
            $this->assertArrayHasKey($key, $options, "Missing derived group {$key}");
        }
    }

    public function test_website_permissions_strip_the_module_prefix(): void
    {
        $this->createPermissions(['website.bookings.read', 'website.room-types.delete']);

        $bookings = PermissionCatalog::describe('website.bookings.read');
        $this->assertSame('website', $bookings['group']);
        $this->assertSame('Bookings', $bookings['resource']);
        $this->assertSame('View', $bookings['action']);
        $this->assertSame('crud', $bookings['kind']);

        $roomTypes = PermissionCatalog::describe('website.room-types.delete');
        $this->assertSame('Room Types', $roomTypes['resource']);
        $this->assertSame('Delete', $roomTypes['action']);
    }

    public function test_group_options_and_ids_are_coherent(): void
    {
        $this->createPermissions(['users.read', 'menu.create', 'website.bookings.read']);

        $options = PermissionCatalog::groupOptions();
        $this->assertContains('admin', array_keys($options));
        $this->assertContains('restaurant', array_keys($options));
        $this->assertContains('website', array_keys($options));

        $adminIds = PermissionCatalog::idsForGroup('admin');
        $this->assertContains(Permission::where('name', 'users.read')->value('id'), $adminIds);
        $this->assertNotContains(Permission::where('name', 'menu.create')->value('id'), $adminIds);
    }
}
