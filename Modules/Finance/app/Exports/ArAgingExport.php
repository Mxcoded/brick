<?php

namespace Modules\Finance\Exports;

use Modules\Finance\Support\LedgerReport;

/**
 * Accounts receivable aging for credit control.
 *
 * The sheet is laid out the way a hotel's credit controller reads it: one row per
 * company, the aging buckets across, and the total owed at the end.
 */
class ArAgingExport extends FinanceReportExport
{
    public function __construct(?string $asOf = null)
    {
        $data = LedgerReport::receivableAging($asOf);

        $rows = [
            self::titleRow('ACCOUNTS RECEIVABLE AGING', null, $asOf, 7),
            self::blankRow(),
            [
                'Customer',
                'Current',
                '1-30 Days',
                '31-60 Days',
                '61-90 Days',
                '90+ Days',
                'Total Outstanding',
            ],
        ];

        foreach ($data['rows'] as $row) {
            $rows[] = [
                $row['label'],
                number_format($row['aging']['current'], 2),
                number_format($row['aging']['days_1_30'], 2),
                number_format($row['aging']['days_31_60'], 2),
                number_format($row['aging']['days_61_90'], 2),
                number_format($row['aging']['days_90_plus'], 2),
                number_format($row['total'], 2),
            ];
        }

        $rows[] = self::blankRow();
        $rows[] = array_merge(
            ['TOTAL'],
            array_map(
                fn (float $amount): string => number_format($amount, 2),
                array_values($data['buckets'])
            ),
            [number_format($data['totalOutstanding'], 2)]
        );

        parent::__construct($rows, 'AR Aging');
    }
}
