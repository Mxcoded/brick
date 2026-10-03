<x-filament-panels::page>
    @include('finance::filament.pages.partials.date-filter', ['from' => $from, 'to' => $to, 'fiscalPeriods' => $fiscalPeriods, 'activeFiscalYear' => $activeFiscalYear])

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="space-y-4">
            <div class="overflow-hidden rounded-xl border border-primary-200/40 bg-white shadow-sm dark:border-primary-500/15 dark:bg-white/5">
                <div class="border-b border-gray-100 px-4 py-3 dark:border-white/10">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Assets</p>
                </div>
                <table class="w-full text-left text-sm">
                    <tbody>
                        @forelse ($assets as $account)
                            <tr class="border-b border-gray-50 dark:border-white/5">
                                <td class="px-4 py-2 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $account->code }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $account->name }}</td>
                                <td class="px-4 py-2 text-right font-mono text-xs text-gray-700 dark:text-gray-300">{{ number_format($account->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No assets in this period.</td></tr>
                        @endforelse
                        <tr class="border-t-2 border-gray-200 bg-gray-50/60 font-semibold dark:border-white/10 dark:bg-white/5">
                            <td colspan="2" class="px-4 py-2 text-right text-gray-700 dark:text-gray-200">Total Assets</td>
                            <td class="px-4 py-2 text-right font-mono text-xs text-gray-900 dark:text-white">{{ number_format($totalAssets, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <div class="overflow-hidden rounded-xl border border-primary-200/40 bg-white shadow-sm dark:border-primary-500/15 dark:bg-white/5">
                <div class="border-b border-gray-100 px-4 py-3 dark:border-white/10">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Liabilities</p>
                </div>
                <table class="w-full text-left text-sm">
                    <tbody>
                        @forelse ($liabilities as $account)
                            <tr class="border-b border-gray-50 dark:border-white/5">
                                <td class="px-4 py-2 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $account->code }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $account->name }}</td>
                                <td class="px-4 py-2 text-right font-mono text-xs text-gray-700 dark:text-gray-300">{{ number_format($account->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No liabilities in this period.</td></tr>
                        @endforelse
                        <tr class="border-t-2 border-gray-200 bg-gray-50/60 font-semibold dark:border-white/10 dark:bg-white/5">
                            <td colspan="2" class="px-4 py-2 text-right text-gray-700 dark:text-gray-200">Total Liabilities</td>
                            <td class="px-4 py-2 text-right font-mono text-xs text-gray-900 dark:text-white">{{ number_format($totalLiabilities, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="overflow-hidden rounded-xl border border-primary-200/40 bg-white shadow-sm dark:border-primary-500/15 dark:bg-white/5">
                <div class="border-b border-gray-100 px-4 py-3 dark:border-white/10">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Equity</p>
                </div>
                <table class="w-full text-left text-sm">
                    <tbody>
                        @forelse ($equity as $account)
                            <tr class="border-b border-gray-50 dark:border-white/5">
                                <td class="px-4 py-2 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $account->code }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $account->name }}</td>
                                <td class="px-4 py-2 text-right font-mono text-xs text-gray-700 dark:text-gray-300">{{ number_format($account->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No equity accounts in this period.</td></tr>
                        @endforelse
                        <tr class="border-b border-gray-100 dark:border-white/10">
                            <td colspan="2" class="px-4 py-2 text-right text-gray-700 dark:text-gray-200">Retained earnings (net {{ $netIncome >= 0 ? 'profit' : 'loss' }})</td>
                            <td class="px-4 py-2 text-right font-mono text-xs text-gray-700 dark:text-gray-300">{{ number_format($netIncome, 2) }}</td>
                        </tr>
                        <tr class="border-t-2 border-gray-200 bg-gray-50/60 font-semibold dark:border-white/10 dark:bg-white/5">
                            <td colspan="2" class="px-4 py-2 text-right text-gray-700 dark:text-gray-200">Total Equity &amp; Liabilities</td>
                            <td class="px-4 py-2 text-right font-mono text-xs text-gray-900 dark:text-white">{{ number_format($totalEquityAndLiabilities, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 flex items-center justify-end">
        <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $balanced ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
            <x-filament::icon :icon="$balanced ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle'" class="h-4 w-4" />
            {{ $balanced ? 'Balanced' : 'Out of balance' }}
        </span>
    </div>
</x-filament-panels::page>