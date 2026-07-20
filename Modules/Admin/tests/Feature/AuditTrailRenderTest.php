<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Website\Models\Booking;
use Modules\Website\Models\Offer;
use Modules\Website\Models\OffersPage;
use OwenIt\Auditing\Models\Audit;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuditTrailRenderTest extends TestCase
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

    public function test_page_renders_all_expected_columns(): void
    {
        $response = $this->get(route('admin.audit-trails.index'));

        $response->assertOk();
        $response->assertSee('Field-Level Audit Trails');
        foreach (['Time', 'User', 'Event', 'Model', 'Changes', 'IP', 'URL', 'User Agent', 'Actions'] as $column) {
            $response->assertSee($column);
        }
    }

    public function test_row_renders_url_user_agent_and_ip(): void
    {
        // The audit resolver captures url / user_agent / ip_address on every entry.
        $target = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        $audit = new Audit([
            'user_type' => User::class,
            'user_id' => $this->admin->id,
            'event' => 'updated',
            'auditable_type' => User::class,
            'auditable_id' => $target->id,
            'old_values' => ['name' => 'Old Name'],
            'new_values' => ['name' => 'New Name'],
            'url' => 'http://localhost/admin/users/'.$target->id.'/edit',
            'ip_address' => '203.0.113.5',
            'user_agent' => 'Mozilla/5.0 AuditRenderTestAgent',
        ]);
        $audit->save();

        $response = $this->get(route('admin.audit-trails.index'));

        $response->assertOk();
        $response->assertSee('admin/users/'.$target->id.'/edit'); // captured URL
        $response->assertSee('AuditRenderTestAgent');                 // captured user agent
        $response->assertSee('203.0.113.5');                        // captured IP
    }

    public function test_anonymous_guest_booking_shows_email_instead_of_system(): void
    {
        $target = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        $audit = new Audit([
            'user_type' => null,
            'user_id' => null,
            'event' => 'created',
            'auditable_type' => Booking::class,
            'auditable_id' => 999,
            'old_values' => [],
            'new_values' => ['guest_email' => 'walkin@example.com'],
            'url' => 'http://localhost/booking',
            'ip_address' => '198.51.100.7',
            'user_agent' => 'Mozilla/5.0 GuestBrowser',
            'tags' => 'guest:walkin@example.com',
        ]);
        $audit->save();

        $response = $this->get(route('admin.audit-trails.index'));

        $response->assertOk();
        $response->assertSee('walkin@example.com'); // guest email shown as the actor
        $response->assertDontSee('>System<');        // not labelled System for this row
    }

    public function test_array_column_changes_are_captured_and_rendered(): void
    {
        // Relies on config/audit.php having allowed_array_values => true.
        $page = OffersPage::create([]);
        $offer = Offer::create([
            'offers_page_id' => $page->id,
            'title' => 'Spring Escape',
            'features' => ['wifi', 'breakfast'],
        ]);

        $audit = Audit::where('auditable_type', Offer::class)
            ->where('auditable_id', $offer->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($audit, 'Creating an Offer should produce an audit entry.');
        $this->assertArrayHasKey('features', $audit->new_values, 'Array/JSON column must be captured.');

        // laravel-auditing stores raw attribute values, so array casts arrive as JSON strings.
        $features = $audit->new_values['features'];
        if (is_string($features)) {
            $features = json_decode($features, true);
        }
        $this->assertEquals(['wifi', 'breakfast'], $features);

        $response = $this->get(route('admin.audit-trails.index'));

        $response->assertOk();
        $response->assertSee('Offer');   // model label
        $response->assertSee('features'); // changed array column is shown
        $response->assertSee('wifi');     // decoded array value is rendered
    }

    public function test_guest_is_redirected_away_from_audit_page(): void
    {
        auth()->logout();

        $response = $this->get(route('admin.audit-trails.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_is_forbidden(): void
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->actingAs($user);

        $response = $this->get(route('admin.audit-trails.index'));

        $response->assertForbidden();
    }
}
