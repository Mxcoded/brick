<?php

namespace Modules\Finance\Support;

/**
 * Hook consumed by the Admin role editor.
 *
 * Admin discovers this class by convention in every module's Support namespace
 * so Finance can describe its own permissions without Admin needing to know
 * anything about the Finance taxonomy.
 */
class PermissionCatalog
{
    /**
     * @return array<string, array{group: string, resource: string, action: string, kind: string, label: string}>
     */
    public static function catalogOverrides(): array
    {
        return FinancePermission::catalogOverrides();
    }
}
