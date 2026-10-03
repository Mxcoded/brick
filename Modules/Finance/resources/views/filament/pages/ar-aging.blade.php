<x-filament-panels::page>
    <div class="mb-4 text-sm text-gray-500 dark:text-gray-400">
        As at <span class="font-medium text-gray-700 dark:text-gray-200">{{ \Illuminate\Support\Carbon::parse($asOf)->format('d M Y') }}</span>
        &mdash; what each company owes the hotel, measured from when it fell due.
    </div>

    <div class="overflow-hidden rounded-xl border border-primary-200/40 bg-white shadow-sm dark:border-primary-500/15 dark:bg-white/5">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <th class="px-4 py-2 font-medium">Customer</th>
                        <th class="px-4 py-2 font-medium">Oldest Due</th>
                        <th class="px-4 py-2 text-right font-medium">Current</th>
                        <th class="px-4 py-2 text-right font-medium">1&ndash;30</th>
                        <th class="px-4 py-2 text-right font-medium">31&ndash;60</th>
                        <th class="px-4 py-2 text-right font-medium">61&ndash;90</th>
                        <th class="px-4 py-2 text-right font-medium">90+</th>
                        <th class="px-4 py-2 text-right font-medium">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr class="border-b border-gray-50 hover:bg-gray-50/60 dark:border-white/5 dark:hover:bg-white/5">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                {{ $row['label'] }}
                                <span class="ml-1 text-xs text-gray-400 dark:text-gray-500">{{ $row['documents'] }} open {{ \Illuminate\Support\Str::plural('document', $row['documents']) }}</span>
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ $row['oldestDue']?->format('d M Y') }}
                            </td>
                            <td class="px-4 py-2 text-right font-mono text-xs text-gray-700 dark:text-gray-300">{{ $row['aging']['current'] > 0 ? number_format($row['aging']['current'], 2) : '' }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs text-gray-700 dark:text-gray-300">{{ $row['aging']['days_1_30'] > 0 ? number_format($row['aging']['days_1_30'], 2) : '' }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs {{ $row['aging']['days_31_60'] > 0 ? 'text-warning-600 dark:text-warning-400' : 'text-gray-500 dark:text-gray-400' }}">{{ $row['aging']['days_31_60'] > 0 ? number_format($row['aging']['days_31_60'], 2) : '' }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs {{ $row['aging']['days_61_90'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-500 dark:text-gray-400' }}">{{ $row['aging']['days_61_90'] > 0 ? number_format($row['aging']['days_61_90'], 2) : '' }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs {{ $row['aging']['days_90_plus'] > 0 ? 'text-danger-600 dark:text-danger-400 font-semibold' : 'text-gray-500 dark:text-gray-400' }}">{{ $row['aging']['days_90_plus'] > 0 ? number_format($row['aging']['days_90_plus'], 2) : '' }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs font-semibold text-gray-900 dark:text-white">{{ number_format($row['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                Nothing outstanding &mdash; every charge on account has been settled.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50/60 font-semibold dark:border-white/10 dark:bg-white/5">
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-200" colspan="2">Total outstanding</td>
                        @foreach (['current', 'days_1_30', 'days_31_60', 'days_61_90', 'days_90_plus'] as $bucket)
                            <td class="px-4 py-2 text-right font-mono text-xs text-gray-900 dark:text-white">{{ number_format($buckets[$bucket], 2) }}</td>
                        @endforeach
                        <td class="px-4 py-2 text-right font-mono text-xs text-gray-900 dark:text-white">{{ number_format($totalOutstanding, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-filament-panels::page>
