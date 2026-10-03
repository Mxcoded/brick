<x-filament-panels::page>
    @include('finance::filament.pages.partials.date-filter', ['from' => $from, 'to' => $to, 'fiscalPeriods' => $fiscalPeriods, 'activeFiscalYear' => $activeFiscalYear])

    <div class="overflow-hidden rounded-xl border border-primary-200/40 bg-white shadow-sm dark:border-primary-500/15 dark:bg-white/5">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <th class="px-4 py-2 font-medium">Source</th>
                        <th class="px-4 py-2 text-right font-medium">Revenue</th>
                        <th class="px-4 py-2 text-right font-medium">Cost</th>
                        <th class="px-4 py-2 text-right font-medium">Net</th>
                        <th class="px-4 py-2 text-right font-medium">Entries</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr class="border-b border-gray-50 hover:bg-gray-50/60 dark:border-white/5 dark:hover:bg-white/5">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $row['label'] }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs text-gray-700 dark:text-gray-300">{{ number_format($row['revenue'], 2) }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs text-gray-500 dark:text-gray-400">{{ $row['expense'] > 0 ? number_format($row['expense'], 2) : '' }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs {{ $row['net'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">{{ number_format($row['net'], 2) }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs text-gray-500 dark:text-gray-400">{{ $row['entryCount'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No posted entries in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50/60 font-semibold dark:border-white/10 dark:bg-white/5">
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-200">Total</td>
                        <td class="px-4 py-2 text-right font-mono text-xs text-gray-900 dark:text-white">{{ number_format($totalRevenue, 2) }}</td>
                        <td class="px-4 py-2 text-right font-mono text-xs text-gray-900 dark:text-white">{{ number_format($totalExpense, 2) }}</td>
                        <td class="px-4 py-2 text-right font-mono text-xs text-gray-900 dark:text-white">{{ number_format($netContribution, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-filament-panels::page>
