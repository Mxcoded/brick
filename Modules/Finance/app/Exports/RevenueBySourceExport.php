<?php

namespace Modules\Finance\Exports;

use Modules\Finance\Support\LedgerReport;

/**
 * Revenue and cost by operating area, for management reporting.
 */
class RevenueBySourceExport extends FinanceReportExport
{
    public function __construct(?string $from = null, ?string $to = null)
    {
        $data = LedgerReport::revenueBySource($from, $to);

        $rows = [
            self::titleRow('REVENUE BY SOURCE', $from, $to, 5),
            self::blankRow(),
            ['Source', 'Revenue', 'Expense', 'Net', 'Entries'],
        ];

        foreach ($data['rows'] as $row) {
            $rows[] = [
                $row['label'],
                number_format($row['revenue'], 2),
                number_format($row['expense'], 2),
                number_format($row['net'], 2),
                $row['entryCount'],
            ];
        }

        $rows[] = self::blankRow();
        $rows[] = [
            'TOTAL',
            number_format($data['totalRevenue'], 2),
            number_format($data['totalExpense'], 2),
            number_format($data['netContribution'], 2),
            '',
        ];

        parent::__construct($rows, 'Revenue by Source');
    }
}
