<?php

namespace Modules\Finance\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Support\FinancePermission;
use UnitEnum;

class FinanceDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Finance Dashboard';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'finance';

    protected static ?string $title = 'Finance Dashboard';

    protected string $view = 'finance::filament.pages.finance-dashboard';

    public static function canAccess(): bool
    {
        return FinancePermission::allows(FinancePermission::DASHBOARD_ACCESS);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = auth()->user();

        $canViewChartOfAccounts = FinancePermission::allows(FinancePermission::CHART_OF_ACCOUNTS_READ);
        $canViewJournal = FinancePermission::allows(FinancePermission::JOURNAL_ENTRIES_READ);
        $canViewReports = FinancePermission::allows(FinancePermission::REPORTS_READ);

        return [
            'accountsCount' => ChartOfAccount::count(),
            'postedCount' => JournalEntry::where('status', 'posted')->count(),
            'draftCount' => JournalEntry::where('status', 'draft')->count(),
            'recentEntries' => $canViewJournal
                ? JournalEntry::with('lines.account')->latest()->limit(10)->get()
                : collect(),
            'canViewChartOfAccounts' => $canViewChartOfAccounts,
            'canViewJournal' => $canViewJournal,
            'canViewReports' => $canViewReports,
            'canPostJournal' => FinancePermission::allows(FinancePermission::JOURNAL_ENTRIES_POST),
            'canCreateJournal' => FinancePermission::allows(FinancePermission::JOURNAL_ENTRIES_CREATE),
            'canManagePeriods' => FinancePermission::allows(FinancePermission::PERIODS_UPDATE),
            'canExport' => FinancePermission::allows(FinancePermission::REPORTS_EXPORT),
        ];
    }
}
