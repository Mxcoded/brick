<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PermissionGateTest extends TestCase
{
    use DatabaseTransactions;

    private const ALL_PERMISSIONS = [
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

        'manage_users', 'manage_roles', 'manage_permissions', 'manage_settings',
        'users.create', 'users.read', 'users.update', 'users.delete', 'users.manage',
        'roles.create', 'roles.read', 'roles.update', 'roles.delete', 'roles.manage',
        'permissions.create', 'permissions.read', 'permissions.update', 'permissions.delete',
        'settings.update',

        'check_in_guest', 'check_out_guest',
        'guests.create', 'guests.read', 'guests.update', 'guests.delete', 'guests.manage',

        'view_employees', 'manage_employees', 'approve_leaves',
        'employees.create', 'employees.read', 'employees.update', 'employees.delete',
        'leaves.create', 'leaves.read', 'leaves.update', 'leaves.approve', 'leaves.manage',
        'leaves.apply-for-others',

        'tasks.create', 'tasks.read', 'tasks.update', 'tasks.delete', 'tasks.assign',

        'view_inventory', 'adjust_stock', 'manage_suppliers',
        'inventory.create', 'inventory.read', 'inventory.update', 'inventory.delete',
        'suppliers.create', 'suppliers.read', 'suppliers.update', 'suppliers.delete',
        'inventory.restock', 'inventory.transfer', 'inventory.usage', 'inventory.adjustments',
        'inventory.reports', 'inventory.export', 'inventory.scan',
        'purchase_orders.create', 'purchase_orders.approve', 'purchase_orders.cancel',
        'purchase_orders.receive',
        'stores.create', 'stores.read', 'stores.update', 'stores.delete',
        'departments.create', 'departments.read', 'departments.update', 'departments.delete',

        'take_orders', 'manage_menu',
        'orders.create', 'orders.read', 'orders.update', 'orders.delete',
        'menu.create', 'menu.read', 'menu.update', 'menu.delete',

        'view_tasks', 'assign_tasks', 'log_maintenance',
        'maintenance.create', 'maintenance.read', 'maintenance.update', 'maintenance.delete',

        'manage_banquet',
        'banquet.create', 'banquet.read', 'banquet.update', 'banquet.delete',

        'gym.manage',
        'gym.create',
        'gym.update',
        'gym.delete',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ValidateCsrfToken::class,
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::ALL_PERMISSIONS as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
    }

    private function createUser(): User
    {
        return User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);
    }

    private function assertBlocked(string $url, array $permissions): void
    {
        $user = $this->createUser();
        foreach ($permissions as $perm) {
            $user->givePermissionTo($perm);
        }
        $this->actingAs($user)->get($url)->assertStatus(403);
    }

    private function assertPasses(string $url, array $permissions): void
    {
        $user = $this->createUser();
        foreach ($permissions as $perm) {
            $user->givePermissionTo($perm);
        }
        $response = $this->actingAs($user)->get($url);
        $this->assertNotEquals(
            403,
            $response->getStatusCode(),
            "Gate blocked access to [$url] for user with permissions: ".implode(', ', $permissions)
        );
    }

    private function assertModuleAccess(string $url, string $permission): void
    {
        $this->assertBlocked($url, []);
        $this->assertPasses($url, [$permission]);
    }

    private function assertGranularAccess(string $url, string $groupPermission, array $extraPermissions): void
    {
        $this->assertBlocked($url, [$groupPermission]);
        $this->assertPasses($url, array_merge([$groupPermission], $extraPermissions));
    }

    // ==========================================
    // UNAUTHENTICATED & NO-PERM GUARD
    // ==========================================

    /** @test */
    public function unauthenticated_user_is_redirected_to_login()
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    /** @test */
    public function user_without_any_permission_cannot_access_any_module()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $this->get('/admin')->assertStatus(403);
        $this->get('/frontdesk/registrations')->assertStatus(403);
        $this->get('/restaurant-admin/dashboard')->assertStatus(403);
        $this->get('/gym')->assertStatus(403);
        $this->get('/inventory')->assertStatus(403);
        $this->get('/maintenance/dashboard')->assertStatus(403);
        $this->get('/tasks')->assertStatus(403);
        $this->get('/banquet')->assertStatus(403);
        $this->get('/website/admin')->assertStatus(403);
    }

    // ==========================================
    // MODULE ACCESS (10 permissions)
    // ==========================================

    /** @test */
    public function access_admin_dashboard()
    {
        $this->assertModuleAccess('/admin', 'access_admin_dashboard');
    }

    /** @test */
    public function access_frontdesk_dashboard()
    {
        $this->assertModuleAccess('/frontdesk/registrations', 'access_frontdesk_dashboard');
    }

    /** @test */
    public function access_staff_dashboard()
    {
        $this->assertBlocked('/staff', []);
        $this->assertBlocked('/staff', ['access_staff_dashboard']);
        $this->assertPasses('/staff', [
            'access_staff_dashboard', 'employees.read',
        ]);
    }

    /** @test */
    public function access_restaurant_dashboard()
    {
        $this->assertModuleAccess('/restaurant-admin/dashboard', 'access_restaurant_dashboard');
    }

    /** @test */
    public function access_gym_dashboard()
    {
        $this->assertModuleAccess('/gym', 'access_gym_dashboard');
    }

    /** @test */
    public function access_inventory_dashboard()
    {
        $this->assertModuleAccess('/inventory', 'access_inventory_dashboard');
    }

    /** @test */
    public function access_maintenance_dashboard()
    {
        $this->assertModuleAccess('/maintenance/dashboard', 'access_maintenance_dashboard');
    }

    /** @test */
    public function access_tasks_dashboard()
    {
        $this->assertModuleAccess('/tasks', 'access_tasks_dashboard');
    }

    /** @test */
    public function access_banquet_dashboard()
    {
        $this->assertModuleAccess('/banquet', 'access_banquet_dashboard');
    }

    /** @test */
    public function access_website_dashboard()
    {
        $this->assertModuleAccess('/website/admin', 'access_website_dashboard');
    }

    // ==========================================
    // STAFF GRANULAR PERMISSIONS
    // ==========================================

    /** @test */
    public function staff_employees_read()
    {
        $this->assertGranularAccess('/staff/export', 'access_staff_dashboard', ['employees.read']);
    }

    /** @test */
    public function staff_employees_create()
    {
        $this->assertGranularAccess('/staff/create', 'access_staff_dashboard', ['employees.create']);
    }

    // ==========================================
    // INVENTORY GRANULAR PERMISSIONS
    // ==========================================

    /** @test */
    public function inventory_create()
    {
        $this->assertGranularAccess('/inventory/items/create', 'access_inventory_dashboard', ['inventory.create']);
    }

    /** @test */
    public function inventory_read()
    {
        $this->assertGranularAccess('/inventory/barcode-labels', 'access_inventory_dashboard', ['inventory.read']);
    }

    /** @test */
    public function inventory_transfer()
    {
        $this->assertGranularAccess('/inventory/transfers', 'access_inventory_dashboard', ['inventory.transfer']);
    }

    /** @test */
    public function inventory_usage()
    {
        $this->assertGranularAccess('/inventory/usage', 'access_inventory_dashboard', ['inventory.usage']);
    }

    /** @test */
    public function inventory_adjustments()
    {
        $this->assertGranularAccess('/inventory/adjustments', 'access_inventory_dashboard', ['inventory.adjustments']);
    }

    /** @test */
    public function inventory_reports()
    {
        $this->assertGranularAccess('/inventory/report', 'access_inventory_dashboard', ['inventory.reports']);
    }

    /** @test */
    public function inventory_export()
    {
        $this->assertGranularAccess('/inventory/export/items', 'access_inventory_dashboard', ['inventory.export']);
    }

    /** @test */
    public function inventory_scan()
    {
        $this->assertGranularAccess('/inventory/scan', 'access_inventory_dashboard', ['inventory.scan']);
    }

    /** @test */
    public function suppliers_read()
    {
        $this->assertGranularAccess('/inventory/suppliers', 'access_inventory_dashboard', ['suppliers.read']);
    }

    /** @test */
    public function suppliers_create()
    {
        $this->assertGranularAccess('/inventory/suppliers/create', 'access_inventory_dashboard', ['suppliers.create']);
    }

    /** @test */
    public function purchase_orders_create()
    {
        $this->assertGranularAccess('/inventory/purchase-orders/create', 'access_inventory_dashboard', ['purchase_orders.create']);
    }

    /** @test */
    public function stores_read()
    {
        $this->assertGranularAccess('/inventory/stores', 'access_inventory_dashboard', ['stores.read']);
    }

    /** @test */
    public function stores_create()
    {
        $this->assertGranularAccess('/inventory/stores/create', 'access_inventory_dashboard', ['stores.create']);
    }

    /** @test */
    public function departments_read()
    {
        $this->assertGranularAccess('/inventory/departments', 'access_inventory_dashboard', ['departments.read']);
    }

    /** @test */
    public function departments_create()
    {
        $this->assertGranularAccess('/inventory/departments/create', 'access_inventory_dashboard', ['departments.create']);
    }

    // ==========================================
    // BANQUET GRANULAR PERMISSIONS
    // ==========================================

    /** @test */
    public function banquet_create()
    {
        $this->assertGranularAccess('/banquet/customers/create', 'access_banquet_dashboard', ['banquet.create']);
    }

    // ==========================================
    // MAINTENANCE GRANULAR PERMISSIONS
    // ==========================================

    /** @test */
    public function maintenance_create()
    {
        $user = $this->createUser();
        $this->actingAs($user)->get('/maintenance/create')->assertStatus(403);

        $this->assertPasses('/maintenance/create', ['access_maintenance_dashboard']);
        $this->assertPasses('/maintenance/create', ['maintenance.create']);
    }

    /** @test */
    public function maintenance_read()
    {
        $user = $this->createUser();
        $this->actingAs($user)->get('/maintenance/dashboard')->assertStatus(403);

        $this->assertPasses('/maintenance/dashboard', ['access_maintenance_dashboard']);
        $this->assertPasses('/maintenance/dashboard', ['maintenance.read']);
    }

    /** @test */
    public function maintenance_update()
    {
        $user = $this->createUser();
        $this->actingAs($user)->get('/maintenance/report')->assertStatus(403);

        $this->assertPasses('/maintenance/report', ['access_maintenance_dashboard']);
        $this->assertPasses('/maintenance/report', ['maintenance.read']);
    }

    // ==========================================
    // FRONT DESK GRANULAR PERMISSIONS
    // ==========================================

    /** @test */
    public function guests_read()
    {
        $this->assertGranularAccess('/frontdesk/guests', 'access_frontdesk_dashboard', ['guests.read']);
    }

    /** @test */
    public function guests_create()
    {
        $this->assertGranularAccess('/frontdesk/guests/create', 'access_frontdesk_dashboard', ['guests.create']);
    }

    // ==========================================
    // GYM GRANULAR PERMISSIONS
    // ==========================================

    /** @test */
    public function gym_granular_permissions_exist()
    {
        // Gym routes only gate on access_gym_dashboard at the group level.
        // gym.create/update/delete are used in blade @can checks.
        // Verify they can be granted and don't cause issues.
        $user = $this->createUser();
        $user->givePermissionTo(['access_gym_dashboard', 'gym.create', 'gym.update', 'gym.delete']);
        $response = $this->actingAs($user)->get('/gym');
        $this->assertNotEquals(403, $response->getStatusCode(), 'Gym gate blocked despite having access_gym_dashboard');
    }

    // ==========================================
    // CONTROLLER-LEVEL PERMISSION CHECKS
    // ==========================================

    // ==========================================
    // WEBSITE GRANULAR PAGES
    // ==========================================

    /** @test */
    public function website_newsletter_admin()
    {
        $this->assertBlocked('/website/admin/newsletter/campaigns', []);
        $this->assertPasses('/website/admin/newsletter/campaigns', ['access_website_dashboard']);
    }

    /** @test */
    public function website_settings_requires_dashboard_not_manage_settings()
    {
        // manage_settings is blade-only — route is gated by access_website_dashboard
        $this->assertBlocked('/website/admin/settings', []);
        $this->assertPasses('/website/admin/settings', ['access_website_dashboard']);
    }

    /** @test */
    public function website_subscribers_admin()
    {
        $this->assertBlocked('/website/admin/newsletter/subscribers', []);
        $this->assertPasses('/website/admin/newsletter/subscribers', ['access_website_dashboard']);
    }

    /** @test */
    public function website_contact_messages_admin()
    {
        $this->assertBlocked('/website/admin/contact-messages', []);
        $this->assertPasses('/website/admin/contact-messages', ['access_website_dashboard']);
    }

    /** @test */
    public function website_rooms_calendar()
    {
        $this->assertBlocked('/website/admin/calendar', []);
        $this->assertPasses('/website/admin/calendar', ['access_website_dashboard']);
    }

    /** @test */
    public function website_bookings_admin()
    {
        $this->assertBlocked('/website/admin/bookings', []);
        $this->assertPasses('/website/admin/bookings', ['access_website_dashboard']);
    }

    /** @test */
    public function website_room_types_admin()
    {
        $this->assertBlocked('/website/admin/room-types', []);
        $this->assertPasses('/website/admin/room-types', ['access_website_dashboard']);
    }

    /** @test */
    public function website_inventory_calendar_admin()
    {
        $this->assertBlocked('/website/admin/inventory', []);
        $this->assertPasses('/website/admin/inventory', ['access_website_dashboard']);
    }

    // ==========================================
    // CONTROLLER-LEVEL PERMISSION CHECKS
    // ==========================================

    /** @test */
    public function tasks_assign_is_checked_in_controller()
    {
        // The tasks.assign permission is checked via $user->can('tasks.assign')
        // in TasksController methods. This verifies the permission exists and
        // a user with access_tasks_dashboard can reach the create page.
        $this->assertPasses('/tasks/create', ['access_tasks_dashboard', 'tasks.create']);
    }

    /** @test */
    public function leaves_apply_for_others_is_checked_in_controller()
    {
        // leaves.apply-for-others is checked in LeaveController::submitLeaveForOther()
        // via $user->can('leaves.apply-for-others')
        $this->assertPasses('/staff/leaves/admin/apply', [
            'access_staff_dashboard',
        ]);
    }
}
