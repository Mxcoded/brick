<?php

namespace Modules\Finance\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Spatie\Permission\PermissionRegistrar;

/**
 * Single source of truth for the Finance permission taxonomy.
 *
 * Finance is the system of record for money, so its permissions are split per
 * resource and per action instead of the flat `finance.create_journal` style
 * used originally. That split is what makes segregation of duties possible: a
 * junior accountant can read the ledger and export reports without being able
 * to draft, post or reverse a journal, while a senior accountant can.
 *
 * The legacy flat names are kept as aliases so permissions granted before the
 * split keep working during rollout. Aliases are only ever *additive* - nothing
 * widens access that the new granular name would not allow on its own.
 *
 * Every new Finance capability should be added here first, then referenced by
 * the Filament resources and pages via allows() rather than auth()->user()->can()
 * with a hardcoded string.
 */
class FinancePermission
{
    // ── Chart of accounts ────────────────────────────────────────────
    public const CHART_OF_ACCOUNTS_READ = 'finance.chart_of_accounts.read';

    public const CHART_OF_ACCOUNTS_CREATE = 'finance.chart_of_accounts.create';

    public const CHART_OF_ACCOUNTS_UPDATE = 'finance.chart_of_accounts.update';

    public const CHART_OF_ACCOUNTS_DELETE = 'finance.chart_of_accounts.delete';

    public const CHART_OF_ACCOUNTS_EXPORT = 'finance.chart_of_accounts.export';

    // ── Journal entries ──────────────────────────────────────────────
    public const JOURNAL_ENTRIES_READ = 'finance.journal_entries.read';

    public const JOURNAL_ENTRIES_CREATE = 'finance.journal_entries.create';

    public const JOURNAL_ENTRIES_UPDATE = 'finance.journal_entries.update';

    public const JOURNAL_ENTRIES_DELETE = 'finance.journal_entries.delete';

    public const JOURNAL_ENTRIES_POST = 'finance.journal_entries.post';

    public const JOURNAL_ENTRIES_REVERSE = 'finance.journal_entries.reverse';

    public const JOURNAL_ENTRIES_EXPORT = 'finance.journal_entries.export';

    // ── Fiscal years and periods ─────────────────────────────────────
    public const FISCAL_YEARS_READ = 'finance.fiscal_years.read';

    public const FISCAL_YEARS_CREATE = 'finance.fiscal_years.create';

    public const FISCAL_YEARS_UPDATE = 'finance.fiscal_years.update';

    public const FISCAL_YEARS_DELETE = 'finance.fiscal_years.delete';

    public const PERIODS_READ = 'finance.periods.read';

    public const PERIODS_UPDATE = 'finance.periods.update';

    // ── Ledger, reports and dashboard ────────────────────────────────
    public const LEDGER_READ = 'finance.ledger.read';

    public const LEDGER_EXPORT = 'finance.ledger.export';

    public const REPORTS_READ = 'finance.reports.read';

    public const REPORTS_EXPORT = 'finance.reports.export';

    public const DASHBOARD_ACCESS = 'access_finance_dashboard';

    /**
     * Resource => [action => permission], in the order the role editor should
     * display them. Keeping the order here means the permission matrix, the
     * seeders and the tests all agree on one order.
     *
     * @var array<string, array<string, string>>
     */
    public const TAXONOMY = [
        'chart_of_accounts' => [
            'read' => self::CHART_OF_ACCOUNTS_READ,
            'create' => self::CHART_OF_ACCOUNTS_CREATE,
            'update' => self::CHART_OF_ACCOUNTS_UPDATE,
            'delete' => self::CHART_OF_ACCOUNTS_DELETE,
            'export' => self::CHART_OF_ACCOUNTS_EXPORT,
        ],
        'journal_entries' => [
            'read' => self::JOURNAL_ENTRIES_READ,
            'create' => self::JOURNAL_ENTRIES_CREATE,
            'update' => self::JOURNAL_ENTRIES_UPDATE,
            'delete' => self::JOURNAL_ENTRIES_DELETE,
            'post' => self::JOURNAL_ENTRIES_POST,
            'reverse' => self::JOURNAL_ENTRIES_REVERSE,
            'export' => self::JOURNAL_ENTRIES_EXPORT,
        ],
        'fiscal_years' => [
            'read' => self::FISCAL_YEARS_READ,
            'create' => self::FISCAL_YEARS_CREATE,
            'update' => self::FISCAL_YEARS_UPDATE,
            'delete' => self::FISCAL_YEARS_DELETE,
        ],
        'periods' => [
            'read' => self::PERIODS_READ,
            'update' => self::PERIODS_UPDATE,
        ],
        'ledger' => [
            'read' => self::LEDGER_READ,
            'export' => self::LEDGER_EXPORT,
        ],
        'reports' => [
            'read' => self::REPORTS_READ,
            'export' => self::REPORTS_EXPORT,
        ],
    ];

