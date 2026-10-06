<?php

namespace Modules\Frontdeskcrm\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Spatie\Permission\PermissionRegistrar;

/**
 * Single source of truth for the Frontdesk CRM permission taxonomy.
 *
 * The module used to sit behind a single `access_frontdesk_dashboard` check,
 * with `guests.*` as the only finer-grained set. That is no longer enough once
 * the PMS screens are served as Filament resources, because the gate has to
 * answer a different question per screen: reading the guest directory is a
 * receptionist's job, but issuing an invoice, voiding one, posting a charge to
 * a folio or closing a stay are not.
 *
 * Guests already had granular permissions, so those are reused verbatim rather
 * than renamed - otherwise the Filament resource and the legacy Blade routes
 * would disagree about the same action.
 *
 * This class only describes *what* the capabilities are. *Who* holds them is
 * the role map in config/config.php (merged as `frontdeskcrm.roles`), so
 * changing who can void an invoice is a config change rather than a code change.
 */
class FrontdeskPermission
{
    // ── Module gate ───────────────────────────────────────────────────
    public const DASHBOARD_ACCESS = 'access_frontdesk_dashboard';

    // ── Guests (already existed; reused so both UIs agree) ─────────────
    public const GUESTS_READ = 'guests.read';

    public const GUESTS_CREATE = 'guests.create';

    public const GUESTS_UPDATE = 'guests.update';

    public const GUESTS_DELETE = 'guests.delete';

    public const GUESTS_MANAGE = 'guests.manage';

    public const GUESTS_CHECK_IN = 'guests.check_in';

    public const GUESTS_CHECK_OUT = 'guests.check_out';

    /**
     * Bulk-load the directory from a spreadsheet.
     *
     * Separate from GUESTS_CREATE because a 5,000-row import is a far bigger
     * commitment than adding one guest by hand, and an overwrite of existing
     * profiles is not the same risk as a single insert.
     */
    public const GUESTS_IMPORT = 'guests.import';

    // ── Registrations (the PMS stay record) ────────────────────────────
    public const REGISTRATIONS_READ = 'registrations.read';

    public const REGISTRATIONS_CREATE = 'registrations.create';

    public const REGISTRATIONS_UPDATE = 'registrations.update';

    public const REGISTRATIONS_DELETE = 'registrations.delete';

    /** Blanket hold over every registration capability. */
    public const REGISTRATIONS_MANAGE = 'registrations.manage';

    public const REGISTRATIONS_CHECK_IN = 'registrations.check_in';

    public const REGISTRATIONS_CHECK_OUT = 'registrations.check_out';

    /** Extend or shorten a stay after the guest has already arrived. */
    public const REGISTRATIONS_ADJUST_STAY = 'registrations.adjust_stay';

    /** Push a charge onto the guest's bill outside the normal flow. */
    public const REGISTRATIONS_POST_CHARGE = 'registrations.post_charge';

    // ── Folios (the guest's bill) ──────────────────────────────────────
    public const FOLIOS_READ = 'folios.read';

    public const FOLIOS_POST_CHARGE = 'folios.post_charge';

    /** Break one bill into several payers. */
    public const FOLIOS_SPLIT = 'folios.split';

    /** Settle and close the bill. */
    public const FOLIOS_CLOSE = 'folios.close';

    // ── Invoices (the financial record) ────────────────────────────────
    public const INVOICES_READ = 'invoices.read';

    public const INVOICES_CREATE = 'invoices.create';

    /** Make an invoice binding - the point money becomes receivable. */
    public const INVOICES_ISSUE = 'invoices.issue';

    /** Cancel an issued invoice. Rewrites the financial record. */
    public const INVOICES_VOID = 'invoices.void';

    /** Credit note against an issued invoice. Moves money back. */
    public const INVOICES_CREDIT_NOTE = 'invoices.credit_note';

    // ── Rate codes (priced inventory) ──────────────────────────────────
    public const RATE_CODES_READ = 'rate_codes.read';

    public const RATE_CODES_CREATE = 'rate_codes.create';

    public const RATE_CODES_UPDATE = 'rate_codes.update';

    public const RATE_CODES_DELETE = 'rate_codes.delete';

    // ── Guest types (segmentation + negotiated rates) ───────────────────
    public const GUEST_TYPES_READ = 'guest_types.read';

    public const GUEST_TYPES_CREATE = 'guest_types.create';

    public const GUEST_TYPES_UPDATE = 'guest_types.update';

    public const GUEST_TYPES_DELETE = 'guest_types.delete';

    // ── PMS settings (the back-office switches) ─────────────────────────
    public const PMS_SETTINGS_READ = 'pms.settings.read';

    /** Change what the scheduler automatically charges. */
    public const PMS_SETTINGS_UPDATE = 'pms.settings.update';

