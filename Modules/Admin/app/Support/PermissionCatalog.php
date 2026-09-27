<?php

namespace Modules\Admin\Support;

use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

/**
 * Turns flat permission names into a module/resource/action catalogue so the
 * role form can render a grouped CRUD permission matrix.
 *
 * The group set is derived from the module tree (each "module.json" under the
 * Modules directory) so a new module automatically appears here. Permission
 * names themselves follow the "{resource}.{action}" convention and carry the
 * owning module.
 */
class PermissionCatalog
{
    /** Recognised CRUD action suffixes and their display labels. */
    protected const CRUD_ACTIONS = [
        'create' => 'Create',
        'read' => 'View',
        'update' => 'Edit',
        'delete' => 'Delete',
    ];

    /** Module alias => group key when the permission prefix differs from the module alias. */
    protected const MODULE_ALIAS_GROUPS = [
        'frontdeskcrm' => 'frontdesk',
    ];

    /** Resource prefix (first dot segment) => group key for resources owned by a differently-named module. */
    protected const RESOURCE_GROUPS = [
        'users' => 'admin',
        'roles' => 'admin',
        'permissions' => 'admin',
        'settings' => 'admin',
        'payment_gateways' => 'admin',
        'login_logs' => 'admin',
        'activity_logs' => 'admin',
        'audit_trails' => 'admin',
        'guests' => 'frontdesk',
        'employees' => 'staff',
        'leaves' => 'staff',
        'departments' => 'staff',
        'menu' => 'restaurant',
        'orders' => 'restaurant',
        'suppliers' => 'inventory',
        'stores' => 'inventory',
        'purchase_orders' => 'inventory',
    ];

    /** Group key => display label overrides for the generic headline label. */
    protected const LABEL_OVERRIDES = [
        'frontdesk' => 'Front Desk',
        'staff' => 'HR & Staff',
        'inventory' => 'Inventory & Stock',
        'banquet' => 'Banquets & Events',
    ];

    /** Virtual groups that exist on top of the module scan (no module directory). */
    protected const VIRTUAL_GROUPS = [
        'procurement' => 'Procurement',
        'other' => 'Other',
    ];

    protected static ?array $groups = null;

    protected static bool $loaded = false;

    protected static ?array $definitions = null;

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

        foreach (static::definitions() as $key => $meta) {
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
            $group = static::hasGroup($module) ? $module : 'other';
            $resource = $group === 'other' ? 'Dashboards' : 'Dashboard';

            return self::descriptor($group, $resource, 'Access', 'access', "Access {$resource}");
        }

        $segments = explode('.', $name);

        if (count($segments) === 1) {
            $label = Str::headline(str_replace(['_', '-'], ' ', $name));

            return self::descriptor('other', $label, 'Manage All', 'manage', "Manage All {$label}");
        }

        $actionSegment = array_pop($segments);
        $first = $segments[0];
        $group = static::RESOURCE_GROUPS[$first] ?? (static::hasGroup($first) ? $first : 'other');

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

    /**
     * @return array<string, array{label: string}> group key => label
     */
    protected static function definitions(): array
    {
        if (static::$definitions !== null) {
            return static::$definitions;
        }

        $definitions = [];

        foreach (glob(base_path('Modules/*/module.json')) ?: [] as $file) {
            $meta = json_decode((string) file_get_contents($file), true);
            $alias = Str::lower((string) ($meta['alias'] ?? $meta['name'] ?? ''));

            if ($alias === '') {
                continue;
            }

            $key = static::MODULE_ALIAS_GROUPS[$alias] ?? $alias;
            $definitions[$key] = ['label' => static::LABEL_OVERRIDES[$key] ?? Str::headline($key)];
        }

        foreach (static::VIRTUAL_GROUPS as $key => $label) {
            $definitions[$key] = ['label' => $label];
        }

        return static::$definitions = $definitions;
    }

    protected static function hasGroup(string $key): bool
    {
        return isset(static::definitions()[$key]);
    }

    public static function groupLabel(string $groupKey): string
    {
        return static::definitions()[$groupKey]['label'] ?? $groupKey;
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
        static::$definitions = null;
    }
}
