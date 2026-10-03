<x-filament-panels::page>
    @include('finance::filament.pages.partials.date-filter', ['from' => $from, 'to' => $to, 'fiscalPeriods' => $fiscalPeriods, 'activeFiscalYear' => $activeFiscalYear])

    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
        <div class="rounded-xl border-s-4 border-success-500 bg-white p-4 shadow-sm ring-1 ring-primary-100/50 dark:border-success-500 dark:bg-white/5 dark:ring-primary-500/10">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Income</p>
            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ number_format($totalIncome, 2) }}</p>
        </div>
        <div class="rounded-xl border-s-4 border-danger-500 bg-white p-4 shadow-sm ring-1 ring-primary-100/50 dark:border-danger-500 dark:bg-white/5 dark:ring-primary-500/10">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Expenses</p>
            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ number_format($totalExpense, 2) }}</p>
        </div>
        <div class="rounded-xl border-s-4 {{ $netIncome >= 0 ? 'border-primary-600' : 'border-danger-500' }} bg-white p-4 shadow-sm ring-1 ring-primary-100/50 dark:bg-white/5 dark:ring-primary-500/10">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Net {{ $netIncome >= 0 ? 'Profit' : 'Loss' }}</p>
            <p class="mt-1 text-lg font-bold {{ $netIncome >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">{{ number_format($netIncome, 2) }}</p>
        </div>
    </div>

    @foreach (['Income' => $income, 'Expenses' => $expense] as $label => $rows)
        <div class="mt-4 overflow-hidden rounded-xl border border-primary-200/40 bg-white shadow-sm dark:border-primary-500/15 dark:bg-white/5">
            <div class="border-b border-gray-100 px-4 py-3 dark:border-white/10">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $label }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <th class="px-4 py-2 font-medium">GL Code</th>
                            <th class="px-4 py-2 font-medium">Account</th>
                            <th class="px-4 py-2 text-right font-medium">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $account)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/60 dark:border-white/5 dark:hover:bg-white/5">
                                <td class="px-4 py-2 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $account->code }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $account->name }}</td>
                                <td class="px-4 py-2 text-right font-mono text-xs text-gray-700 dark:text-gray-300">{{ number_format($account->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No {{ strtolower($label) }} in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</x-filament-panels::page>