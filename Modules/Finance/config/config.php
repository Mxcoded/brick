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
        'paystack_clearing' => '1110',
        'stripe_clearing' => '1120',
        'accounts_receivable' => '1200',
        'inventory' => '1300',
        'accounts_payable' => '2000',

        /*
         * Where a sale's non-revenue components land. A gross receipt is not
         * revenue: VAT is money held for the tax authority and a service charge
         * is a separate income stream, so recordSale() credits each to its own
         * account and leaves only the net in the module's revenue.
         */
        'tax_payable' => '2200',
        'service_charge_revenue' => '4400',

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
            // Purchases are expensed to Cost of Sales at goods-receipt because
            // consumption-side COGS posting is not implemented yet. Revisit
            // once stock issues post Dr COGS / Cr Inventory.
            'inventory' => '5000',
            // 5100 Salaries & Wages. This previously pointed at 5400, which the
            // chart of accounts defines as Depreciation, so every gym trainer
            // payout landed in the wrong account.
            'gym' => '5100', // trainer payouts
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

        // Pluggable online gateways → dedicated clearing accounts for reconciliation
        'paystack' => 'paystack_clearing',
        'stripe' => 'stripe_clearing',
    ],
];
