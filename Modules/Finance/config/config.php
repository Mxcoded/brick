<?php

return [
    'name' => 'Finance',

    /*
     * GL account codes used when posting transactions from other modules.
     * Codes map to rows in finance_chart_of_accounts (seeded by ChartOfAccountsSeeder).
     */
    'accounts' => [
        'cash' => '1000',
        'bank' => '1100',
        'accounts_receivable' => '1200',
        'inventory' => '1300',
        'accounts_payable' => '2000',

        // Revenue account per module (credited on a sale / cash receipt)
        'revenue' => [
            'restaurant' => '4100',
            'banquet' => '4200',
            'gym' => '4300',
            'frontdesk' => '4000',
            'website' => '4000',
        ],

        // Expense account per module (debited on an outflow)
        'expense' => [
            'inventory' => '5000',
            'gym' => '5400', // trainer payouts
            'restaurant' => '5100',
        ],
    ],

    /*
     * Maps free-text payment methods (stored on each module's payment row)
     * to an asset GL account. Anything not listed falls back to 'bank'.
     */
    'payment_methods' => [
        'cash' => 'cash',
        'pos' => 'bank',
        'card' => 'bank',
        'mobile_money' => 'bank',
        'transfer' => 'bank',
        'bank_transfer' => 'bank',
        'cheque' => 'bank',
        'crypto' => 'bank',
        'room_charge' => 'accounts_receivable',
    ],
];
