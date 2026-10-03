<?php

namespace Modules\Restaurant\Support;

/**
 * Grouping for the Restaurant taxonomy in the Admin role editor.
 *
 * Admin discovers this class by convention in every module's Support namespace,
 * so nothing has to be registered here. Without it the role editor falls back to
 * splitting the dotted name, which misreads the three-segment permissions
 * (`reports.sales` becomes a resource literally called "Reports.sales") and
 * files `settings.*` under the admin group because Inventory and Admin both own
 * a settings screen.
 *
 * The mapping itself lives in RestaurantPermission so the taxonomy stays the one
 * place that defines what the module can do.
 */
class PermissionCatalog
{
    public static function catalogOverrides(): array
    {
        return RestaurantPermission::catalogOverrides();
    }
}
