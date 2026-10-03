<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Frontdeskcrm\Filament\Resources\FolioResource;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource;
use Modules\Frontdeskcrm\Filament\Resources\GuestTypeResource;
use Modules\Frontdeskcrm\Filament\Resources\InvoiceResource;
use Modules\Frontdeskcrm\Filament\Resources\RateCodeResource;
use Modules\Frontdeskcrm\Filament\Resources\RegistrationResource;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;
use Modules\Frontdeskcrm\Tests\Concerns\GrantsFrontdeskRoles;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * The PMS screens used to sit behind `access_frontdesk_dashboard` alone, so
 * anyone holding the module gate could open every Filament resource: the guest
 * directory, every registration, every folio and every invoice.
 *
 * The specific regression these tests exist to prevent is the Filament
 * migration: registering the resources in the shared panel without a
 * `canAccess()` on each one exposes all six screens to any authenticated user,
 * because Filament's default is "allow".
 */
class FrontdeskRoleMatrixTest extends TestCase
{
    use DatabaseTransactions, GrantsFrontdeskRoles;

    private User $receptionist;

    private User $finance;

    private User $auditor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->grantFrontdeskRole(RoleEnum::RECEPTIONIST->value);
        $this->grantFrontdeskRole(RoleEnum::FINANCE->value);
        $this->grantFrontdeskRole(RoleEnum::AUDITOR->value);

