<?php

namespace Modules\Website\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Website\Filament\Resources\AddonResource;
use Modules\Website\Filament\Resources\AmenityResource;
use Modules\Website\Filament\Resources\BookingResource;
use Modules\Website\Filament\Resources\ContactMessageResource;
use Modules\Website\Filament\Resources\NewsletterResource;
use Modules\Website\Filament\Resources\NewsletterSubscriberResource;
use Modules\Website\Filament\Resources\RoomTypeResource;
use Modules\Website\Filament\Resources\TestimonialResource;
use Modules\Website\Models\Booking;
use Modules\Website\Models\Testimonial;
use Modules\Website\Support\WebsitePermission;
use Modules\Website\Tests\Concerns\GrantsWebsiteRoles;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * The Website admin screens used to sit behind `access_website_dashboard` plus an
 * inline `website.{resource}.{action}` string built in routes/web.php. That is
 * workable for a route but not for a panel: Filament's default is to permit, so
 * registering these eight resources without a `canAccess()` on each one would
 * expose all of them to anyone who can reach the panel at all.
 *
 * The other thing these tests exist to prevent is the migration going the other
 * way - renaming the permissions. The legacy routes keep their hyphenated names,
 * so a role seeded from this taxonomy has to keep passing `permission:
 * website.room-types.update` on the routes that are still registered.
 */
class WebsiteRoleMatrixTest extends TestCase
{
    use DatabaseTransactions, GrantsWebsiteRoles;

    private User $websiteAdmin;

    private User $receptionist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->grantWebsiteRole(RoleEnum::WEBSITE_ADMIN->value);
        $this->grantWebsiteRole(RoleEnum::RECEPTIONIST->value);

        $this->websiteAdmin = $this->staffWith(RoleEnum::WEBSITE_ADMIN->value);
        $this->receptionist = $this->staffWith(RoleEnum::RECEPTIONIST->value);
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
     * Resource index URLs, read from the resources themselves.
     *
     * Hardcoding the slugs here would let the test pass while the resources moved,
     * because a wrong slug 403s/404s for a different reason and the assertion still
     * fails for the wrong one. Using getUrl() means the assertion is about access,
     * not about naming.
     *
     * @return array<int, class-string>
     */
    private function websiteResources(): array
    {
        return [
            BookingResource::class,
            TestimonialResource::class,
            ContactMessageResource::class,
            NewsletterResource::class,
            NewsletterSubscriberResource::class,
            RoomTypeResource::class,
            AmenityResource::class,
            AddonResource::class,
        ];
    }

    public function test_a_user_without_website_permissions_reaches_no_website_resource(): void
    {
        // The realistic shape of the bug: a legitimate user of the admin panel who
        // has no business reading guest enquiries. The role holds the module gate, so
        // it gets into the Website area's own page only if a capability is also
        // required - which is exactly what canAccess() asks.
        $this->actingAs($this->receptionist);

        foreach ($this->websiteResources() as $resource) {
            $url = $resource::getUrl();

            $this->assertFalse(
                $resource::canAccess(),
                "{$resource} must not be accessible without a Website grant"
            );

            $this->get($url)
                ->assertForbidden("{$url} must be closed to a non-Website role");
        }
    }

    public function test_a_user_without_website_permissions_cannot_reach_the_overview(): void
    {
        $this->actingAs($this->receptionist)
            ->get('/admin/website')
            ->assertForbidden();
    }

    public function test_the_panel_hides_website_resources_from_an_unprivileged_user(): void
    {
        // canAccess() false removes the resource from the navigation as well as
        // making it unreachable, so the menu does not advertise the Website area.
        $this->actingAs($this->receptionist);

        foreach ($this->websiteResources() as $resource) {
            $this->assertFalse($resource::canAccess());
        }
    }

    public function test_website_admin_runs_the_site(): void
    {
        $this->actingAs($this->websiteAdmin);

        foreach ($this->websiteResources() as $resource) {
            $this->assertTrue(
                $resource::canAccess(),
                "website_admin should reach {$resource}"
            );

            $this->get($resource::getUrl())->assertSuccessful();
        }

        $this->get('/admin/website')->assertSuccessful();
    }