    /**
     * Resource => [action => permission], in the order the role editor should
     * display them. One order for the matrix, the seeders and the tests.
     *
     * @var array<string, array<string, string>>
     */
    public const TAXONOMY = [
        'guests' => [
            'read' => self::GUESTS_READ,
            'create' => self::GUESTS_CREATE,
            'update' => self::GUESTS_UPDATE,
            'delete' => self::GUESTS_DELETE,
            'manage' => self::GUESTS_MANAGE,
            'check_in' => self::GUESTS_CHECK_IN,
            'check_out' => self::GUESTS_CHECK_OUT,
            'import' => self::GUESTS_IMPORT,
        ],
        'registrations' => [
            'read' => self::REGISTRATIONS_READ,
            'create' => self::REGISTRATIONS_CREATE,
            'update' => self::REGISTRATIONS_UPDATE,
            'delete' => self::REGISTRATIONS_DELETE,
            'manage' => self::REGISTRATIONS_MANAGE,
            'check_in' => self::REGISTRATIONS_CHECK_IN,
            'check_out' => self::REGISTRATIONS_CHECK_OUT,
            'adjust_stay' => self::REGISTRATIONS_ADJUST_STAY,
            'post_charge' => self::REGISTRATIONS_POST_CHARGE,
        ],
        'folios' => [
            'read' => self::FOLIOS_READ,
            'post_charge' => self::FOLIOS_POST_CHARGE,
            'split' => self::FOLIOS_SPLIT,
            'close' => self::FOLIOS_CLOSE,
        ],
        'invoices' => [
            'read' => self::INVOICES_READ,
            'create' => self::INVOICES_CREATE,
            'issue' => self::INVOICES_ISSUE,
            'void' => self::INVOICES_VOID,
            'credit_note' => self::INVOICES_CREDIT_NOTE,
        ],
        'rate_codes' => [
            'read' => self::RATE_CODES_READ,
            'create' => self::RATE_CODES_CREATE,
            'update' => self::RATE_CODES_UPDATE,
            'delete' => self::RATE_CODES_DELETE,
        ],
        'guest_types' => [
            'read' => self::GUEST_TYPES_READ,
            'create' => self::GUEST_TYPES_CREATE,
            'update' => self::GUEST_TYPES_UPDATE,
            'delete' => self::GUEST_TYPES_DELETE,
        ],
        'pms_settings' => [
            'read' => self::PMS_SETTINGS_READ,
            'update' => self::PMS_SETTINGS_UPDATE,
        ],
    ];

    /**
     * Permissions the Filament resources need in order to be reachable at all,
     * used by the panel's resource gating.
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
     * Every permission name across every role in the config, plus the taxonomy.
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

        foreach ((array) config('frontdeskcrm.roles', []) as $grants) {
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
        $grants = config('frontdeskcrm.roles', []);

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
     * as `folios.*`?
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
     * reads as "Post a charge to a folio" rather than a raw dot-string.
     *
     * @return array<string, array{group: string, resource: string, action: string, kind: string, label: string}>
     */
    public static function catalogOverrides(): array
    {
        $resourceLabels = [
            'guests' => 'Guests',
            'registrations' => 'Registrations',
            'folios' => 'Folios',
            'invoices' => 'Invoices',
            'rate_codes' => 'Rate Codes',
            'guest_types' => 'Guest Types',
            'pms_settings' => 'PMS Settings',
        ];

        // Verb => display action. Kept as a table so the role editor says what
        // a manager would call the capability while the CRUD verbs stay
        // coloured as CRUD chips and the money-moving verbs stand out on their
        // own.
        $verbs = [
            'read' => ['View', 'crud'],
            'create' => ['Create', 'crud'],
            'update' => ['Edit', 'crud'],
            'delete' => ['Delete', 'crud'],
            'manage' => ['Manage All', 'manage'],
            'check_in' => ['Check In', 'action'],
            'check_out' => ['Check Out', 'action'],
            'import' => ['Import', 'action'],
            'adjust_stay' => ['Adjust Stay', 'action'],
            'post_charge' => ['Post Charge', 'action'],
            'split' => ['Split', 'action'],
            'close' => ['Close', 'action'],
            'issue' => ['Issue', 'action'],
            'void' => ['Void', 'action'],
            'credit_note' => ['Credit Note', 'action'],
        ];

        $overrides = [];

        foreach (self::TAXONOMY as $resource => $actions) {
            $resourceLabel = $resourceLabels[$resource] ?? Str($resource)->headline()->toString();

            foreach ($actions as $action => $permission) {
                [$label, $kind] = $verbs[$action] ?? [Str($action)->headline()->toString(), 'action'];

                $overrides[$permission] = [
                    'group' => 'frontdesk',
                    'resource' => $resourceLabel,
                    'action' => $label,
                    'kind' => $kind,
                    'label' => $label.' '.$resourceLabel,
                ];
            }
        }

        $overrides[self::DASHBOARD_ACCESS] = [
            'group' => 'frontdesk',
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