    /**
     * Granular permission => pre-split flat names that used to grant it.
     *
     * A user holding a legacy permission keeps exactly the access it used to
     * imply, so upgrading the seeders does not silently lock anybody out.
     *
     * The export permissions are deliberately absent. Exporting is a new
     * capability: nothing in the old taxonomy could take the ledger away in a
     * spreadsheet, so aliasing a read or manage permission to it would hand
     * bulk data to people who previously only ever saw it on screen. The
     * seeder grants exports explicitly to the roles that should have them, and
     * anyone else has to be granted `*.export` on purpose.
     *
     * @var array<string, array<int, string>>
     */
    public const LEGACY_ALIASES = [
        self::CHART_OF_ACCOUNTS_READ => ['finance.view_coa'],
        self::CHART_OF_ACCOUNTS_CREATE => ['finance.manage_coa'],
        self::CHART_OF_ACCOUNTS_UPDATE => ['finance.manage_coa'],
        self::CHART_OF_ACCOUNTS_DELETE => ['finance.manage_coa'],

        self::JOURNAL_ENTRIES_READ => ['finance.create_journal', 'finance.post_journal'],
        self::JOURNAL_ENTRIES_CREATE => ['finance.create_journal'],
        self::JOURNAL_ENTRIES_UPDATE => ['finance.create_journal'],
        self::JOURNAL_ENTRIES_DELETE => ['finance.create_journal'],
        self::JOURNAL_ENTRIES_POST => ['finance.post_journal'],
        self::JOURNAL_ENTRIES_REVERSE => ['finance.post_journal'],

        self::FISCAL_YEARS_READ => ['finance.manage_periods'],
        self::FISCAL_YEARS_CREATE => ['finance.manage_periods'],
        self::FISCAL_YEARS_UPDATE => ['finance.manage_periods'],
        self::FISCAL_YEARS_DELETE => ['finance.manage_periods'],
        self::PERIODS_READ => ['finance.manage_periods'],
        self::PERIODS_UPDATE => ['finance.manage_periods'],

        self::LEDGER_READ => ['finance.view_ledger'],
        self::REPORTS_READ => ['finance.view_reports'],

        self::DASHBOARD_ACCESS => [
            'finance.view_coa',
            'finance.manage_coa',
            'finance.create_journal',
            'finance.post_journal',
            'finance.view_ledger',
            'finance.view_reports',
        ],
    ];

    /**
     * Read-only capabilities every Finance role starts from.
     *
     * @var array<int, string>
     */
    public const READ_ONLY = [
        self::DASHBOARD_ACCESS,
        self::CHART_OF_ACCOUNTS_READ,
        self::JOURNAL_ENTRIES_READ,
        self::FISCAL_YEARS_READ,
        self::PERIODS_READ,
        self::LEDGER_READ,
        self::REPORTS_READ,
    ];

    /**
     * Exporting is separated from reading so an audit trail can require
     * "may read the ledger" without also allowing bulk data exfiltration.
     *
     * @var array<int, string>
     */
    public const EXPORTS = [
        self::CHART_OF_ACCOUNTS_EXPORT,
        self::JOURNAL_ENTRIES_EXPORT,
        self::LEDGER_EXPORT,
        self::REPORTS_EXPORT,
    ];

    /**
     * Mutations reserved for a senior accountant. A junior accountant may read
     * and export everything but cannot move money in the ledger.
     *
     * @var array<int, string>
     */
    public const SENIOR_ONLY = [
        self::CHART_OF_ACCOUNTS_CREATE,
        self::CHART_OF_ACCOUNTS_UPDATE,
        self::CHART_OF_ACCOUNTS_DELETE,
        self::JOURNAL_ENTRIES_CREATE,
        self::JOURNAL_ENTRIES_UPDATE,
        self::JOURNAL_ENTRIES_DELETE,
        self::JOURNAL_ENTRIES_POST,
        self::JOURNAL_ENTRIES_REVERSE,
        self::FISCAL_YEARS_CREATE,
        self::FISCAL_YEARS_UPDATE,
        self::FISCAL_YEARS_DELETE,
        self::PERIODS_UPDATE,
    ];

    /** Role holding only the read-only set: the junior accountant tier. */
    public const ROLE_JUNIOR = 'finance';

