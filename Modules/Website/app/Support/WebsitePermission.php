<?php

namespace Modules\Website\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Spatie\Permission\PermissionRegistrar;

/**
 * Single source of truth for the Website module permission taxonomy.
 *
 * The admin screens used to be a set of Blade controllers under
 * `/website/admin/*`, each route carrying a `website.{resource}.{action}`
 * middleware string built by a closure in routes/web.php. Those names are
 * reused here verbatim, including the hyphens in `website.room-types.read` and
 * `website.contact-messages.read`.
 *
 * That is deliberate. The legacy routes stay registered alongside the Filament
 * resources, so renaming the permissions to the underscore style used elsewhere
 * would leave the two UIs checking different strings for the same action: a
 * role seeded from this taxonomy would pass the Filament gate and then be
 * refused by the route it was just linked from.
 *
 * This class only describes *what* the capabilities are. *Who* holds them is the
 * role map in config/config.php (merged as `website.roles`), so changing who can
 * delete a booking is a config change rather than a code change.
 *
 * Note on `contact-messages`: there is no create. An enquiry arrives from the
 * public contact form, so nobody administers one into being.
 */
class WebsitePermission
{
    // ── Module gate ───────────────────────────────────────────────────
    public const DASHBOARD_ACCESS = 'access_website_dashboard';

    // ── Bookings ──────────────────────────────────────────────────────
    public const BOOKINGS_READ = 'website.bookings.read';

    /**
     * Not granted to the website team by default.
     *
     * A booking is created by a guest completing checkout on the public site, so
     * a create capability here would only ever be used to fabricate one.
     */
    public const BOOKINGS_CREATE = 'website.bookings.create';

    /**
     * Covers confirming, cancelling, resending and assigning a room.
     *
     * The legacy routes gate all of those on `website.bookings.update`, so they
     * share one capability rather than splitting a single workflow across five.
     */
    public const BOOKINGS_UPDATE = 'website.bookings.update';

    /** Held by finance rather than the website team: it destroys the record. */
    public const BOOKINGS_DELETE = 'website.bookings.delete';

    // ── Testimonials (public reviews) ──────────────────────────────────
    public const TESTIMONIALS_READ = 'website.testimonials.read';

    /**
     * Held by nobody in the website team.
     *
     * A review is written by a guest. Publishing one is an update, moderating it
     * is an update, and deleting one removes someone's words from the site.
     */
    public const TESTIMONIALS_CREATE = 'website.testimonials.create';

    /** Approving a review is what puts it on the public site. */
    public const TESTIMONIALS_UPDATE = 'website.testimonials.update';

    public const TESTIMONIALS_DELETE = 'website.testimonials.delete';

    // ── Enquiries (contact messages) ───────────────────────────────────
    public const CONTACT_MESSAGES_READ = 'website.contact-messages.read';

    /** Reply, archive, reassign. There is no create; they arrive from the form. */
    public const CONTACT_MESSAGES_UPDATE = 'website.contact-messages.update';

    public const CONTACT_MESSAGES_DELETE = 'website.contact-messages.delete';

    // ── Newsletter campaigns ──────────────────────────────────────────
    public const NEWSLETTER_READ = 'website.newsletter.read';

    public const NEWSLETTER_CREATE = 'website.newsletter.create';

    public const NEWSLETTER_UPDATE = 'website.newsletter.update';

    public const NEWSLETTER_DELETE = 'website.newsletter.delete';

    // ── Subscribers ───────────────────────────────────────────────────
    public const SUBSCRIBERS_READ = 'website.subscribers.read';

    public const SUBSCRIBERS_CREATE = 'website.subscribers.create';

    public const SUBSCRIBERS_UPDATE = 'website.subscribers.update';

    public const SUBSCRIBERS_DELETE = 'website.subscribers.delete';

    // ── Room types (and the units under them) ──────────────────────────
    public const ROOM_TYPES_READ = 'website.room-types.read';

    public const ROOM_TYPES_CREATE = 'website.room-types.create';

    public const ROOM_TYPES_UPDATE = 'website.room-types.update';

    public const ROOM_TYPES_DELETE = 'website.room-types.delete';

    // ── Amenities ──────────────────────────────────────────────────────
    public const AMENITIES_READ = 'website.amenities.read';

    public const AMENITIES_CREATE = 'website.amenities.create';

    public const AMENITIES_UPDATE = 'website.amenities.update';

    public const AMENITIES_DELETE = 'website.amenities.delete';

    // ── Add-ons / upsells ─────────────────────────────────────────────
    public const ADDONS_READ = 'website.addons.read';

    public const ADDONS_CREATE = 'website.addons.create';

    public const ADDONS_UPDATE = 'website.addons.update';

    public const ADDONS_DELETE = 'website.addons.delete';

