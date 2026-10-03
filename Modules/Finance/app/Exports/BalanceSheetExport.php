<?php

namespace Modules\Finance\Exports;

use Modules\Finance\Support\LedgerReport;

/**
 * Balance sheet: assets, liabilities, equity, and the check that the two sides
 * agree. The check row is included deliberately so a reader of the exported
 * file can see the statement was internally consistent when it was produced.
 */
class BalanceSheetExport extends FinanceReportExport
{
    public function __construct(?string $from = null, ?string $to = null)
    {
        $data = LedgerReport::balanceSheet($from, $to);

        $rows = [
            self::titleRow('BALANCE SHEET', $from, $to, 3),
            self::blankRow(),
            ['ASSETS', '', 'Amount'],
        ];

        foreach ($data['assets'] as $account) {
            $rows[] = ['', $account->code.' '.$account->name, number_format((float) $account->amount, 2)];
        }

        $rows[] = ['TOTAL ASSETS', '', number_format($data['totalAssets'], 2)];
        $rows[] = self::blankRow();
        $rows[] = ['LIABILITIES', '', 'Amount'];

        foreach ($data['liabilities'] as $account) {
            $rows[] = ['', $account->code.' '.$account->name, number_format((float) $account->amount, 2)];
        }

        $rows[] = ['TOTAL LIABILITIES', '', number_format($data['totalLiabilities'], 2)];
        $rows[] = self::blankRow();
        $rows[] = ['EQUITY', '', 'Amount'];

        foreach ($data['equity'] as $account) {
            $rows[] = ['', $account->code.' '.$account->name, number_format((float) $account->amount, 2)];
        }

        $rows[] = ['NET INCOME', '', number_format($data['netIncome'], 2)];
        $rows[] = ['TOTAL EQUITY', '', number_format($data['totalEquity'], 2)];
        $rows[] = ['TOTAL EQUITY AND LIABILITIES', '', number_format($data['totalEquityAndLiabilities'], 2)];
        $rows[] = self::blankRow();
        $rows[] = [
            $data['balanced'] ? 'BALANCED' : 'OUT OF BALANCE',
            '',
            number_format(abs($data['totalAssets'] - $data['totalEquityAndLiabilities']), 2).' difference',
        ];

        parent::__construct($rows, 'Balance Sheet');
    }
}
