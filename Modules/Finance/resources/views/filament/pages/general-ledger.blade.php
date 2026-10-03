<x-filament-panels::page>
    @include('finance::filament.pages.partials.date-filter', ['from' => $from, 'to' => $to, 'fiscalPeriods' => $fiscalPeriods, 'activeFiscalYear' => $activeFiscalYear])

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-2">
        <label class="text-xs font-medium text-gray-600 dark:text-gray-300">
            Account
            <select name="account" class="mt-1 block rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5">
                <option value="">All accounts with movement</option>
                @foreach ($accounts as $code => $name)
                    <option value="{{ $code }}" @selected($account === (string) $code)>{{ $code }} — {{ $name }}</option>
                @endforeach
            </select>
        </label>
        <input type="hidden" name="from" value="{{ $from }}">
        <input type="hidden" name="to" value="{{ $to }}">
        <x-filament::button type="submit" size="sm" color="gray">Filter</x-filament::button>
    </form>

    <div class="overflow-hidden rounded-xl border border-primary-200/40 bg-white shadow-sm dark:border-primary-500/15 dark:bg-white/5">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <th class="px-4 py-2 font-medium">Date</th>
                        <th class="px-4 py-2 font-medium">Entry</th>
                        <th class="px-4 py-2 font-medium">Reference</th>
                        <th class="px-4 py-2 font-medium">Description</th>
                        <th class="px-4 py-2 text-right font-medium">Debit</th>
                        <th class="px-4 py-2 text-right font-medium">Credit</th>
                        <th class="px-4 py-2 text-right font-medium">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $line)
                        <tr class="border-b border-gray-50 hover:bg-gray-50/60 dark:border-white/5 dark:hover:bg-white/5">
                            <td class="px-4 py-2 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $line->journalEntry?->date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-2 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $line->journalEntry?->entry_number }}</td>
                            <td class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">{{ $line->journalEntry?->reference_type }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $line->description ?: $line->journalEntry?->description }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs {{ $line->debit > 0 ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400' }}">{{ $line->debit > 0 ? number_format($line->debit, 2) : '' }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs {{ $line->credit > 0 ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400' }}">{{ $line->credit > 0 ? number_format($line->credit, 2) : '' }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs text-gray-900 dark:text-white">{{ number_format($line->balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No posted entries in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50/60 font-semibold dark:border-white/10 dark:bg-white/5">
                        <td colspan="4" class="px-4 py-2 text-right text-gray-700 dark:text-gray-200">Totals</td>
                        <td class="px-4 py-2 text-right font-mono text-xs text-gray-900 dark:text-white">{{ number_format($totalDebit, 2) }}</td>
                        <td class="px-4 py-2 text-right font-mono text-xs text-gray-900 dark:text-white">{{ number_format($totalCredit, 2) }}</td>
                        <td class="px-4 py-2 text-right font-mono text-xs text-gray-900 dark:text-white">{{ number_format($closingBalance, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-filament-panels::page>
