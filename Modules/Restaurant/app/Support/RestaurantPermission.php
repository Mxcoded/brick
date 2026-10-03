<?php

namespace Modules\Restaurant\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Spatie\Permission\PermissionRegistrar;

/**
 * Single source of truth for the Restaurant permission taxonomy.
 *
 * The back office used to sit behind a single `access_restaurant_dashboard`
 * check, which meant anyone who could see the module could also delete menu
 * items, redraw the floor plan, adjust stock, read every customer and export
 * the sales and waiter-performance reports. The names below split those apart
 * per resource and per action.
 *
 * This class only describes *what* the capabilities are. *Who* holds them is
 * the role map in config/restaurant.php, so changing an org chart is a config
 * change rather than a code change.
 *
 * Two deliberate gaps:
 *
 * - Reporting is read-only. Exports are separate permissions (see the
 *   `export_permissions` config key) so granting someone a report does not
 *   hand them the whole dataset in a spreadsheet.
 * - Settings and refunds are manager-level. Changing tax or service charge
 *   settings rewrites what every future invoice means, and a refund moves
 *   money, so neither is something a waiter can reach.
 */
class RestaurantPermission
{
    // ── Menu ──────────────────────────────────────────────────────────
    public const MENU_CATEGORIES_READ = 'menu_categories.read';

    public const MENU_CATEGORIES_CREATE = 'menu_categories.create';

    public const MENU_CATEGORIES_UPDATE = 'menu_categories.update';

    public const MENU_CATEGORIES_DELETE = 'menu_categories.delete';

    public const MENU_CATEGORIES_RESTORE = 'menu_categories.restore';

    public const MENU_ITEMS_READ = 'menu_items.read';

    public const MENU_ITEMS_CREATE = 'menu_items.create';

    public const MENU_ITEMS_UPDATE = 'menu_items.update';

    public const MENU_ITEMS_DELETE = 'menu_items.delete';

    public const MENU_ITEMS_RESTORE = 'menu_items.restore';

    // ── Floor plan ────────────────────────────────────────────────────
    public const TABLES_READ = 'tables.read';

    public const TABLES_CREATE = 'tables.create';

    public const TABLES_UPDATE = 'tables.update';

    public const TABLES_DELETE = 'tables.delete';

    // ── Sales points ──────────────────────────────────────────────────
    public const SALES_POINTS_READ = 'sales_points.read';

    public const SALES_POINTS_CREATE = 'sales_points.create';

    public const SALES_POINTS_UPDATE = 'sales_points.update';

    public const SALES_POINTS_DELETE = 'sales_points.delete';

    // ── Collection points ─────────────────────────────────────────────
    public const COLLECTION_POINTS_READ = 'collection_points.read';

    public const COLLECTION_POINTS_CREATE = 'collection_points.create';

    public const COLLECTION_POINTS_UPDATE = 'collection_points.update';

    public const COLLECTION_POINTS_DELETE = 'collection_points.delete';

    // ── Orders ────────────────────────────────────────────────────────
    public const ORDERS_READ = 'orders.read';

    public const ORDERS_CREATE = 'orders.create';

    public const ORDERS_UPDATE = 'orders.update';

    public const ORDERS_DELETE = 'orders.delete';

    /** Move an order out of 'pending' into the kitchen. */
    public const ORDERS_ACCEPT = 'orders.accept';

    public const ORDERS_REJECT = 'orders.reject';

    /** Void an order that should never have been taken. */
    public const ORDERS_VOID = 'orders.void';

    // ── Payments ──────────────────────────────────────────────────────
    public const PAYMENTS_READ = 'payments.read';

    public const PAYMENTS_CREATE = 'payments.create';

    /** Reverses a completed payment and its ledger entry. */
    public const PAYMENTS_REFUND = 'payments.refund';

    // ── Kitchen display ───────────────────────────────────────────────
    public const KITCHEN_READ = 'kitchen.read';

    public const KITCHEN_UPDATE = 'kitchen.update';

    // ── Stock and recipes ─────────────────────────────────────────────
    public const STOCK_READ = 'stock.read';

    public const STOCK_CREATE = 'stock.create';

    public const STOCK_UPDATE = 'stock.update';

    public const STOCK_DELETE = 'stock.delete';

    /** Bring a trashed ingredient back; StockItem soft-deletes. */
    public const STOCK_RESTORE = 'stock.restore';

    /** Adjust, consume or transfer stock. */
    public const STOCK_MOVEMENTS = 'stock.movements';

