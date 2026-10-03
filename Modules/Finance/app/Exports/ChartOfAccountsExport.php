<?php

namespace Modules\Finance\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Finance\Support\LedgerReport;

/**
 * The chart of accounts with the posted movement for the current period.
 */
class ChartOfAccountsExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(private ?string $from = null, private ?string $to = null) {}

    public function headings(): array
    {
        return ['Code', 'Account Name', 'Type', 'Normal Balance', 'Active', 'Debit', 'Credit', 'Balance'];
    }

    public function title(): string
    {
        return 'Chart of Accounts';
    }

    public function array(): array
    {
        $rows = [];

        foreach (LedgerReport::accounts($this->from, $this->to) as $account) {
            $balance = $account->normal_balance === 'credit'
                ? (float) $account->credit_total - (float) $account->debit_total
                : (float) $account->debit_total - (float) $account->credit_total;

            $rows[] = [
                $account->code,
                $account->name,
                ucfirst((string) $account->type),
                ucfirst((string) $account->normal_balance),
                $account->active ? 'Yes' : 'No',
                number_format((float) $account->debit_total, 2),
                number_format((float) $account->credit_total, 2),
                number_format($balance, 2),
            ];
        }

        return $rows;
    }
}
