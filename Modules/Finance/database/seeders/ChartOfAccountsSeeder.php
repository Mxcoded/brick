<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Finance\Models\ChartOfAccount;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Standard hospitality chart of accounts.
     * normal_balance is derived from the account type.
     */
    protected array $accounts = [
        // Assets (debit normal)
        ['code' => '1000', 'name' => 'Cash', 'type' => 'asset'],
        ['code' => '1100', 'name' => 'Bank', 'type' => 'asset'],
        ['code' => '1110', 'name' => 'Paystack Clearing', 'type' => 'asset'],
        ['code' => '1120', 'name' => 'Stripe Clearing', 'type' => 'asset'],
        ['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'asset'],
        ['code' => '1300', 'name' => 'Inventory', 'type' => 'asset'],
        ['code' => '1400', 'name' => 'Fixed Assets', 'type' => 'asset'],
        ['code' => '1500', 'name' => 'Prepaid Expenses', 'type' => 'asset'],

        // Liabilities (credit normal)
        ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability'],
        ['code' => '2100', 'name' => 'Accrued Expenses', 'type' => 'liability'],
        ['code' => '2200', 'name' => 'Tax Payable', 'type' => 'liability'],
        ['code' => '2300', 'name' => 'Deferred Revenue', 'type' => 'liability'],

        // Equity (credit normal)
        ['code' => '3000', 'name' => "Owner's Equity", 'type' => 'equity'],
        ['code' => '3100', 'name' => 'Retained Earnings', 'type' => 'equity'],

        // Income (credit normal)
        ['code' => '4000', 'name' => 'Room Revenue', 'type' => 'income'],
        ['code' => '4100', 'name' => 'Restaurant Revenue', 'type' => 'income'],
        ['code' => '4200', 'name' => 'Banquet Revenue', 'type' => 'income'],
        ['code' => '4300', 'name' => 'Gym Revenue', 'type' => 'income'],
        ['code' => '4900', 'name' => 'Other Income', 'type' => 'income'],

        // Expenses (debit normal)
        ['code' => '5000', 'name' => 'Cost of Sales', 'type' => 'expense'],
        ['code' => '5100', 'name' => 'Salaries & Wages', 'type' => 'expense'],
        ['code' => '5200', 'name' => 'Utilities', 'type' => 'expense'],
        ['code' => '5300', 'name' => 'Maintenance', 'type' => 'expense'],
        ['code' => '5400', 'name' => 'Depreciation', 'type' => 'expense'],
        ['code' => '5900', 'name' => 'Other Expenses', 'type' => 'expense'],
    ];

    public function run(): void
    {
        foreach ($this->accounts as $account) {
            ChartOfAccount::updateOrCreate(
                ['code' => $account['code']],
                [
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'normal_balance' => in_array($account['type'], ['asset', 'expense']) ? 'debit' : 'credit',
                    'active' => true,
                ]
            );
        }
    }
}
