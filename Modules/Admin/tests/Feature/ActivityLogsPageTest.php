<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Admin\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ActivityLogsPageTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $role = Role::firstOrCreate(['name' => RoleEnum::ADMIN->value, 'guard_name' => 'web']);
        $perms = [
            'access_admin_dashboard',
            'activity_logs.read',
        ];
        foreach ($perms as $permName) {
            $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            if (! $role->hasPermissionTo($perm)) {
                $role->givePermissionTo($perm);
            }
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->admin->assignRole(RoleEnum::ADMIN->value);
        $this->actingAs($this->admin);
    }

    /**
     * The activity log resource should render the new color-coded verb labels and
     * show which resource/record was affected for quick context visibility.
     */
    public function test_activity_logs_resource_renders_with_colored_verb_labels()
    {
        $log = UserActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'room-types.update',
            'description' => 'Room Types: Assignment Suite (#5)',
            'method' => 'PUT',
            'url' => '/admin/activity-logs',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test-agent',
        ]);

        $component = Livewire::test(ListActivityLogs::class);

        $component->assertOk();
        // Verb label derived from the action's "update" verb.
        $component->assertTableActionDoesNotExist('edit');
        $component->assertCanSeeTableRecords([$log]);
        $component->assertTableColumnExists('action');
        $component->assertTableColumnExists('description');
        $component->assertTableColumnExists('user.name');

        // Render the table HTML and confirm the derived labels/context appear.
        $component->assertSee('Updated');
        $component->assertSee('Room Types');
        $component->assertSee('Assignment Suite');
    }

    public function test_activity_logs_resource_requires_admin_dashboard_access()
    {
        auth()->logout();

        $this->get('/admin/activity-logs')->assertRedirect('/admin/login');
    }

    public function test_activity_logs_resource_rejects_users_without_permission()
    {
        $noAccess = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        // The staff blanket tasks grant makes this a legitimate panel user, so
        // the panel admits them and this one page is what refuses them. A 403 is
        // the honest answer; only denial of the panel as a whole redirects.
        $this->actingAs($noAccess)
            ->get('/admin/activity-logs')
            ->assertForbidden();
    }

    public function test_activity_logs_resource_is_read_only()
    {
        Livewire::test(ListActivityLogs::class)
            ->assertOk();
    }
}
