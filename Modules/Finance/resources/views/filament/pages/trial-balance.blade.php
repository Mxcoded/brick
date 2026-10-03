<x-filament-panels::page>
    @include('finance::filament.pages.partials.date-filter', ['from' => $from, 'to' => $to, 'fiscalPeriods' => $fiscalPeriods, 'activeFiscalYear' => $activeFiscalYear])

    <div class="overflow-hidden rounded-xl border border-primary-200/40 bg-white shadow-sm dark:border-primary-500/15 dark:bg-white/5">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <th class="px-4 py-2 font-medium">GL Code</th>
                        <th class="px-4 py-2 font-medium">Account</th>
                        <th class="px-4 py-2 text-right font-medium">Debit</th>
                        <th class="px-4 py-2 text-right font-medium">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accounts as $account)
                        <tr class="border-b border-gray-50 hover:bg-gray-50/60 dark:border-white/5 dark:hover:bg-white/5">
                            <td class="px-4 py-2 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $account->code }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $account->name }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs {{ $account->debit_total > 0 ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400' }}">{{ $account->debit_total > 0 ? number_format($account->debit_total, 2) : '' }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs {{ $account->credit_total > 0 ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400' }}">{{ $account->credit_total > 0 ? number_format($account->credit_total, 2) : '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No posted entries in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50/60 font-semibold dark:border-white/10 dark:bg-white/5">
                        <td colspan="2" class="px-4 py-2 text-right text-gray-700 dark:text-gray-200">Totals</td>
                        <td class="px-4 py-2 text-right font-mono text-xs text-gray-900 dark:text-white">{{ number_format($totalDebit, 2) }}</td>
                        <td class="px-4 py-2 text-right font-mono text-xs text-gray-900 dark:text-white">{{ number_format($totalCredit, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="flex items-center justify-between border-t border-gray-100 px-4 py-3 dark:border-white/10">
            <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $balanced ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                <x-filament::icon :icon="$balanced ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle'" class="h-4 w-4" />
                {{ $balanced ? 'Balanced' : 'Out of balance' }}
            </span>
        </div>
    </div>
</x-filament-panels::page>