<?php

namespace Modules\Finance\Exports;

use Modules\Finance\Support\LedgerReport;

/**
 * Profit & loss: income, expense, and the resulting net result for the period.
 */
class ProfitLossExport extends FinanceReportExport
{
    public function __construct(?string $from = null, ?string $to = null)
    {
        $data = LedgerReport::profitLoss($from, $to);

        $rows = [
            self::titleRow('PROFIT & LOSS', $from, $to, 4),
            self::blankRow(),
            ['INCOME', '', '', 'Amount', ''],
        ];

        foreach ($data['income'] as $account) {
            $rows[] = ['', $account->code.' '.$account->name, '', number_format((float) $account->amount, 2), ''];
        }

        $rows[] = ['TOTAL INCOME', '', '', number_format($data['totalIncome'], 2), ''];
        $rows[] = self::blankRow();
        $rows[] = ['EXPENSE', '', '', 'Amount', ''];

        foreach ($data['expense'] as $account) {
            $rows[] = ['', $account->code.' '.$account->name, '', number_format((float) $account->amount, 2), ''];
        }

        $rows[] = ['TOTAL EXPENSE', '', '', number_format($data['totalExpense'], 2), ''];
        $rows[] = self::blankRow();
        $rows[] = [
            'NET '.(($data['netIncome'] ?? 0) >= 0 ? 'PROFIT' : 'LOSS'),
            '',
            '',
            number_format($data['netIncome'], 2),
            '',
        ];

        parent::__construct($rows, 'Profit and Loss');
    }
}
