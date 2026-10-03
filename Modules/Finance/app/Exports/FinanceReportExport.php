<?php

namespace Modules\Finance\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Base for the Finance report exports.
 *
 * Financial statements are single sheets of numbers laid out exactly as the
 * report shows them, so they are exported from arrays rather than row models.
 * `blankRow()` inserts the spacing that makes a statement readable in Excel,
 * and the header/total rows stay as literal data so nothing is lost when the
 * file is forwarded on.
 */
abstract class FinanceReportExport implements FromArray, ShouldAutoSize, WithTitle
{
    /** @var array<int, array<int, mixed>> */
    protected array $rows = [];

    protected string $heading = 'Report';

    public function __construct(array $rows = [], string $heading = 'Report')
    {
        $this->rows = $rows;
        $this->heading = $heading;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        // Excel forbids : \ / ? * [ ] in sheet names and caps them at 31 chars.
        return mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $this->heading) ?: 'Report', 0, 31);
    }

    /**
     * @return array<int, mixed>
     */
    protected static function blankRow(): array
    {
        return [];
    }

    /**
     * A statement header spanning the visible columns, so the exported file is
     * self-describing once it is separated from the application.
     *
     * @return array<int, string>
     */
    protected static function titleRow(string $title, ?string $from = null, ?string $to = null, int $span = 4): array
    {
        $period = match (true) {
            $from && $to => "  ({$from} to {$to})",
            $from => "  (from {$from})",
            $to => "  (to {$to})",
            default => '',
        };

        $row = array_fill(0, $span, '');
        $row[0] = $title.$period;

        return $row;
    }
}