    // ── Page editors still served by the legacy UI ─────────────────────
    // Listed so the seeder and the role map cover the whole module, even though
    // these screens are not Filament resources yet.
    public const DINING_READ = 'website.dining.read';

    public const DINING_CREATE = 'website.dining.create';

    public const DINING_UPDATE = 'website.dining.update';

    public const DINING_DELETE = 'website.dining.delete';

    public const MEETING_READ = 'website.meeting.read';

    public const MEETING_CREATE = 'website.meeting.create';

    public const MEETING_UPDATE = 'website.meeting.update';

    public const MEETING_DELETE = 'website.meeting.delete';

    public const FACILITIES_READ = 'website.facilities.read';

    public const FACILITIES_CREATE = 'website.facilities.create';

    public const FACILITIES_UPDATE = 'website.facilities.update';

    public const FACILITIES_DELETE = 'website.facilities.delete';

    public const OFFERS_READ = 'website.offers.read';

    public const OFFERS_CREATE = 'website.offers.create';

    public const OFFERS_UPDATE = 'website.offers.update';

    public const OFFERS_DELETE = 'website.offers.delete';

    public const SETTINGS_READ = 'website.settings.read';

    public const SETTINGS_CREATE = 'website.settings.create';

    public const SETTINGS_UPDATE = 'website.settings.update';

    public const SETTINGS_DELETE = 'website.settings.delete';

    public const INVENTORY_READ = 'website.inventory.read';

    public const INVENTORY_CREATE = 'website.inventory.create';

    public const INVENTORY_UPDATE = 'website.inventory.update';

    public const INVENTORY_DELETE = 'website.inventory.delete';

    /**
     * Resource => [action => permission], in the order the role editor should
     * display them. One order for the matrix, the seeders and the tests.
     *
     * @var array<string, array<string, string>>
     */
    public const TAXONOMY = [
        'bookings' => [
            'read' => self::BOOKINGS_READ,
            'create' => self::BOOKINGS_CREATE,
            'update' => self::BOOKINGS_UPDATE,
            'delete' => self::BOOKINGS_DELETE,
        ],
        'testimonials' => [
            'read' => self::TESTIMONIALS_READ,
            'create' => self::TESTIMONIALS_CREATE,
            'update' => self::TESTIMONIALS_UPDATE,
            'delete' => self::TESTIMONIALS_DELETE,
        ],
        'contact-messages' => [
            'read' => self::CONTACT_MESSAGES_READ,
            'update' => self::CONTACT_MESSAGES_UPDATE,
            'delete' => self::CONTACT_MESSAGES_DELETE,
        ],
        'newsletter' => [
            'read' => self::NEWSLETTER_READ,
            'create' => self::NEWSLETTER_CREATE,
            'update' => self::NEWSLETTER_UPDATE,
            'delete' => self::NEWSLETTER_DELETE,
        ],
        'subscribers' => [
            'read' => self::SUBSCRIBERS_READ,
            'create' => self::SUBSCRIBERS_CREATE,
            'update' => self::SUBSCRIBERS_UPDATE,
            'delete' => self::SUBSCRIBERS_DELETE,
        ],
        'room-types' => [
            'read' => self::ROOM_TYPES_READ,
            'create' => self::ROOM_TYPES_CREATE,
            'update' => self::ROOM_TYPES_UPDATE,
            'delete' => self::ROOM_TYPES_DELETE,
        ],
        'amenities' => [
            'read' => self::AMENITIES_READ,
            'create' => self::AMENITIES_CREATE,
            'update' => self::AMENITIES_UPDATE,
            'delete' => self::AMENITIES_DELETE,
        ],
        'addons' => [
            'read' => self::ADDONS_READ,
            'create' => self::ADDONS_CREATE,
            'update' => self::ADDONS_UPDATE,
            'delete' => self::ADDONS_DELETE,
        ],
        'dining' => [
            'read' => self::DINING_READ,
            'create' => self::DINING_CREATE,
            'update' => self::DINING_UPDATE,
            'delete' => self::DINING_DELETE,
        ],
        'meeting' => [
            'read' => self::MEETING_READ,
            'create' => self::MEETING_CREATE,
            'update' => self::MEETING_UPDATE,
            'delete' => self::MEETING_DELETE,
        ],
        'facilities' => [
            'read' => self::FACILITIES_READ,
            'create' => self::FACILITIES_CREATE,
            'update' => self::FACILITIES_UPDATE,
            'delete' => self::FACILITIES_DELETE,
        ],
        'offers' => [
            'read' => self::OFFERS_READ,
            'create' => self::OFFERS_CREATE,
            'update' => self::OFFERS_UPDATE,
            'delete' => self::OFFERS_DELETE,
        ],
        'settings' => [
            'read' => self::SETTINGS_READ,
            'create' => self::SETTINGS_CREATE,
            'update' => self::SETTINGS_UPDATE,
            'delete' => self::SETTINGS_DELETE,
        ],
        'inventory' => [
            'read' => self::INVENTORY_READ,
            'create' => self::INVENTORY_CREATE,
            'update' => self::INVENTORY_UPDATE,
            'delete' => self::INVENTORY_DELETE,
        ],
    ];