    /** Role allowed to draft, post and reverse: the senior accountant tier. */
    public const ROLE_SENIOR = 'senior_accountant';

    /** Read-only observer tier used for internal and external audit. */
    public const ROLE_AUDITOR = 'auditor';

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_values(array_unique(array_merge(
            self::READ_ONLY,
            self::EXPORTS,
            self::SENIOR_ONLY,
        )));
    }

    /**
     * Every permission name the seeders must create, including the legacy
     * aliases so an existing database keeps validating against them.
     *
     * @return array<int, string>
     */
    public static function allIncludingLegacy(): array
    {
        $names = self::all();

        foreach (self::LEGACY_ALIASES as $aliases) {
            foreach ($aliases as $alias) {
                $names[] = $alias;
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * The distinct pre-split flat names, i.e. every permission that still
     * resolves as an alias to something.
     *
     * @return array<int, string>
     */
    public static function legacyNames(): array
    {
        return array_values(array_unique(array_merge(...array_values(self::LEGACY_ALIASES))));
    }

    /**
     * The permission set a Finance role should hold.
     *
     * @return array<int, string>
     */
    public static function forRole(string $role): array
    {
        return match ($role) {
            self::ROLE_SENIOR => array_merge(self::READ_ONLY, self::EXPORTS, self::SENIOR_ONLY),
            self::ROLE_JUNIOR => array_merge(self::READ_ONLY, self::EXPORTS),
            self::ROLE_AUDITOR => array_merge(self::READ_ONLY, self::EXPORTS),
            default => self::READ_ONLY,
        };
    }

    /**
     * Every name that should satisfy the given permission, including aliases.
     *
     * @return array<int, string>
     */
    public static function equivalentsFor(string $permission): array
    {
        return array_values(array_unique(array_merge(
            [$permission],
            self::LEGACY_ALIASES[$permission] ?? [],
        )));
    }

    /**
     * Can the given user perform this Finance capability?
     *
     * A user satisfies a permission through the granular name or any legacy
     * alias, and through a wildcard such as `finance.journal_entries.*`.
     */
    public static function can(?Authenticatable $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }

        $candidates = self::equivalentsFor($permission);
        $prefix = Str($permission)->beforeLast('.')->append('.*')->toString();

        foreach ($candidates as $candidate) {
            if ($user->can($candidate) || $user->can($prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Can the currently authenticated user perform this capability?
     */
    public static function allows(string $permission): bool
    {
        return self::can(auth()->user(), $permission);
    }

    /**
     * Can the user do any one of the listed capabilities?
     */
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
     * How the Admin role editor should label each permission.
     *
     * The generic PermissionCatalog would render `finance.journal_entries.post`
     * against a resource called "Finance" because it only special-cases the
     * website prefix. These overrides keep the module's own naming rules inside
     * the module instead of leaking Finance labels into Admin.
     *
     * @return array<string, array{group: string, resource: string, action: string, kind: string, label: string}>
     */
    public static function catalogOverrides(): array
    {
        $resourceLabels = [
            'chart_of_accounts' => 'Chart of Accounts',
            'journal_entries' => 'Journal Entries',
            'fiscal_years' => 'Fiscal Years',
            'periods' => 'Fiscal Periods',
            'ledger' => 'General Ledger',
            'reports' => 'Financial Reports',
        ];

        $actionLabels = [
            'read' => ['label' => 'View', 'kind' => 'crud'],
            'create' => ['label' => 'Create', 'kind' => 'crud'],
            'update' => ['label' => 'Edit', 'kind' => 'crud'],
            'delete' => ['label' => 'Delete', 'kind' => 'crud'],
            'post' => ['label' => 'Post to Ledger', 'kind' => 'action'],
            'reverse' => ['label' => 'Reverse', 'kind' => 'action'],
            'export' => ['label' => 'Export', 'kind' => 'action'],
        ];

        $overrides = [];

        foreach (self::TAXONOMY as $resource => $actions) {
            $resourceLabel = $resourceLabels[$resource] ?? Str($resource)->headline()->toString();

            foreach ($actions as $action => $permission) {
                $meta = $actionLabels[$action] ?? ['label' => Str($action)->headline()->toString(), 'kind' => 'action'];

                $overrides[$permission] = [
                    'group' => 'finance',
                    'resource' => $resourceLabel,
                    'action' => $meta['label'],
                    'kind' => $meta['kind'],
                    'label' => $meta['label'].' '.$resourceLabel,
                ];
            }
        }

        $overrides[self::DASHBOARD_ACCESS] = [
            'group' => 'finance',
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
