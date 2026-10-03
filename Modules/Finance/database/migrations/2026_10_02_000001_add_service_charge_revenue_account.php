<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Finance\Models\ChartOfAccount;

/**
 * Chart-of-accounts rows are normally seeded, but a new account that other
 * modules post to cannot wait for someone to remember `db:seed`. LedgerService
 * resolves an account with firstOrFail(), so on an install that never re-seeds,
 * every service charge on a sale would throw, the payment would be left
 * unposted, and `restaurant:reconcile-finance` would retry it forever.
 *
 * The row is idempotent, so a fresh install that already ran
 * ChartOfAccountsSeeder is unaffected.
 */
return new class extends Migration
{
    /** @var array<int, array{code: string, name: string, type: string}> */
    protected array $accounts = [
        ['code' => '4400', 'name' => 'Service Charge Revenue', 'type' => 'income'],
    ];

    public function up(): void
    {
        foreach ($this->accounts as $account) {
            ChartOfAccount::firstOrCreate(
                ['code' => $account['code']],
                [
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'normal_balance' => 'credit',
                    'active' => true,
                ],
            );
        }
    }

    public function down(): void
    {
        foreach ($this->accounts as $account) {
            ChartOfAccount::where('code', $account['code'])->forceDelete();
        }
    }
};