    public const RECIPES_READ = 'recipes.read';

    public const RECIPES_CREATE = 'recipes.create';

    public const RECIPES_DELETE = 'recipes.delete';

    // ── Customers ─────────────────────────────────────────────────────
    public const CUSTOMERS_READ = 'customers.read';

    public const CUSTOMERS_CREATE = 'customers.create';

    public const CUSTOMERS_UPDATE = 'customers.update';

    // ── Reports ───────────────────────────────────────────────────────
    public const REPORTS_SALES_READ = 'reports.sales.read';

    public const REPORTS_POPULAR_ITEMS_READ = 'reports.popular_items.read';

    public const REPORTS_WAITER_PERFORMANCE_READ = 'reports.waiter_performance.read';

    public const REPORTS_SHIFT_READ = 'reports.shift.read';

    public const REPORTS_SALES_EXPORT = 'reports.sales.export';

    public const REPORTS_POPULAR_ITEMS_EXPORT = 'reports.popular_items.export';

    // ── Settings and dashboard ────────────────────────────────────────
    public const SETTINGS_READ = 'settings.read';

    public const SETTINGS_UPDATE = 'settings.update';

    public const DASHBOARD_ACCESS = 'access_restaurant_dashboard';

    /**
     * Resource => [action => permission], in the order the role editor should
     * display them. One order for the matrix, the seeders and the tests.
     *
     * @var array<string, array<string, string>>
     */
    public const TAXONOMY = [
        'menu_categories' => [
            'read' => self::MENU_CATEGORIES_READ,
            'create' => self::MENU_CATEGORIES_CREATE,
            'update' => self::MENU_CATEGORIES_UPDATE,
            'delete' => self::MENU_CATEGORIES_DELETE,
            'restore' => self::MENU_CATEGORIES_RESTORE,
        ],
        'menu_items' => [
            'read' => self::MENU_ITEMS_READ,
            'create' => self::MENU_ITEMS_CREATE,
            'update' => self::MENU_ITEMS_UPDATE,
            'delete' => self::MENU_ITEMS_DELETE,
            'restore' => self::MENU_ITEMS_RESTORE,
        ],
        'tables' => [
            'read' => self::TABLES_READ,
            'create' => self::TABLES_CREATE,
            'update' => self::TABLES_UPDATE,
            'delete' => self::TABLES_DELETE,
        ],
        'sales_points' => [
            // Read is held by the waiter because the terminal has to show which
            // counter they are on. The rest decide which revenue account backs
            // that counter, which is a management call.
            'read' => self::SALES_POINTS_READ,
            'create' => self::SALES_POINTS_CREATE,
            'update' => self::SALES_POINTS_UPDATE,
            'delete' => self::SALES_POINTS_DELETE,
        ],
        'collection_points' => [
            // Where an order is collected rather than sold. Read is held by the
            // waiter for the same reason as sales points: the terminal has to
            // show which counter they are collecting from. The rest decide
            // which revenue account backs that counter.
            'read' => self::COLLECTION_POINTS_READ,
            'create' => self::COLLECTION_POINTS_CREATE,
            'update' => self::COLLECTION_POINTS_UPDATE,
            'delete' => self::COLLECTION_POINTS_DELETE,
        ],
        'orders' => [
            'read' => self::ORDERS_READ,
            'create' => self::ORDERS_CREATE,
            'update' => self::ORDERS_UPDATE,
            'delete' => self::ORDERS_DELETE,
            'accept' => self::ORDERS_ACCEPT,
            'reject' => self::ORDERS_REJECT,
            'void' => self::ORDERS_VOID,
        ],
        'payments' => [
            'read' => self::PAYMENTS_READ,
            'create' => self::PAYMENTS_CREATE,
            'refund' => self::PAYMENTS_REFUND,
        ],
        'kitchen' => [
            'read' => self::KITCHEN_READ,
            'update' => self::KITCHEN_UPDATE,
        ],
        'stock' => [
            'read' => self::STOCK_READ,
            'create' => self::STOCK_CREATE,
            'update' => self::STOCK_UPDATE,
            'delete' => self::STOCK_DELETE,
            'restore' => self::STOCK_RESTORE,
            'movements' => self::STOCK_MOVEMENTS,
        ],
        'recipes' => [
            'read' => self::RECIPES_READ,
            'create' => self::RECIPES_CREATE,
            'delete' => self::RECIPES_DELETE,
        ],
        'customers' => [
            'read' => self::CUSTOMERS_READ,
            'create' => self::CUSTOMERS_CREATE,
            'update' => self::CUSTOMERS_UPDATE,
        ],
        'reports' => [
            // The key is the action, not the resource qualifier, so these read
            // as "View" chips under the Reports resource rather than as four
            // one-off actions.
            'read' => self::REPORTS_SALES_READ,
            'read_popular_items' => self::REPORTS_POPULAR_ITEMS_READ,
            'read_waiter_performance' => self::REPORTS_WAITER_PERFORMANCE_READ,
            'read_shift' => self::REPORTS_SHIFT_READ,
            'export_sales' => self::REPORTS_SALES_EXPORT,
            'export_popular_items' => self::REPORTS_POPULAR_ITEMS_EXPORT,
        ],
        'settings' => [
            'read' => self::SETTINGS_READ,
            'update' => self::SETTINGS_UPDATE,
        ],
    ];

