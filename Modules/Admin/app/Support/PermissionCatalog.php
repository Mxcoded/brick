<?php

namespace Modules\Admin\Support;

use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

/**
 * Turns flat permission names into a module/resource/action catalogue so the
 * role form can render a grouped CRUD permission matrix.
 */
class PermissionCatalog
{
    /** Central 'group' definitions. Keep the key stable - used as a filter value. */
    protected const GROUPS = [
        'admin' => ['label' => 'Admin'],
        'frontdesk' => ['label' => 'Front Desk'],
        'staff' => ['label' => 'HR & Staff'],
        'tasks' => ['label' => 'Tasks'],
        'restaurant' => ['label' => 'Restaurant'],
        'inventory' => ['label' => 'Inventory & Stock'],
        'maintenance' => ['label' => 'Maintenance'],
        'banquet' => ['label' => 'Banquets & Events'],
        'gym' => ['label' => 'Gym'],
        'website' => ['label' => 'Website'],
        'procurement' => ['label' => 'Procurement'],
        'finance' => ['label' => 'Finance'],
        'contracts' => ['label' => 'Contracts'],
        'other' => ['label' => 'Other'],
    ];

    /** First dot-segment of a permission name => group key. */
    protected const PREFIX_GROUPS = [
        'users' => 'admin',
        'roles' => 'admin',
        'permissions' => 'admin',
        'settings' => 'admin',
        'guests' => 'frontdesk',
        'employees' => 'staff',
        'leaves' => 'staff',
        'departments' => 'staff',
        'tasks' => 'tasks',
        'menu' => 'restaurant',
        'orders' => 'restaurant',
        'inventory' => 'inventory',
        'suppliers' => 'inventory',
        'stores' => 'inventory',
        'purchase_orders' => 'inventory',
        'maintenance' => 'maintenance',
        'banquet' => 'banquet',
        'gym' => 'gym',
        'website' => 'website',
        'procurement' => 'procurement',
        'finance' => 'finance',
        'contracts' => 'contracts',
    ];

    protected const CRUD_ACTIONS = [
        'create' => 'Create',
        'read' => 'View',
        'update' => 'Edit',
        'delete' => 'Delete',
    ];

    /** Legacy (underscore only) permission => [group, resource, action, kind]. */
    protected const LEGACY_MAP = [
        'manage_users' => ['admin', 'Users', 'Manage All', 'manage'],
        'manage_roles' => ['admin', 'Roles', 'Manage All', 'manage'],
        'manage_permissions' => ['admin', 'Permissions', 'Manage All', 'manage'],
        'manage_settings' => ['admin', 'Settings', 'Manage All', 'manage'],
        'manage_payment_gateways' => ['admin', 'Payment Gateways', 'Manage All', 'manage'],
        'check_in_guest' => ['frontdesk', 'Guest Check-in', 'Perform', 'action'],
        'check_out_guest' => ['frontdesk', 'Guest Check-out', 'Perform', 'action'],
        'view_employees' => ['staff', 'Employees', 'View', 'crud'],
        'manage_employees' => ['staff', 'Employees', 'Manage All', 'manage'],
        'approve_leaves' => ['staff', 'Leaves', 'Approve', 'action'],
        'view_inventory' => ['inventory', 'Inventory', 'View', 'crud'],
        'adjust_stock' => ['inventory', 'Stock', 'Adjust', 'action'],
        'manage_suppliers' => ['inventory', 'Suppliers', 'Manage All', 'manage'],
        'take_orders' => ['restaurant', 'Orders', 'Take Orders', 'action'],
        'manage_menu' => ['restaurant', 'Menu', 'Manage All', 'manage'],
        'view_tasks' => ['maintenance', 'Maintenance Tasks', 'View', 'crud'],
        'assign_tasks' => ['maintenance', 'Maintenance Tasks', 'Assign', 'action'],
        'log_maintenance' => ['maintenance', 'Maintenance Log', 'Log', 'action'],
        'manage_banquet' => ['banquet', 'Banquets', 'Manage All', 'manage'],
    ];

    protected const MODULE_ALIAS_GROUPS = [
        'admin' => 'admin',
        'frontdesk' => 'frontdesk',
        'staff' => 'staff',
        'restaurant' => 'restaurant',
        'gym' => 'gym',
        'inventory' => 'inventory',
        'maintenance' => 'maintenance',
        'tasks' => 'tasks',
        'banquet' => 'banquet',
        'website' => 'website',
        'contracts' => 'contracts',
    ];

    protected static ?array $groups = null;

    protected static bool $loaded = false;

