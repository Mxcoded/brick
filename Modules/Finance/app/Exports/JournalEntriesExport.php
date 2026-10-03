<?php

namespace Modules\Finance\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Finance\Models\JournalEntry;

/**
 * Journal entries as a flat sheet, with the lines of each entry expanded onto
 * their own line.
 *
 * Exported from the table's filtered query rather than a fresh unfiltered one,
 * so "export" always means "export what I am looking at".
 */
class JournalEntriesExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /** @var Builder<JournalEntry> */
    private $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function headings(): array
    {
        return ['Date', 'Entry Number', 'Status', 'Description', 'Reference', 'Account Code', 'Account', 'Description', 'Debit', 'Credit'];
    }

    public function title(): string
    {
        return 'Journal Entries';
    }

    public function array(): array
    {
        $rows = [];

        $this->query
            ->with('lines.account')
            ->orderBy('date')
            ->orderBy('entry_number')
            ->chunk(200, function ($entries) use (&$rows): void {
                foreach ($entries as $entry) {
                    $first = true;

                    foreach ($entry->lines as $line) {
                        $rows[] = [
                            $entry->date?->format('Y-m-d'),
                            $entry->entry_number,
                            $first ? ucfirst((string) $entry->status) : '',
                            $first ? $entry->description : '',
                            $first ? $entry->reference_type : '',
                            $line->account?->code,
                            $line->account?->name,
                            $line->description,
                            number_format((float) $line->debit, 2),
                            number_format((float) $line->credit, 2),
                        ];

                        $first = false;
                    }
                }
            });

        return $rows;
    }
}
