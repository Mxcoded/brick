<?php

namespace App\Support;

/**
 * Bridges the legacy underscore permission names (e.g. `manage_users`) and the
 * granular dot-style names (e.g. `users.*`) so a user granted one spelling may
 * use either. Any permission name (Blade @can, route middleware, controllers)
 * is therefore interchangeable with its equivalent.
 */
class PermissionAliases
{
    /**
     * Legacy permission => granular equivalents. The legacy name stays a valid
     * ability on its own; these are the GRC-native synonyms it now implies.
     */
    public const ALIASES = [
        'manage_users' => ['users.create', 'users.read', 'users.update', 'users.delete', 'users.manage'],
        'manage_roles' => ['roles.create', 'roles.read', 'roles.update', 'roles.delete', 'roles.manage'],
        'manage_permissions' => ['permissions.create', 'permissions.read', 'permissions.update', 'permissions.delete'],
        'manage_settings' => ['settings.update'],
        'view_employees' => ['employees.read'],
        'manage_employees' => ['employees.create', 'employees.read', 'employees.update', 'employees.delete'],
        'approve_leaves' => ['leaves.approve', 'leaves.manage'],
        'view_inventory' => ['inventory.read'],
        'adjust_stock' => ['inventory.adjustments'],
        'manage_suppliers' => ['suppliers.create', 'suppliers.read', 'suppliers.update', 'suppliers.delete'],
        'take_orders' => ['orders.create', 'orders.read'],
        'manage_menu' => ['menu.create', 'menu.read', 'menu.update', 'menu.delete'],
        'view_tasks' => ['tasks.read'],
        'assign_tasks' => ['tasks.assign'],
        'log_maintenance' => ['maintenance.create'],
        'manage_banquet' => ['banquet.create', 'banquet.read', 'banquet.update', 'banquet.delete'],
    ];

    /**
     * Expand an ability into the set of equivalent permission names, resolving
     * in both directions (legacy -> granular and granular -> legacy).
     *
     * @return string[]|null Null when the ability is not aliased so the Gate
     *                       keeps evaluating it with Spatie's own resolution.
     */
    public static function resolve(string $ability): ?array
    {
        $names = [$ability];

        if (isset(self::ALIASES[$ability])) {
            array_push($names, ...self::ALIASES[$ability]);
        }

        foreach (self::ALIASES as $legacy => $equivalents) {
            if (in_array($ability, $equivalents, true)) {
                $names[] = $legacy;
            }
        }

        $names = array_values(array_unique($names));

        return count($names) > 1 ? $names : null;
    }
}
