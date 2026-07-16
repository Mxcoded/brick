<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
        $perm = Permission::firstOrCreate(['name' => 'access_admin_dashboard', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($perm)) {
            $role->givePermissionTo($perm);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->admin->assignRole(RoleEnum::ADMIN->value);
        $this->actingAs($this->admin);
    }

    /**
     * The activity log page should render the new color-coded verb labels and
     * show which resource/record was affected for quick context visibility.
     */
    public function test_activity_logs_page_renders_with_colored_verb_labels()
    {
        UserActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'room-types.update',
            'description' => 'Room Types: Assignment Suite (#5)',
            'method' => 'PUT',
            'url' => '/admin/activity-logs',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test-agent',
        ]);

        $response = $this->get(route('admin.activity-logs.index'));

        $response->assertOk();
        $response->assertSee('User Activity Logs');
        // Verb label derived from the action's "update" verb.
        $response->assertSee('Updated');
        // Resource context is shown for visibility.
        $response->assertSee('Room Types');
        // The affected record name is visible in the description.
        $response->assertSee('Assignment Suite');
    }
}