    /**
     * Grouped catalogue of every permission in the system.
     *
     * @return array<int, array{key: string, label: string, count: int, ids: array<int, int>, resources: array<int, array{name: string, permissions: array<int, array{id: int, name: string, action: string, kind: string, label: string}>}>}>
     */
    public static function groups(): array
    {
        if (static::$loaded) {
            return static::$groups ?? [];
        }

        $groups = [];

        foreach (static::GROUPS as $key => $meta) {
            $groups[$key] = [
                'key' => $key,
                'label' => $meta['label'],
                'count' => 0,
                'ids' => [],
                'resources' => [],
            ];
        }

        foreach (Permission::orderBy('name')->get(['id', 'name']) as $permission) {
            $descriptor = static::describe($permission->name);
            $groupKey = $descriptor['group'];

            $group = &$groups[$groupKey];
            $group['count']++;
            $group['ids'][] = (int) $permission->id;

            $resourceName = $descriptor['resource'];
            $resourceIndex = null;

            foreach ($group['resources'] as $index => $resource) {
                if ($resource['name'] === $resourceName) {
                    $resourceIndex = $index;
                    break;
                }
            }

            if ($resourceIndex === null) {
                $resourceIndex = count($group['resources']);
                $group['resources'][$resourceIndex] = [
                    'name' => $resourceName,
                    'permissions' => [],
                ];
            }

            $group['resources'][$resourceIndex]['permissions'][] = [
                'id' => (int) $permission->id,
                'name' => $permission->name,
                'action' => $descriptor['action'],
                'kind' => $descriptor['kind'],
                'label' => $descriptor['label'],
            ];
        }

        unset($group);

        static::$groups = collect($groups)
            ->filter(fn (array $group) => $group['count'] > 0)
            ->values()
            ->all();
        static::$loaded = true;

        return static::$groups;
    }

    /**
     * @return array{group: string, resource: string, action: string, kind: string, label: string}
     */
    public static function describe(string $name): array
    {
        if (str_starts_with($name, 'access_') && str_ends_with($name, '_dashboard')) {
            $module = substr($name, 7, -10);
            $group = static::MODULE_ALIAS_GROUPS[$module] ?? 'other';
            $resource = $group === 'other' ? 'Dashboards' : 'Dashboard';

            return self::descriptor($group, $resource, 'Access', 'access', "Access {$resource}");
        }

        if (isset(static::LEGACY_MAP[$name])) {
            [$group, $resource, $action, $kind] = static::LEGACY_MAP[$name];

            return self::descriptor($group, $resource, $action, $kind, static::labelFor($resource, $action, $kind));
        }

        $segments = explode('.', $name);

        if (count($segments) === 1) {
            $label = Str::headline(str_replace(['_', '-'], ' ', $name));

            return self::descriptor('other', $label, 'Manage All', 'manage', "Manage All {$label}");
        }

        $actionSegment = array_pop($segments);
        $first = $segments[0];
        $group = static::PREFIX_GROUPS[$first] ?? 'other';

        if (isset(static::CRUD_ACTIONS[$actionSegment])) {
            $action = static::CRUD_ACTIONS[$actionSegment];
            $kind = 'crud';
        } else {
            $action = Str::headline(str_replace(['_', '-'], ' ', $actionSegment));
            $kind = $actionSegment === 'manage' ? 'manage' : 'action';

            if ($kind === 'manage') {
                $action = 'Manage All';
            }
        }

        $resourceSegments = $first === 'website' ? array_slice($segments, 1) : [$first];
        $resource = Str::headline(str_replace(['_', '-'], ' ', implode(' ', $resourceSegments)));

        if ($resource === '') {
            $resource = Str::headline($name);
        }

        if ($group === 'other') {
            $resource = Str::headline($name);
        }

        return self::descriptor($group, $resource, $action, $kind, static::labelFor($resource, $action, $kind));
    }

    protected static function descriptor(string $group, string $resource, string $action, string $kind, string $label): array
    {
        return [
            'group' => $group,
            'resource' => $resource,
            'action' => $action,
            'kind' => $kind,
            'label' => $label,
        ];
    }

    public static function groupFor(string $name): string
    {
        return static::describe($name)['group'];
    }

    public static function groupLabel(string $groupKey): string
    {
        return static::GROUPS[$groupKey]['label'] ?? $groupKey;
    }

    /**
     * @return array<string, string> group key => label (ready for a Select filter)
     */
    public static function groupOptions(): array
    {
        return collect(static::groups())
            ->mapWithKeys(fn (array $group) => [$group['key'] => $group['label']])
            ->all();
    }

    /**
     * @return array<int, int>
     */
    public static function idsForGroup(string $groupKey): array
    {
        foreach (static::groups() as $group) {
            if ($group['key'] === $groupKey) {
                return $group['ids'];
            }
        }

        return [];
    }

    public static function count(): int
    {
        return array_reduce(static::groups(), fn (int $carry, array $group) => $carry + $group['count'], 0);
    }

    protected static function labelFor(string $resource, string $action, string $kind): string
    {
        if ($kind === 'manage') {
            return "Manage All {$resource}";
        }

        return "{$action} {$resource}";
    }

    public static function flush(): void
    {
        static::$groups = null;
        static::$loaded = false;
    }
}
