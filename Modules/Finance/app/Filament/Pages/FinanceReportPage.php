<?php

namespace Modules\Finance\Filament\Pages;

use Filament\Actions;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Finance\Exports\FinanceReportExport;
use Modules\Finance\Models\FiscalYear;
use Modules\Finance\Services\FiscalPeriodService;
use Modules\Finance\Support\FinancePermission;

/**
 * Shared behaviour for the read-only financial report pages.
 *
 * The from/to period is carried as query-string parameters (rendered by the
 * views as a small GET filter) so it survives a full page load and matches the
 * behaviour of the report pages before they were ported to Filament.
 */
abstract class FinanceReportPage extends Page
{
    public ?string $from = null;

    public ?string $to = null;

    public static function canAccess(): bool
    {
        return FinancePermission::allows(FinancePermission::REPORTS_READ);
    }

    public function mount(): void
    {
        $this->from = request()->query('from') ?: null;
        $this->to = request()->query('to') ?: null;

        // With no explicit filter, fall back to the configured fiscal calendar so
        // reports open on the current period. Stays unfiltered when no calendar
        // has been set up, preserving the pre-fiscal-year behaviour.
        if ($this->from === null && $this->to === null) {
            $range = app(FiscalPeriodService::class)->defaultReportRange();

            $this->from = $range['from'] ?? null;
            $this->to = $range['to'] ?? null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return array_merge(
            $this->reportData($this->from, $this->to),
            [
                'from' => $this->from,
                'to' => $this->to,
                'fiscalPeriods' => app(FiscalPeriodService::class)->periodsForFilter(),
                'activeFiscalYear' => FiscalYear::current(),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function reportData(?string $from, ?string $to): array;

    /**
     * Export the report to Excel.
     *
     * The action is always registered but hidden and unauthorized without
     * `finance.reports.export`, and the permission is re-checked inside the
     * handler. A user who can read the trial balance on screen must not be able
     * to take the whole ledger away in a spreadsheet.
     *
     * @return array<int, Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        $exportClass = $this->exportClass();

        if (! $exportClass) {
            return [];
        }

        return [
            Actions\Action::make('exportExcel')
                ->label('Export to Excel')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->visible(fn (): bool => FinancePermission::allows(FinancePermission::REPORTS_EXPORT))
                ->authorize(fn (): bool => FinancePermission::allows(FinancePermission::REPORTS_EXPORT))
                ->action(function () {
                    abort_unless(
                        FinancePermission::allows(FinancePermission::REPORTS_EXPORT),
                        403,
                        'You do not have permission to export financial reports.'
                    );

                    return Excel::download($this->newExport(), $this->exportFilename());
                }),
        ];
    }

    /**
     * @return class-string|null
     */
    protected function exportClass(): ?string
    {
        return null;
    }

    /**
     * Build the export for the current period.
     *
     * Overridden by reports whose export needs more than the period, such as the
     * general ledger's account filter.
     */
    protected function newExport(): ?FinanceReportExport
    {
        $exportClass = $this->exportClass();

        return $exportClass ? new $exportClass($this->from, $this->to) : null;
    }

    protected function exportFilename(): string
    {
        return Str::slug(static::getTitle() ?: 'report').'-'.now()->format('Y-m-d').'.xlsx';
    }
}