    /**
     * Every permission name the taxonomy defines.
     *
     * @return array<int, string>
     */
    public static function all(): array
    {
        $names = [self::DASHBOARD_ACCESS];

        foreach (self::TAXONOMY as $actions) {
            foreach ($actions as $permission) {
                $names[] = $permission;
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Taxonomy plus anything the config map names, so a config entry is never
     * silently dropped for want of a taxonomy slot.
     *
     * @return array<int, string>
     */
    public static function allConfigured(): array
    {
        return array_values(array_unique(array_merge(
            self::all(),
            self::configuredNames()
        )));
    }

    /**
     * Distinct permission names referenced by the config role map.
     *
     * @return array<int, string>
     */
    public static function configuredNames(): array
    {
        $names = [];

        foreach ((array) config('website.roles', []) as $grants) {
            foreach ((array) $grants as $permission) {
                if ($permission !== '*') {
                    $names[] = $permission;
                }
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * The permissions a role should hold, per the module config.
     *
     * A role absent from the map gets nothing here. Access to the module shell
     * is a separate question handled by the dashboard permission.
     *
     * @return array<int, string>
     */
    public static function forRole(string $role): array
    {
        $grants = config('website.roles', []);

        if (! is_array($grants) || ! array_key_exists($role, $grants)) {
            return [];
        }

        $configured = (array) $grants[$role];

        if (in_array('*', $configured, true)) {
            return self::all();
        }

        // Only names that exist in the taxonomy, so a typo in config silently
        // drops a grant rather than creating an orphan permission.
        return array_values(array_intersect($configured, self::all()));
    }

    /**
     * Can the user perform this capability, directly or through a wildcard such
     * as `website.bookings.*`?
     */
    public static function can(?Authenticatable $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }

        $prefix = Str($permission)->beforeLast('.')->append('.*')->toString();

        return $user->can($permission) || $user->can($prefix);
    }

    public static function allows(string $permission): bool
    {
        return self::can(auth()->user(), $permission);
    }

    public static function allowsAny(string ...$permissions): bool
    {
        $user = auth()->user();

        foreach ($permissions as $permission) {
            if (self::can($user, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * How the Admin role editor should label each permission, so the matrix
     * reads as "Approve a testimonial" rather than a raw dot-string.
     *
     * @return array<string, array{group: string, resource: string, action: string, kind: string, label: string}>
     */
    public static function catalogOverrides(): array
    {
        $resourceLabels = [
            'bookings' => 'Bookings',
            'testimonials' => 'Testimonials',
            'contact-messages' => 'Enquiries',
            'newsletter' => 'Newsletters',
            'subscribers' => 'Subscribers',
            'room-types' => 'Room Types',
            'amenities' => 'Amenities',
            'addons' => 'Add-ons',
            'dining' => 'Dining',
            'meeting' => 'Meetings Page',
            'facilities' => 'Facilities Page',
            'offers' => 'Offers Page',
            'settings' => 'Website Settings',
            'inventory' => 'Inventory Calendar',
        ];

        // Verb => display action. Kept as a table so the role editor says what a
        // manager would call the capability while the CRUD verbs stay coloured as
        // CRUD chips.
        $verbs = [
            'read' => ['View', 'crud'],
            'create' => ['Create', 'crud'],
            'update' => ['Edit', 'crud'],
            'delete' => ['Delete', 'crud'],
        ];

        $overrides = [];

        foreach (self::TAXONOMY as $resource => $actions) {
            $resourceLabel = $resourceLabels[$resource] ?? Str($resource)->headline()->toString();

            foreach ($actions as $action => $permission) {
                [$label, $kind] = $verbs[$action] ?? [Str($action)->headline()->toString(), 'action'];

                $overrides[$permission] = [
                    'group' => 'website',
                    'resource' => $resourceLabel,
                    'action' => $label,
                    'kind' => $kind,
                    'label' => $label.' '.$resourceLabel,
                ];
            }
        }

        $overrides[self::DASHBOARD_ACCESS] = [
            'group' => 'website',
            'resource' => 'Dashboard',
            'action' => 'Access',
            'kind' => 'access',
            'label' => 'Access Dashboard',
        ];

        return $overrides;
    }

    /**
     * Drop the Spatie permission cache so newly seeded permissions are visible
     * without a manual `permission:cache-reset`.
     */
    public static function forgetCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