    /**
     * Every permission name the seeders must create.
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
     * Used to keep the permissions table in step with the role map so a grant
     * can never reference a name that was never registered.
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

        foreach ((array) config('restaurant.roles', []) as $grants) {
            foreach ((array) $grants as $permission) {
                if ($permission !== '*') {
                    $names[] = $permission;
                }
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * The permissions a role should hold, per config/restaurant.php.
     *
     * A role absent from the map gets nothing here. Access to the module shell
     * is a separate question handled by the dashboard permission.
     *
     * @return array<int, string>
     */
    public static function forRole(string $role): array
    {
        $grants = config('restaurant.roles', []);

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
     * The export permissions declared in config.
     *
     * @return array<int, string>
     */
    public static function exportPermissions(): array
    {
        return (array) config('restaurant.export_permissions', []);
    }

    /**
     * Can the user perform this capability, directly or through a wildcard such
     * as `orders.*`?
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
        foreach ($permissions as $permission) {
            if (self::allows($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * How the Admin role editor should label each permission, so the matrix
     * reads as "Manage menu items" rather than a raw dot-string.
     *
     * @return array<string, array{group: string, resource: string, action: string, kind: string, label: string}>
     */
    public static function catalogOverrides(): array
    {
        $resourceLabels = [
            'menu_categories' => 'Menu Categories',
            'menu_items' => 'Menu Items',
            'tables' => 'Dining Tables',
            'sales_points' => 'Sales Points',
            'collection_points' => 'Collection Points',
            'orders' => 'Orders',
            'payments' => 'Payments',
            'kitchen' => 'Kitchen Display',
            'stock' => 'Restaurant Stock',
            'recipes' => 'Recipes',
            'customers' => 'Customers',
            'reports' => 'Reports',
            'settings' => 'Settings',
        ];

        // Verb => display action. Keeping the labels as a table rather than
        // deriving them means the role editor says what a manager would call the
        // capability, and the CRUD verbs stay coloured as CRUD chips while
        // accept/reject/void/refund render as their own capabilities.
        $verbs = [
            'read' => ['View', 'crud'],
            'create' => ['Create', 'crud'],
            'update' => ['Edit', 'crud'],
            'delete' => ['Delete', 'crud'],
            'restore' => ['Restore', 'crud'],
            'accept' => ['Accept', 'action'],
            'reject' => ['Reject', 'action'],
            'void' => ['Void', 'action'],
            'refund' => ['Refund', 'action'],
            'movements' => ['Record Movement', 'action'],
            'read_popular_items' => ['View Popular Items', 'crud'],
            'read_waiter_performance' => ['View Waiter Performance', 'crud'],
            'read_shift' => ['View Shift', 'crud'],
            'export_sales' => ['Export Sales', 'action'],
            'export_popular_items' => ['Export Popular Items', 'action'],
        ];

        $overrides = [];

        foreach (self::TAXONOMY as $resource => $actions) {
            $resourceLabel = $resourceLabels[$resource] ?? Str($resource)->headline()->toString();

            foreach ($actions as $action => $permission) {
                [$label, $kind] = $verbs[$action] ?? [Str($action)->headline()->toString(), 'action'];

                $overrides[$permission] = [
                    'group' => 'restaurant',
                    'resource' => $resourceLabel,
                    'action' => $label,
                    'kind' => $kind,
                    'label' => $label.' '.$resourceLabel,
                ];
            }
        }

        $overrides[self::DASHBOARD_ACCESS] = [
            'group' => 'restaurant',
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