    /**
     * A booking is the ledger's receivable. The website team needs to confirm,
     * cancel and assign rooms - all one capability, matching the legacy routes -
     * but not to remove the row, and not to fabricate one.
     */
    public function test_website_admin_may_operate_bookings_but_not_create_or_delete_them(): void
    {
        $this->actingAs($this->websiteAdmin);

        $this->assertTrue(BookingResource::canEdit(new Booking));
        $this->assertTrue($this->websiteAdmin->can(WebsitePermission::BOOKINGS_UPDATE));
        $this->assertTrue($this->websiteAdmin->can(WebsitePermission::BOOKINGS_READ));

        $this->assertFalse(BookingResource::canCreate());
        $this->assertFalse($this->websiteAdmin->can(WebsitePermission::BOOKINGS_CREATE));

        $this->assertFalse(BookingResource::canDelete(new Booking));
        $this->assertFalse($this->websiteAdmin->can(WebsitePermission::BOOKINGS_DELETE));
    }

    public function test_admin_holds_the_whole_taxonomy(): void
    {
        $this->grantWebsiteRole(RoleEnum::ADMIN->value);

        $admin = $this->staffWith(RoleEnum::ADMIN->value);
        $this->actingAs($admin);

        foreach (WebsitePermission::all() as $permission) {
            $this->assertTrue(
                $admin->can($permission),
                "admin should hold {$permission}"
            );
        }

        $this->assertTrue(BookingResource::canCreate());
        $this->assertTrue(TestimonialResource::canCreate());
    }

    /**
     * Reviews are written by guests. Nobody in the website team types one in.
     */
    public function test_website_admin_may_moderate_reviews_but_not_write_them(): void
    {
        $this->actingAs($this->websiteAdmin);

        $this->assertTrue(TestimonialResource::canEdit(new Testimonial));
        $this->assertFalse(TestimonialResource::canCreate());
    }

    /**
     * Enquiries arrive from the public contact form, so there is no create
     * capability at all - not in the taxonomy and not on the resource.
     */
    public function test_enquiries_cannot_be_created(): void
    {
        $this->actingAs($this->websiteAdmin);

        $this->assertFalse(ContactMessageResource::canCreate());
        $this->assertArrayNotHasKey('create', WebsitePermission::TAXONOMY['contact-messages']);

        // There is no create page to reach, so the URL is not merely forbidden - it
        // does not exist. That is the stronger guarantee: there is no form to find.
        $this->get('/admin/enquiries/create')->assertNotFound();
    }

    public function test_a_role_outside_the_map_keeps_nothing(): void
    {
        $this->grantWebsitePermissions('gm', [
            WebsitePermission::DASHBOARD_ACCESS,
        ]);

        $gm = $this->staffWith('gm');

        $this->assertTrue($gm->can(WebsitePermission::DASHBOARD_ACCESS));
        $this->assertFalse($gm->can(WebsitePermission::BOOKINGS_READ));
        $this->assertFalse($gm->can(WebsitePermission::CONTACT_MESSAGES_READ));
        $this->assertFalse($gm->can(WebsitePermission::ROOM_TYPES_UPDATE));
    }

    /**
     * The hyphenated names are load-bearing. They are what the surviving legacy
     * routes check in their middleware, so the taxonomy must keep emitting them
     * exactly or a role seeded here gets refused by the route it was linked from.
     */
    public function test_the_taxonomy_keeps_the_legacy_permission_spellings(): void
    {
        $expected = [
            WebsitePermission::BOOKINGS_READ,
            WebsitePermission::BOOKINGS_UPDATE,
            WebsitePermission::TESTIMONIALS_READ,
            WebsitePermission::CONTACT_MESSAGES_READ,
            WebsitePermission::NEWSLETTER_READ,
            WebsitePermission::SUBSCRIBERS_READ,
            WebsitePermission::ROOM_TYPES_READ,
            WebsitePermission::AMENITIES_READ,
            WebsitePermission::ADDONS_READ,
        ];

        foreach ($expected as $permission) {
            $this->assertMatchesRegularExpression(
                '/^website\.[a-z]+(-[a-z]+)?\.(read|create|update|delete)$/',
                $permission
            );
        }

        $this->assertSame('website.room-types.read', WebsitePermission::ROOM_TYPES_READ);
        $this->assertSame('website.contact-messages.read', WebsitePermission::CONTACT_MESSAGES_READ);
    }

