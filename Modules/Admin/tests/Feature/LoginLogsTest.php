<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\UserLoginLog;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Admin\Filament\Resources\LoginLogs\Pages\ActiveSessions;
use Modules\Admin\Filament\Resources\LoginLogs\Pages\ListLoginLogs;
use Modules\Admin\Filament\Resources\LoginLogs\Widgets\LoginLogStatsOverview;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LoginLogsTest extends TestCase
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
            'login_logs.read',
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

    private function makeLog(array $overrides = []): UserLoginLog
    {
        return UserLoginLog::create(array_merge([
            'user_id' => $this->admin->id,
            'session_id' => 'sess-'.uniqid(),
            'ip_address' => '127.0.0.1',
            'browser' => 'Chrome',
            'platform' => 'Windows',
            'device_type' => 'desktop',
            'status' => 'success',
            'logged_in_at' => now()->subMinutes(10),
        ], $overrides));
    }

    public function test_login_logs_page_loads_with_stats_and_records()
    {
        $log = $this->makeLog();
        $this->makeLog(['status' => 'failed', 'browser' => 'Firefox']);

        Livewire::test(ListLoginLogs::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$log]);

        Livewire::test(LoginLogStatsOverview::class)
            ->assertSee('Logins Today')
            ->assertSee('Unique Users Today')
            ->assertSee('Active Now')
            ->assertSee('Failed Today');
    }

    public function test_login_logs_filters_by_user()
    {
        $otherUser = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $otherLog = $this->makeLog(['user_id' => $otherUser->id]);
        $adminLog = $this->makeLog();

        Livewire::test(ListLoginLogs::class)
            ->filterTable('user_id', $otherUser->id)
            ->assertCanSeeTableRecords([$otherLog])
            ->assertCanNotSeeTableRecords([$adminLog]);
    }

    public function test_login_logs_filters_by_status()
    {
        $success = $this->makeLog();
        $failed = $this->makeLog(['status' => 'failed']);

        Livewire::test(ListLoginLogs::class)
            ->filterTable('status', 'success')
            ->assertCanSeeTableRecords([$success])
            ->assertCanNotSeeTableRecords([$failed]);
    }

    public function test_login_logs_filters_by_device_type()
    {
        $desktop = $this->makeLog(['device_type' => 'desktop']);
        $mobile = $this->makeLog(['device_type' => 'mobile']);

        Livewire::test(ListLoginLogs::class)
            ->filterTable('device_type', 'mobile')
            ->assertCanSeeTableRecords([$mobile])
            ->assertCanNotSeeTableRecords([$desktop]);
    }

    public function test_login_logs_filters_by_date_range()
    {
        $today = $this->makeLog();
        $older = $this->makeLog(['logged_in_at' => now()->subDays(10)]);

        Livewire::test(ListLoginLogs::class)
            ->filterTable('date', [
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ])
            ->assertCanSeeTableRecords([$today])
            ->assertCanNotSeeTableRecords([$older]);
    }

    public function test_active_sessions_page_shows_active_only()
    {
        $active = $this->makeLog(['last_activity_at' => now()->subMinutes(2)]);
        // Logged out session must not appear.
        $this->makeLog(['logged_out_at' => now()->subMinutes(5)]);
        // Stale session still shows (live but inactive); legacy lists it in the same monitoring page.
        $stale = $this->makeLog(['last_activity_at' => now()->subHours(2)]);
        // Failed logins are not sessions.
        $this->makeLog(['status' => 'failed']);

        $component = Livewire::test(ActiveSessions::class);

        $component->assertOk();
        $component->assertCanSeeTableRecords([$active, $stale]);
    }

    public function test_login_logs_requires_authentication()
    {
        auth()->logout();

        $this->get('/admin/login-logs')
            ->assertRedirect('/admin/login');
    }

    public function test_login_logs_requires_admin_permission()
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->actingAs($user);

        // A staff account may enter the panel for its task board, but login logs
        // are admin-only, so the page refuses with a 403.
        $this->get('/admin/login-logs')->assertForbidden();
    }
}
