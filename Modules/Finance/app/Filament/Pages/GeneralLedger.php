<?php

namespace Modules\Finance\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Exports\FinanceReportExport;
use Modules\Finance\Exports\GeneralLedgerExport;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Support\LedgerReport;
use UnitEnum;

/**
 * Transaction-level detail behind the trial balance.
 *
 * The report itself is reachable from the report navigation group; the
 * trial-balance view links an account code in so an auditor can go from a
 * summary straight to the postings behind it.
 */
class GeneralLedger extends FinanceReportPage
{
    protected static ?string $title = 'General Ledger';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'General Ledger';

    protected static ?int $navigationSort = 8;

    protected static ?string $slug = 'finance/general-ledger';

    protected string $view = 'finance::filament.pages.general-ledger';

    /** Optional account filter; null shows every account that moved. */
    public ?string $account = null;

    public function mount(): void
    {
        parent::mount();

        $this->account = request()->query('account') ?: null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'account' => $this->account,
                'accounts' => ChartOfAccount::orderBy('code')->pluck('name', 'code'),
            ],
        );
    }

    protected function reportData(?string $from, ?string $to): array
    {
        return LedgerReport::generalLedger($this->account, $from, $to);
    }

    protected function exportClass(): ?string
    {
        return GeneralLedgerExport::class;
    }

    /**
     * The account filter is part of what is exported, otherwise the spreadsheet
     * would silently contain the whole ledger instead of the account in view.
     */
    protected function newExport(): ?FinanceReportExport
    {
        return new GeneralLedgerExport($this->from, $this->to, $this->account);
    }
}
