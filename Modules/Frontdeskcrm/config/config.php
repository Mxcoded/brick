<?php

return [
    'name' => 'Frontdeskcrm',

    /*
    |--------------------------------------------------------------------------
    | Frontdesk CRM role map
    |--------------------------------------------------------------------------
    |
    | Who holds which Frontdesk capability. This is the ONLY place in the module
    | where a role name is allowed to appear: the permission taxonomy lives in
    | Modules/Frontdeskcrm/app/Support/FrontdeskPermission.php and the PHP only
    | ever asks what a capability is, never who holds it.
    |
    | `*` means "the whole taxonomy". A role absent from this table holds no
    | Frontdesk grants at all, because the seeder revokes them - so any role
    | meant to keep access has to be listed here explicitly (admin included).
    |
    | Every role listed also receives the module gate
    | (`access_frontdesk_dashboard`) so it can reach the PMS shell at all.
    |
    */

    'roles' => [

        // Administrators keep the whole taxonomy. Listed rather than assumed in
        // the seeder, which revokes from every role missing from this table.
        'admin' => ['*'],

        // General managers run the hotel, so they hold the full PMS including
        // the money-moving actions. Finance and audit read the output but do
        // not operate the desk.
        'gm' => ['*'],
        'ggm' => ['*'],

        // Front office manager: the working owner of the desk. Full PMS except
        // the ability to void or credit an issued invoice, which is finance's
        // to authorise because it rewrites the receivable.
        'receptionist' => [
            'guests.read',
            'guests.create',
            'guests.update',
            'guests.delete',
            'guests.check_in',
            'guests.check_out',
            'guests.import',
            'registrations.read',
            'registrations.create',
            'registrations.update',
            'registrations.delete',
            'registrations.check_in',
            'registrations.check_out',
            'registrations.adjust_stay',
            'registrations.post_charge',
            'folios.read',
            'folios.post_charge',
            'folios.split',
            'folios.close',
            'invoices.read',
            'invoices.create',
            'invoices.issue',
            'rate_codes.read',
            'guest_types.read',
            'pms.settings.read',
        ],

        // Finance reads the billing picture and issues/credits documents, but
        // does not check guests in or maintain the master data - that is the
        // desk's job and needs no finance permission.
        'finance' => [
            'registrations.read',
            'folios.read',
            'invoices.read',
            'invoices.create',
            'invoices.issue',
            'invoices.void',
            'invoices.credit_note',
            'rate_codes.read',
            'seasons.read',
        ],

        'senior_accountant' => [
            'registrations.read',
            'folios.read',
            'invoices.read',
            'invoices.create',
            'invoices.issue',
            'invoices.void',
            'invoices.credit_note',
        ],

        // Auditors are read-only by definition: they see what happened and
        // cannot change it.
        'auditor' => [
            'guests.read',
            'registrations.read',
            'folios.read',
            'invoices.read',
            'rate_codes.read',
            'guest_types.read',
            'seasons.read',
            'pms.settings.read',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions that hand over the full dataset
    |--------------------------------------------------------------------------
    |
    | Exports and bulk writes are the capabilities most likely to be granted by
    | accident, so they are declared here and asserted against in the tests
    | rather than assumed from the taxonomy.
    |
    */

    'export_permissions' => [
        'guests.import',
        'invoices.void',
        'invoices.credit_note',
    ],
];