        $this->receptionist = $this->staffWith(RoleEnum::RECEPTIONIST->value);
        $this->finance = $this->staffWith(RoleEnum::FINANCE->value);
        $this->auditor = $this->staffWith(RoleEnum::AUDITOR->value);
    }

    private function staffWith(string $roleName): User
    {
        $user = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);
        $user->assignRole($roleName);

        return $user;
    }

    /**
     * The core guard: a user with no Frontdesk grant must not be able to reach
     * any of the six panel resources. Filament defaults to permitting these, so
     * without an explicit canAccess() every one of them is an open door.
     */
    public function test_a_user_without_frontdesk_permissions_reaches_no_pms_resource(): void
    {
        // A waiter holds real panel access elsewhere, so this is the realistic
        // shape of the bug: a legitimate user of the admin panel who has no
        // business reading guest folios.
        $this->grantFrontdeskPermissions('waiter', []);

        $waiter = $this->staffWith('waiter');

        $resources = [
            'guests',
            'registrations',
            'folios',
            'invoices',
            'rate-codes',
            'guest-types',
        ];

        foreach ($resources as $slug) {
            $this->actingAs($waiter)
                ->get("/admin/{$slug}")
                ->assertForbidden("/admin/{$slug} must be closed to a non-Frontdesk role");
        }
    }

    public function test_the_panel_hides_pms_resources_from_an_unprivileged_user(): void
    {
        $this->grantFrontdeskPermissions('store_keeper', []);

        $keeper = $this->staffWith('store_keeper');

        // canAccess() false means the resource is absent from the navigation as
        // well as unreachable, so the menu does not advertise the PMS to
        // somebody who cannot open it.
        $this->actingAs($keeper);

        foreach ([
            GuestResource::class,
            RegistrationResource::class,
            FolioResource::class,
            InvoiceResource::class,
            RateCodeResource::class,
            GuestTypeResource::class,
        ] as $resource) {
            $this->assertFalse(
                $resource::canAccess(),
                "{$resource} must not be accessible without a Frontdesk grant"
            );
        }
    }

    public function test_receptionist_runs_the_desk(): void
    {
        foreach ([
            FrontdeskPermission::DASHBOARD_ACCESS,
            FrontdeskPermission::GUESTS_READ,
            FrontdeskPermission::GUESTS_CREATE,
            FrontdeskPermission::GUESTS_UPDATE,
            FrontdeskPermission::GUESTS_DELETE,
            FrontdeskPermission::GUESTS_IMPORT,
            FrontdeskPermission::REGISTRATIONS_READ,
            FrontdeskPermission::REGISTRATIONS_CREATE,
            FrontdeskPermission::REGISTRATIONS_UPDATE,
            FrontdeskPermission::REGISTRATIONS_CHECK_IN,
            FrontdeskPermission::REGISTRATIONS_CHECK_OUT,
            FrontdeskPermission::FOLIOS_READ,
            FrontdeskPermission::FOLIOS_CLOSE,
            FrontdeskPermission::INVOICES_READ,
            FrontdeskPermission::INVOICES_ISSUE,
            FrontdeskPermission::RATE_CODES_READ,
            FrontdeskPermission::GUEST_TYPES_READ,
        ] as $permission) {
            $this->assertTrue(
                $this->receptionist->can($permission),
                "receptionist should hold {$permission}"
            );
        }
    }

    public function test_receptionist_cannot_rewrite_the_financial_record(): void
    {
        // Voiding an invoice and issuing a credit note both move money and
        // rewrite a receivable, so they sit with finance rather than the desk.
        // The receptionist can still issue an invoice - that is the desk's job.
        $this->assertFalse($this->receptionist->can(FrontdeskPermission::INVOICES_VOID));
        $this->assertFalse($this->receptionist->can(FrontdeskPermission::INVOICES_CREDIT_NOTE));
        $this->assertTrue($this->receptionist->can(FrontdeskPermission::INVOICES_ISSUE));
    }

    public function test_finance_touches_invoices_but_not_the_guest_directory(): void
    {
        foreach ([
            FrontdeskPermission::INVOICES_READ,
            FrontdeskPermission::INVOICES_CREATE,
            FrontdeskPermission::INVOICES_ISSUE,
            FrontdeskPermission::INVOICES_VOID,
            FrontdeskPermission::INVOICES_CREDIT_NOTE,
            FrontdeskPermission::FOLIOS_READ,
        ] as $permission) {
            $this->assertTrue(
                $this->finance->can($permission),
                "finance should hold {$permission}"
            );
        }

        // Checking a guest in needs no finance capability.
        $this->assertFalse($this->finance->can(FrontdeskPermission::GUESTS_UPDATE));
        $this->assertFalse($this->finance->can(FrontdeskPermission::REGISTRATIONS_UPDATE));
        $this->assertFalse($this->finance->can(FrontdeskPermission::GUESTS_DELETE));
    }

    public function test_auditor_is_read_only(): void
    {
        foreach (FrontdeskPermission::all() as $permission) {
            // The module gate is the door, not a capability: an auditor needs it
            // to reach the screens they are allowed to read. Everything that is
            // neither the gate nor a read is a write, and must be absent.
            if ($permission === FrontdeskPermission::DASHBOARD_ACCESS) {
                $this->assertTrue(
                    $this->auditor->can($permission),
                    'auditor needs the module gate to reach the read-only screens'
                );

                continue;
            }

            if (str_ends_with($permission, '.read')) {
                $this->assertTrue(
                    $this->auditor->can($permission),
                    "auditor should hold {$permission}"
                );

                continue;
            }

            $this->assertFalse(
                $this->auditor->can($permission),
                "auditor must not hold {$permission}"
            );
        }
    }

    public function test_admin_holds_the_whole_taxonomy(): void
    {
        $this->grantFrontdeskRole(RoleEnum::ADMIN->value);
        $admin = $this->staffWith(RoleEnum::ADMIN->value);

        foreach (FrontdeskPermission::all() as $permission) {
            $this->assertTrue(
                $admin->can($permission),
                "admin should hold {$permission}"
            );
        }
    }

    public function test_every_configured_grant_is_a_real_permission(): void
    {
        // Guards against a typo in the config map that silently grants nothing
        // and reads like it works.
        foreach (array_keys((array) config('frontdeskcrm.roles', [])) as $roleName) {
            foreach (FrontdeskPermission::forRole($roleName) as $permission) {
                $this->assertContains(
                    $permission,
                    FrontdeskPermission::all(),
                    "config grants {$roleName} the unknown permission {$permission}"
                );
            }
        }
    }

    public function test_config_only_names_permissions_the_taxonomy_defines(): void
    {
        foreach (FrontdeskPermission::configuredNames() as $name) {
            $this->assertContains(
                $name,
                FrontdeskPermission::all(),
                "config references {$name}, which the taxonomy does not define"
            );
        }
    }

    public function test_every_role_in_the_map_gets_the_module_gate(): void
    {
        // A role that lists real capabilities but not the gate would pass the
        // matrix tests and then 403 on the PMS shell in the browser.
        foreach (array_keys((array) config('frontdeskcrm.roles', [])) as $roleName) {
            $role = $this->grantFrontdeskRole($roleName);

            $this->assertTrue(
                $role->hasPermissionTo(FrontdeskPermission::DASHBOARD_ACCESS),
                "config names {$roleName} but it would not receive the module gate"
            );
        }
    }

    public function test_a_role_outside_the_map_keeps_nothing(): void
    {
        // Whatever else it holds, the PMS stays shut.
        $this->grantFrontdeskPermissions('line_manager', [
            FrontdeskPermission::DASHBOARD_ACCESS,
        ]);

        $manager = $this->staffWith('line_manager');

        $this->assertTrue($manager->can(FrontdeskPermission::DASHBOARD_ACCESS));
        $this->assertFalse($manager->can(FrontdeskPermission::GUESTS_READ));
        $this->assertFalse($manager->can(FrontdeskPermission::FOLIOS_READ));
        $this->assertFalse($manager->can(FrontdeskPermission::INVOICES_READ));
    }

    public function test_bulk_and_money_permissions_are_declared_in_config(): void
    {
        // Imports, voids and credit notes are the capabilities most likely to be
        // granted by accident, so they are asserted against the config list
        // rather than assumed from the taxonomy.
        $declared = (array) config('frontdeskcrm.export_permissions', []);

        $this->assertContains(FrontdeskPermission::GUESTS_IMPORT, $declared);
        $this->assertContains(FrontdeskPermission::INVOICES_VOID, $declared);
        $this->assertContains(FrontdeskPermission::INVOICES_CREDIT_NOTE, $declared);

        $this->assertFalse($this->auditor->can(FrontdeskPermission::GUESTS_IMPORT));
        $this->assertTrue($this->receptionist->can(FrontdeskPermission::GUESTS_IMPORT));
    }

    /**
     * Architectural guard, not a behavioural one.
     *
     * A module may only ever know *what* the capabilities are. Who holds them is
     * a deployment concern and belongs in config, so a role name appearing in
     * module PHP means someone has hardcoded the org chart into the code - the
     * exact thing that makes a role rename a code change instead of a config
     * change. This is the guard AGENTS.md describes for Restaurant, applied here.
     */
    public function test_no_role_name_is_hardcoded_in_module_php(): void
    {
        // `guest` is deliberately exempt. It is a RoleEnum case, but in a hotel
        // PMS it is also the module's core domain noun - "guest profiles",
        // "guest type", "guests.read" - so scanning for it produces nothing but
        // false positives. Every other role name is unambiguous and is checked.
        $ambiguous = ['guest'];

        $roleNames = array_values(array_diff(
            array_map(
                fn (RoleEnum $role): string => $role->value,
                RoleEnum::cases(),
            ),
            $ambiguous,
        ));

        $offenders = [];

        $files = new Finder;
        $files->files()->name('*.php')->in(module_path('Frontdeskcrm', 'app'));

        foreach ($files as $file) {
            $contents = $file->getContents();

            foreach ($roleNames as $roleName) {
                // Quoted or handed to hasRole()/hasAnyRole(), so 'receptionist',
                // "receptionist" and ->hasRole(receptionist) are all caught.
                $pattern = "/([\'\"]|hasRole\(|hasAnyRole\()\s*".preg_quote($roleName, '/')."\b/i";

                if (preg_match($pattern, $contents)) {
                    $offenders[] = $file->getRelativePathname().' mentions "'.$roleName.'"';
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Role names belong in config/frontdeskcrm.roles, not module PHP:\n".implode("\n", $offenders)
        );
    }
}
