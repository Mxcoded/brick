<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use OwenIt\Auditing\Models\Audit;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuditTrailTest extends TestCase
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

    public function test_audit_trail_page_loads()
    {
        $response = $this->get(route('admin.audit-trails.index'));

        $response->assertOk();
        $response->assertSee('Field-Level Audit Trails');
        $response->assertSee('Audit Trails');
    }

    public function test_audit_trail_shows_field_changes()
    {
        $targetUser = User::factory()->create(['name' => 'Original Name']);
        $targetUser->update(['name' => 'Updated Name']);

        $response = $this->get(route('admin.audit-trails.index'));

        $response->assertOk();
        $response->assertSee('User');
        $response->assertSee('Original Name');
        $response->assertSee('Updated Name');
        $response->assertSee('Updated');
    }

    public function test_audit_trail_filters_by_event()
    {
        $targetUser = User::factory()->create();

        $response = $this->get(route('admin.audit-trails.index', ['event' => 'created']));

        $response->assertOk();
        $response->assertSee(class_basename($targetUser::class));

        $response = $this->get(route('admin.audit-trails.index', ['event' => 'deleted']));

        $response->assertOk();
    }

    public function test_audit_trail_filters_by_model_type()
    {
        User::factory()->create();

        $response = $this->get(route('admin.audit-trails.index', [
            'auditable_type' => 'App\\Models\\User',
        ]));

        $response->assertOk();
        $response->assertSee('User');
    }

    public function test_audit_trail_requires_authentication()
    {
        auth()->logout();

        $response = $this->get(route('admin.audit-trails.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_audit_trail_requires_admin_permission()
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->actingAs($user);

        $response = $this->get(route('admin.audit-trails.index'));

        $response->assertForbidden();
    }

    public function test_model_update_creates_audit_record()
    {
        $targetUser = User::factory()->create(['name' => 'Before Update']);

        Audit::where('auditable_type', User::class)
            ->where('auditable_id', $targetUser->id)
            ->delete();

        $targetUser->update(['name' => 'After Update']);

        $audit = Audit::where('auditable_type', User::class)
            ->where('auditable_id', $targetUser->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('updated', $audit->event);
        $this->assertEquals('Before Update', $audit->old_values['name'] ?? null);
        $this->assertEquals('After Update', $audit->new_values['name'] ?? null);
        $this->assertEquals($this->admin->id, $audit->user_id);
    }

    public function test_model_creation_creates_audit_record()
    {
        $targetUser = User::factory()->create(['name' => 'New User']);

        $audit = Audit::where('auditable_type', User::class)
            ->where('auditable_id', $targetUser->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('created', $audit->event);
        $this->assertEquals('New User', $audit->new_values['name'] ?? null);
    }

    public function test_model_deletion_creates_audit_record()
    {
        $targetUser = User::factory()->create(['name' => 'Deletable User']);
        $id = $targetUser->id;

        $targetUser->delete();

        $audit = Audit::where('auditable_type', User::class)
            ->where('auditable_id', $id)
            ->where('event', 'deleted')
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('deleted', $audit->event);
        $this->assertEquals('Deletable User', $audit->old_values['name'] ?? null);
    }

    public function test_search_filters_audits()
    {
        User::factory()->create(['name' => 'SearchableUser123']);

        $response = $this->get(route('admin.audit-trails.index', ['search' => 'SearchableUser123']));

        $response->assertOk();
        $response->assertSee('SearchableUser123');
    }

    public function test_restore_reverts_an_updated_record()
    {
        $target = User::factory()->create(['name' => 'Before Restore']);
        $target->update(['name' => 'After Restore']);

        $audit = Audit::where('auditable_type', User::class)
            ->where('auditable_id', $target->id)
            ->where('event', 'updated')
            ->firstOrFail();

        $response = $this->post(route('admin.audit-trails.restore', $audit->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('Before Restore', $target->fresh()->name);

        // The revert itself is recorded as a new audit entry.
        $this->assertNotNull(
            Audit::where('auditable_type', User::class)
                ->where('auditable_id', $target->id)
                ->where('event', 'updated')
                ->where('old_values', 'like', '%After Restore%')
                ->first()
        );
    }

    public function test_restore_is_blocked_for_created_events()
    {
        $target = User::factory()->create(['name' => 'Created For Block']);

        $audit = Audit::where('auditable_type', User::class)
            ->where('auditable_id', $target->id)
            ->where('event', 'created')
            ->firstOrFail();

        $response = $this->post(route('admin.audit-trails.restore', $audit->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_audit_trail_shows_restore_action_for_updated()
    {
        $target = User::factory()->create(['name' => 'Restore Button User']);
        $target->update(['name' => 'Changed Name']);

        $response = $this->get(route('admin.audit-trails.index'));

        $response->assertOk();
        $response->assertSee('Restore');
    }

    public function test_restore_requires_admin_permission()
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->actingAs($user);

        $target = User::factory()->create(['name' => 'Perm Check']);
        $target->update(['name' => 'Changed']);
        $audit = Audit::where('auditable_type', User::class)
            ->where('auditable_id', $target->id)
            ->where('event', 'updated')
            ->firstOrFail();

        $response = $this->post(route('admin.audit-trails.restore', $audit->id));

        $response->assertForbidden();
    }

    public function test_role_assignment_is_audited()
    {
        $target = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        $response = $this->post(route('admin.users.assign-role'), [
            'user_id' => $target->id,
            'roles' => ['admin'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $audit = Audit::where('auditable_type', User::class)
            ->where('auditable_id', $target->id)
            ->where('event', 'role-assigned')
            ->first();

        $this->assertNotNull($audit, 'Role assignment should create a custom audit entry.');
        $this->assertContains('admin', $audit->new_values['roles'] ?? []);
        $this->assertNotEquals($audit->old_values['roles'] ?? [], $audit->new_values['roles'] ?? []);
    }

    public function test_audit_trail_shows_role_assignment()
    {
        $target = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        $this->post(route('admin.users.assign-role'), [
            'user_id' => $target->id,
            'roles' => ['admin'],
        ]);

        $response = $this->get(route('admin.audit-trails.index'));

        $response->assertOk();
        $response->assertSee('Role Assigned');
        $response->assertSee('admin');
    }
}