    public function test_every_configured_grant_is_a_real_permission(): void
    {
        foreach (WebsitePermission::allConfigured() as $name) {
            $this->assertContains(
                $name,
                WebsitePermission::all(),
                "config references {$name}, which the taxonomy does not define"
            );
        }
    }

    public function test_config_only_names_permissions_the_taxonomy_defines(): void
    {
        foreach (WebsitePermission::configuredNames() as $name) {
            $this->assertContains(
                $name,
                WebsitePermission::all(),
                "config references {$name}, which the taxonomy does not define"
            );
        }
    }

    public function test_every_role_in_the_map_gets_the_module_gate(): void
    {
        // A role that lists real capabilities but not the gate would pass these
        // tests and then be redirected out of the panel in the browser.
        foreach (array_keys((array) config('website.roles', [])) as $roleName) {
            $role = $this->grantWebsiteRole($roleName);

            $this->assertTrue(
                $role->hasPermissionTo(WebsitePermission::DASHBOARD_ACCESS),
                "config names {$roleName} but it would not receive the module gate"
            );
        }
    }

    public function test_the_dangerous_capabilities_are_declared_in_config(): void
    {
        // Destroying a booking or writing a review by hand are the grants most
        // likely to be handed out by accident, so they are asserted against the
        // config list rather than inferred from the taxonomy.
        $websiteAdminGrants = (array) config('website.roles.'.RoleEnum::WEBSITE_ADMIN->value, []);

        $this->assertNotContains(WebsitePermission::BOOKINGS_DELETE, $websiteAdminGrants);
        $this->assertNotContains(WebsitePermission::BOOKINGS_CREATE, $websiteAdminGrants);
        $this->assertNotContains(WebsitePermission::TESTIMONIALS_CREATE, $websiteAdminGrants);

        $this->assertFalse($this->websiteAdmin->can(WebsitePermission::BOOKINGS_DELETE));
        $this->assertFalse($this->websiteAdmin->can(WebsitePermission::TESTIMONIALS_CREATE));
    }

    /**
     * Architectural guard, not a behavioural one.
     *
     * A module may only ever know *what* the capabilities are. Who holds them is a
     * deployment concern and belongs in config, so a role name appearing in module
     * PHP means someone has hardcoded the org chart into the code - the exact thing
     * that makes a role rename a code change instead of a config change.
     */
    public function test_no_role_name_is_hardcoded_in_module_php(): void
    {
        // `guest` is deliberately exempt. It is a RoleEnum case, but in a website
        // module it is also the module's core domain noun - "guest enquiries",
        // "guest_name", "guest reviews" - so scanning for it produces nothing but
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
        $files->files()->name('*.php')->in(module_path('Website', 'app'));

        foreach ($files as $file) {
            $contents = $file->getContents();

            foreach ($roleNames as $roleName) {
                // Quoted, case-sensitive. Role names are lowercase by convention, so
                // a capital-A 'Admin' is a namespace segment (Controllers\Admin) and
                // not the admin role. Matching it would flag every legacy controller
                // in the module for the directory it lives in.
                $quoted = '/[\'"]\s*'.preg_quote($roleName, '/').'\b/';

                // Handed to hasRole()/hasAnyRole(), case-insensitively, because
                // ->hasRole(RoleEnum::ADMIN) and ->hasRole('ADMIN') are both real.
                $predicate = '/(hasRole\(|hasAnyRole\()\s*'.preg_quote($roleName, '/').'\b/i';

                if (preg_match($quoted, $contents) || preg_match($predicate, $contents)) {
                    $offenders[] = $file->getRelativePathname().' mentions "'.$roleName.'"';
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Role names belong in Modules/Website/config/config.php, not module PHP:\n".implode("\n", $offenders)
        );
    }
}
