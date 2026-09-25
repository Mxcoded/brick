<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Admin\Filament\Resources\AuditTrails\Pages\ListAuditTrails;
use Modules\Admin\Filament\Resources\Users\Pages\EditUser;
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
        Livewire::test(ListAuditTrails::class)
            ->assertOk();
    }

    public function test_audit_trail_shows_field_changes()
    {
        $targetUser = User::factory()->create(['name' => 'Original Name']);
        $targetUser->update(['name' => 'Updated Name']);

        $component = Livewire::test(ListAuditTrails::class);

        $component->assertOk();
        $component->assertSee('Original Name');
        $component->assertSee('Updated Name');
        $component->assertSee('Updated');
    }

    public function test_audit_trail_filters_by_event()
    {
        $targetUser = User::factory()->create();

        $component = Livewire::test(ListAuditTrails::class);

        $component->filterTable('event', 'created')
            ->assertOk()
            ->assertSee(class_basename($targetUser::class));

        $component->filterTable('event', 'deleted')
            ->assertOk();
    }

    public function test_audit_trail_filters_by_model_type()
    {
        User::factory()->create();

        Livewire::test(ListAuditTrails::class)
            ->filterTable('auditable_type', 'App\\Models\\User')
            ->assertOk()
            ->assertSee('User');
    }

    public function test_audit_trail_requires_authentication()
    {
        auth()->logout();

        $this->get('/admin/audit-trails')
            ->assertRedirect('/admin/login');
    }

    public function test_audit_trail_requires_admin_permission()
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->actingAs($user);

        $this->get('/admin/audit-trails')->assertForbidden();
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

        Livewire::test(ListAuditTrails::class)
            ->searchTable('SearchableUser123')
            ->assertOk()
            ->assertSee('SearchableUser123');
    }

    public function test_restore_reverts_an_updated_record()
    {
        $target = User::factory()->create(['name' => 'Before Restore']);
        $target->update(['name' => 'After Restore']);

        $audit = Audit::where('auditable_type', User::class)
            ->where('auditable_id', $target->id)
            ->where('event', 'updated')
            ->firstOrFail();

        Livewire::test(ListAuditTrails::class)
            ->callTableAction('restore', $audit->getKey())
            ->assertHasNoTableActionErrors();

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

        Livewire::test(ListAuditTrails::class)
            ->assertTableActionHidden('restore', $audit->getKey());
    }

    public function test_audit_trail_shows_restore_action_for_updated()
    {
        $target = User::factory()->create(['name' => 'Restore Button User']);
        $target->update(['name' => 'Changed Name']);

        $audit = Audit::where('auditable_type', User::class)
            ->where('auditable_id', $target->id)
            ->where('event', 'updated')
            ->firstOrFail();

        Livewire::test(ListAuditTrails::class)
            ->assertTableActionVisible('restore', $audit->getKey())
            ->assertTableActionHasLabel('restore', 'Restore');
    }

    public function test_restore_requires_admin_permission()
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->actingAs($user);

        $target = User::factory()->create(['name' => 'Perm Check']);
        $target->update(['name' => 'Changed']);

        $this->get('/admin/audit-trails')->assertForbidden();
    }

    public function test_role_assignment_is_audited()
    {
        $target = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        Livewire::test(EditUser::class, ['record' => $target->getKey()])
            ->fillForm(['roles' => [$adminRole->getKey()]])
            ->call('save')
            ->assertHasNoFormErrors();

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

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        Livewire::test(EditUser::class, ['record' => $target->getKey()])
            ->fillForm(['roles' => [$adminRole->getKey()]])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(ListAuditTrails::class)
            ->assertSee('Role Assigned')
            ->assertSee('admin');
    }
}
