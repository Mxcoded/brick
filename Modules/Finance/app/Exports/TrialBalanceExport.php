<?php

namespace Modules\Finance\Exports;

use Modules\Finance\Support\LedgerReport;

/**
 * Trial balance: accounts with movement, then the debit/credit grand totals.
 */
class TrialBalanceExport extends FinanceReportExport
{
    public function __construct(?string $from = null, ?string $to = null)
    {
        $data = LedgerReport::trialBalance($from, $to);

        $rows = [
            self::titleRow('TRIAL BALANCE', $from, $to, 4),
            self::blankRow(),
            ['Code', 'Account', 'Type', 'Debit', 'Credit'],
        ];

        foreach ($data['accounts'] as $account) {
            $rows[] = [
                $account->code,
                $account->name,
                ucfirst((string) $account->type),
                number_format((float) $account->debit_total, 2),
                number_format((float) $account->credit_total, 2),
            ];
        }

        $rows[] = self::blankRow();
        $rows[] = [
            'TOTAL',
            '',
            '',
            number_format($data['totalDebit'], 2),
            number_format($data['totalCredit'], 2),
        ];

        $rows[] = [
            $data['balanced'] ? 'BALANCED' : 'OUT OF BALANCE',
        ];

        parent::__construct($rows, 'Trial Balance');
    }
}
