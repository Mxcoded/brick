<?php

namespace Modules\Finance\Exports;

use Modules\Finance\Support\LedgerReport;

/**
 * General-ledger transaction detail with a running balance.
 *
 * Used both for a single account (the drill-down an auditor asks for) and for
 * every account that moved in the period.
 */
class GeneralLedgerExport extends FinanceReportExport
{
    public function __construct(?string $from = null, ?string $to = null, ?string $code = null)
    {
        $data = LedgerReport::generalLedger($code, $from, $to);

        $heading = $data['account']
            ? $data['account']->code.' '.$data['account']->name
            : 'All accounts';

        $rows = [
            self::titleRow('GENERAL LEDGER - '.$heading, $from, $to, 6),
            self::blankRow(),
            ['Date', 'Entry', 'Reference', 'Description', 'Debit', 'Credit'],
        ];

        foreach ($data['rows'] as $line) {
            $rows[] = [
                optional($line->journalEntry)->date?->format('Y-m-d'),
                $line->journalEntry?->entry_number,
                $line->journalEntry?->reference_type,
                $line->description ?: $line->journalEntry?->description,
                number_format((float) $line->debit, 2),
                number_format((float) $line->credit, 2),
            ];
        }

        $rows[] = self::blankRow();
        $rows[] = [
            'CLOSING BALANCE',
            '',
            '',
            '',
            number_format($data['totalDebit'], 2),
            number_format($data['totalCredit'], 2),
        ];

        parent::__construct($rows, 'General Ledger');
    }
}
