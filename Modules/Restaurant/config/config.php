<?php

return [
    'name' => 'Restaurant',

    /*
    |--------------------------------------------------------------------------
    | Role Grants
    |--------------------------------------------------------------------------
    |
    | Which Spatie role receives which Restaurant permission.
    |
    | This lives in configuration rather than in PHP on purpose. The module
    | defines *what* the capabilities are (RestaurantPermission); this table
    | decides *who* gets them, which is a business question that changes
    | between sites, deploys and tenants. Overriding it in a published config
    | lets a hotel hand the kitchen more or less without a code change.
    |
    | The value for a role is a list of permission names, or the string '*'
    | for the whole taxonomy. Anything not listed here gets nothing beyond the
    | module dashboard access.
    |
    */

    'roles' => [

        // Administrators keep the whole taxonomy. This has to be listed here
        // rather than assumed in RestaurantPermissionSeeder: the seeder revokes
        // Restaurant grants from every role missing from this table, so leaving
        // admin out would strip an administrator of Restaurant access whenever
        // the module is seeded on its own.
        'admin' => ['*'],

        // Runs the venue day to day: full back office, no procurement.
        'restaurant_manager' => ['*'],

        // Takes orders and serves tables. Read-only on the operational records
        // plus the kitchen display and taking payment; nothing that alters the
        // menu, the floor plan, stock, customers, settings or reports.
        'waiter' => [
            'menu_categories.read',
            'menu_items.read',
            'tables.read',
            'sales_points.read',
            'collection_points.read',
            'orders.read',
            'orders.create',
            'orders.update',
            'orders.accept',
            'orders.reject',
            'payments.read',
            'payments.create',
            'kitchen.read',
            'kitchen.update',
        ],

        // Stock and recipe ingredients sit with store keeping, which is already
        // the module's home for physical inventory.
        'store_keeper' => [
            'menu_items.read',
            'stock.read',
            'stock.create',
            'stock.update',
            'stock.delete',
            'stock.restore',
            'stock.movements',
            'recipes.read',
            'recipes.create',
            'recipes.delete',
        ],

        // The property General Manager is accountable for the venue's revenue
        // and cost: they authorise refunds, set local tax and service charge,
        // and void mistakes. Full access is what the job actually requires.
        'gm' => ['*'],

        // The Group General Manager reads across several properties rather than
        // running any one of them. Oversight means seeing the numbers and
        // exporting them, not changing an order, moving money or editing tax
        // settings on somebody else's hotel. Every write capability is
        // deliberately absent below, and the test
        // `RestaurantPermissionSeederTest::group_oversight_is_read_only`
        // fails if one is added by accident.
        'ggm' => [
            'access_restaurant_dashboard',
            'menu_categories.read',
            'menu_items.read',
            'tables.read',
            'sales_points.read',
            'collection_points.read',
            'orders.read',
            'payments.read',
            'kitchen.read',
            'stock.read',
            'recipes.read',
            'customers.read',
            'reports.sales.read',
            'reports.popular_items.read',
            'reports.waiter_performance.read',
            'reports.shift.read',
            'reports.sales.export',
            'reports.popular_items.export',
            'settings.read',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Exports
    |--------------------------------------------------------------------------
    |
    | Report exports are a separate capability from reading the report on
    | screen, for the same reason Finance separates them: a screen is a
    | summary, a CSV is the whole dataset. Grant them on purpose.
    |
    */

    'export_permissions' => [
        'reports.sales.export',
        'reports.popular_items.export',
    ],

    /*
    |--------------------------------------------------------------------------
    | Kitchen Display
    |--------------------------------------------------------------------------
    |
    | Which menu categories are prepared at the bar rather than in the kitchen.
    | An order card is tagged "Bar" or "Kitchen" from the category of the items
    | on it, so this decides where the ticket is expected to appear.
    |
    | Anything not listed here is treated as kitchen work, which is what the
    | legacy display did: it only recognised Beverages, and defaulted every
    | other category -- including ones added later -- to the kitchen. Keep it
    | that way, so a new category is queued for cooking rather than silently
    | disappearing from the pass.
    |
    */

    'kitchen' => [
        'bar_categories' => ['Beverages'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Finance Coupling
    |--------------------------------------------------------------------------
    |
    | Restaurant payments post straight into the Finance ledger. A payment that
    | reaches 'completed' without a ledger entry leaves the two systems
    | disagreeing, so posting is never left to the UI layer: the service posts
    | inside the same transaction and stamps finance_posted for the reconcile
    | command to pick up if anything still slips through.
    |
    */

    'finance' => [
        'source' => 'restaurant',
        'reference_type' => 'restaurant_payment',
    ],
];
