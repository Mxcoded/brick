<x-filament-panels::page>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ now()->format('l, F d, Y') }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if ($canViewChartOfAccounts)
                <x-filament::button tag="a" href="{{ \Modules\Finance\Filament\Resources\ChartOfAccounts\ChartOfAccountResource::getUrl('index') }}"
                    icon="heroicon-m-list-bullet" color="gray" size="sm">
                    Chart of Accounts
                </x-filament::button>
            @endif
            @if ($canViewJournal)
                <x-filament::button tag="a" href="{{ \Modules\Finance\Filament\Resources\JournalEntries\JournalEntryResource::getUrl('index') }}"
                    icon="heroicon-m-book-open" color="gray" size="sm">
                    Journal
                </x-filament::button>
            @endif
            @if ($canViewReports)
                <x-filament::button tag="a" href="{{ \Modules\Finance\Filament\Pages\TrialBalance::getUrl() }}"
                    icon="heroicon-m-scale" color="primary" size="sm">
                    Reports
                </x-filament::button>
            @endif
        </div>
    </div>

    {{-- ═══════════════════ KPI CARDS ═══════════════════ --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="rounded-xl border-s-4 border-primary-600 bg-white p-4 shadow-sm ring-1 ring-primary-100/50 dark:border-primary-500 dark:bg-white/5 dark:ring-primary-500/10">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Accounts</p>
            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $accountsCount }}</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">chart of accounts</p>
        </div>
        <div class="rounded-xl border-s-4 border-success-500 bg-white p-4 shadow-sm ring-1 ring-primary-100/50 dark:border-success-500 dark:bg-white/5 dark:ring-primary-500/10">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Posted Entries</p>
            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $postedCount }}</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">in the ledger</p>
        </div>
        <div class="rounded-xl border-s-4 border-warning-500 bg-white p-4 shadow-sm ring-1 ring-primary-100/50 dark:border-warning-500 dark:bg-white/5 dark:ring-primary-500/10">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Draft Entries</p>
            <p class="mt-1 text-lg font-bold {{ $draftCount > 0 ? 'text-warning-600 dark:text-warning-400' : 'text-gray-900 dark:text-white' }}">{{ $draftCount }}</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">awaiting posting</p>
        </div>
        <div class="rounded-xl border-s-4 border-info-500 bg-white p-4 shadow-sm ring-1 ring-primary-100/50 dark:border-info-400 dark:bg-white/5 dark:ring-primary-500/10">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Month</p>
            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ now()->format('M Y') }}</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">current period</p>
        </div>
    </div>

    @if ($canViewReports)
        {{-- ═══════════════════ QUICK REPORTS ═══════════════════ --}}
        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <a href="{{ \Modules\Finance\Filament\Pages\TrialBalance::getUrl() }}"
                class="rounded-xl border border-primary-200/40 bg-white p-4 shadow-sm transition-all duration-200 hover:border-primary-300/60 hover:shadow-md dark:border-primary-500/15 dark:bg-white/5 dark:hover:border-primary-500/40">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-scale" class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Trial Balance</p>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Debits and credits per account</p>
            </a>
            <a href="{{ \Modules\Finance\Filament\Pages\ProfitLoss::getUrl() }}"
                class="rounded-xl border border-primary-200/40 bg-white p-4 shadow-sm transition-all duration-200 hover:border-primary-300/60 hover:shadow-md dark:border-primary-500/15 dark:bg-white/5 dark:hover:border-primary-500/40">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-arrow-trending-up" class="h-5 w-5 text-success-600 dark:text-success-400" />
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Profit &amp; Loss</p>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Income against expenses</p>
            </a>
            <a href="{{ \Modules\Finance\Filament\Pages\BalanceSheet::getUrl() }}"
                class="rounded-xl border border-primary-200/40 bg-white p-4 shadow-sm transition-all duration-200 hover:border-primary-300/60 hover:shadow-md dark:border-primary-500/15 dark:bg-white/5 dark:hover:border-primary-500/40">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-building-library" class="h-5 w-5 text-info-600 dark:text-info-400" />
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Balance Sheet</p>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Assets, liabilities and equity</p>
            </a>
        </div>
    @endif

    {{-- ═══════════════════ RECENT JOURNAL ENTRIES ═══════════════════ --}}
    @if ($canViewJournal)
        <div class="mt-4 overflow-hidden rounded-xl border border-primary-200/40 bg-white shadow-sm dark:border-primary-500/15 dark:bg-white/5">
            <div class="border-b border-gray-100 px-4 py-3 dark:border-white/10">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Recent Journal Entries</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <th class="px-4 py-2 font-medium">Entry #</th>
                            <th class="px-4 py-2 font-medium">Date</th>
                            <th class="px-4 py-2 font-medium">Description</th>
                            <th class="px-4 py-2 font-medium">Status</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentEntries as $entry)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/60 dark:border-white/5 dark:hover:bg-white/5">
                                <td class="px-4 py-2 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $entry->entry_number }}</td>
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $entry->date->format('d M Y') }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($entry->description ?? '—', 60) }}</td>
                                <td class="px-4 py-2">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $entry->status === 'posted' ? 'bg-success-500/10 text-success-700 dark:text-success-400' : 'bg-gray-500/10 text-gray-600 dark:text-gray-300' }}">
                                        {{ ucfirst($entry->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <a href="{{ \Modules\Finance\Filament\Resources\JournalEntries\JournalEntryResource::getUrl('view', ['record' => $entry]) }}"
                                        class="text-xs font-medium text-primary-600 hover:underline dark:text-primary-400">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No journal entries yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>