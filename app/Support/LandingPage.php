<?php

namespace App\Support;

use App\Models\User;

/**
 * Single source of truth for "where does this user belong?".
 *
 * Shared by the universal login and by the panel's unauthorized redirect so a
 * user cannot end up bounced between two different answers.
 */
class LandingPage
{
    /**
     * Any of these means the user is a finance user and belongs in Finance
     * rather than a generic staff area.
     *
     * `access_finance_dashboard` is the canonical check and is the same gate the
     * Finance dashboard itself uses. The pre-split names are still listed so a
     * user provisioned before the CRUD split is not dumped into a staff task
     * list just because their role no longer carries the legacy grants.
     */
    private const FINANCE_PERMISSIONS = [
        'access_finance_dashboard',
        'finance.view_coa',
        'finance.manage_coa',
        'finance.create_journal',
        'finance.post_journal',
        'finance.view_ledger',
        'finance.view_reports',
        'finance.manage_periods',
    ];

    /**
     * Route name of the landing page for the given user.
     */
    public static function routeFor(?User $user): string
    {
        if (! $user) {
            return 'guest.dashboard';
        }

        if ($user->can('access_admin_dashboard')) {
            return 'filament.admin.pages.dashboard';
        }

        if ($user->can('access_frontdesk_dashboard')) {
            return 'frontdesk.dashboard';
        }

        if ($user->can('access_staff_dashboard') && $user->can('employees.read')) {
            return 'staff.dashboard';
        }

        // Finance is checked before the generic staff fallbacks, otherwise a
        // finance user with the staff role is sent to their task list.
        if ($user->canAny(self::FINANCE_PERMISSIONS)) {
            return 'filament.admin.pages.finance';
        }

        // Restaurant. Both entry points are gated on access_restaurant_dashboard.
        // This is checked before Tasks: AppServiceProvider grants
        // access_tasks_dashboard to every staff *account type*, so a waiter would
        // otherwise be sent to /tasks and never reach the terminal they work on.
        // Floor staff serve tables from the POS terminal; anyone who can also
        // change the menu or read the venue settings runs the back office
        // instead, so a manager or an oversight reader is never dropped into a
        // waiter terminal. The capability names mirror RestaurantPermission, but
        // are spelled here so core does not depend on a module class that may be
        // disabled.
        if ($user->can('access_restaurant_dashboard')) {
            $runsBackOffice = $user->can('menu_items.update') || $user->can('settings.read');

            return $runsBackOffice ? 'restaurant.admin.dashboard' : 'restaurant.waiter.dashboard';
        }

        if ($user->can('access_tasks_dashboard')) {
            return 'tasks.index';
        }

        if ($user->isStaff()) {
            return 'staff.pending';
        }

        return 'guest.dashboard';
    }
}
