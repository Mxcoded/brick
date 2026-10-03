<x-filament-panels::page>
    {{-- One filter drives every report on this page, matching the legacy
         /reports/sales/data, /reports/popular-items and /reports/waiter-performance
         endpoints which each took the same `period` query string. --}}
    <form wire:submit="applyFilters" class="fi-section mb-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        {{ $this->form }}

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <x-filament::button type="submit" icon="heroicon-m-funnel">
                Apply
            </x-filament::button>

            @if ($this->canExportSales())
                {{-- The CSV carries every row, so it is a separate capability
                     from reading the summary on screen. --}}
                <x-filament::button
                    tag="a"
                    color="gray"
                    icon="heroicon-m-arrow-down-tray"
                    :href="route('restaurant.admin.reports.sales.export', $this->exportQuery())"
                >
                    Export sales CSV
                </x-filament::button>
            @endif

            @if ($this->canExportPopularItems())
                <x-filament::button
                    tag="a"
                    color="gray"
                    icon="heroicon-m-arrow-down-tray"
                    :href="route('restaurant.admin.reports.popular.export', $this->exportQuery())"
                >
                    Export popular items CSV
                </x-filament::button>
            @endif
        </div>
    </form>

    @if ($this->canViewSales())
        @php($sales = $this->salesSummary())

        <div class="fi-section mb-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="mb-3 text-base font-semibold text-gray-950 dark:text-white">
                Sales
                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                    {{ $sales['from']->format('j M Y') }} – {{ $sales['to']->format('j M Y') }}
                </span>
            </h2>

            <dl class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ([
                    'Gross sales' => 'NGN '.number_format($sales['total_sales'], 2),
                    'Orders' => number_format($sales['order_count']),
                    'Average order' => 'NGN '.number_format($sales['average_order'], 2),
                    'Discounts' => 'NGN '.number_format($sales['total_discounts'], 2),
                    'Service charge' => 'NGN '.number_format($sales['total_service_charge'], 2),
                    'VAT' => 'NGN '.number_format($sales['total_vat'], 2),
                    'Net revenue' => 'NGN '.number_format($sales['net_sales'], 2),
                ] as $label => $value)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ $label }}
                        </dt>
                        <dd class="text-lg font-semibold text-gray-950 dark:text-white">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            @if ($sales['payment_methods']->isNotEmpty())
                <h3 class="mt-5 mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                    By payment method
                </h3>
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="py-1">Method</th>
                            <th class="py-1">Count</th>
                            <th class="py-1 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sales['payment_methods'] as $method => $figures)
                            <tr class="border-t border-gray-100 dark:border-white/10">
                                <td class="py-1.5 capitalize">{{ $method }}</td>
                                <td class="py-1.5">{{ $figures['count'] }}</td>
                                <td class="py-1.5 text-right">NGN {{ number_format($figures['total'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if ($sales['sales_points']->isNotEmpty())
                <h3 class="mt-5 mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                    By sales point
                </h3>
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="py-1">Sales point</th>
                            <th class="py-1">Orders</th>
                            <th class="py-1 text-right">Gross</th>
                            <th class="py-1 text-right">VAT</th>
                            <th class="py-1 text-right">Service charge</th>
                            <th class="py-1 text-right">Net revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sales['sales_points'] as $point)
                            <tr class="border-t border-gray-100 dark:border-white/10">
                                <td class="py-1.5">{{ $point['name'] }}</td>
                                <td class="py-1.5">{{ number_format($point['orders']) }}</td>
                                <td class="py-1.5 text-right">NGN {{ number_format($point['gross'], 2) }}</td>
                                <td class="py-1.5 text-right">NGN {{ number_format($point['vat'], 2) }}</td>
                                <td class="py-1.5 text-right">NGN {{ number_format($point['service_charge'], 2) }}</td>
                                <td class="py-1.5 text-right font-medium">NGN {{ number_format($point['net'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    @if ($this->canViewPopularItems())
        <div class="fi-section mb-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="mb-3 text-base font-semibold text-gray-950 dark:text-white">Popular items</h2>

            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="py-1">Item</th>
                        <th class="py-1">Category</th>
                        <th class="py-1 text-right">Quantity</th>
                        <th class="py-1 text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->popularItems() as $item)
                        <tr class="border-t border-gray-100 dark:border-white/10">
                            <td class="py-1.5">{{ $item['name'] }}</td>
                            <td class="py-1.5">{{ $item['category'] }}</td>
                            <td class="py-1.5 text-right">{{ number_format($item['quantity']) }}</td>
                            <td class="py-1.5 text-right">NGN {{ number_format($item['revenue'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-3 text-gray-500 dark:text-gray-400">
                                No completed orders in this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($this->canViewWaiterPerformance())
        @php($waiters = $this->waiterPerformance())

        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="mb-3 text-base font-semibold text-gray-950 dark:text-white">Waiter performance</h2>

            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="py-1">Waiter</th>
                        <th class="py-1 text-right">Shifts</th>
                        <th class="py-1 text-right">Orders</th>
                        <th class="py-1 text-right">Hours</th>
                        <th class="py-1 text-right">Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($waiters as $waiter)
                        <tr class="border-t border-gray-100 dark:border-white/10">
                            <td class="py-1.5">{{ $waiter['waiter_name'] }}</td>
                            <td class="py-1.5 text-right">{{ $waiter['shifts'] }}</td>
                            <td class="py-1.5 text-right">{{ $waiter['orders_taken'] }}</td>
                            <td class="py-1.5 text-right">{{ number_format($waiter['hours_worked'], 1) }}</td>
                            <td class="py-1.5 text-right">NGN {{ number_format($waiter['total_sales'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-3 text-gray-500 dark:text-gray-400">
                                No shifts clocked in this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($this->canViewShift())
        @php($shift = $this->selectedShift())

        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="mb-3 text-base font-semibold text-gray-950 dark:text-white">Shift reconciliation</h2>

            @if (! $shift)
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Choose a shift above to reconcile the till for that waiter.
                </p>
            @else
                <dl class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ([
                        'Waiter' => $shift['waiter'],
                        'Clocked in' => $shift['clock_in']?->format('j M Y, H:i') ?? '—',
                        'Clocked out' => $shift['clock_out']?->format('j M Y, H:i') ?? 'Still open',
                        'Total sales' => 'NGN '.number_format($shift['total_sales'], 2),
                        'Service charge' => 'NGN '.number_format($shift['total_service_charge'], 2),
                        'VAT' => 'NGN '.number_format($shift['total_vat'], 2),
                        'Net of charge & VAT' => 'NGN '.number_format($shift['net_sales'], 2),
                        'Orders' => number_format($shift['order_count']),
                        'Opening cash' => 'NGN '.number_format($shift['starting_cash'], 2),
                        'Counted cash' => $shift['ending_cash'] === null
                            ? 'Not counted'
                            : 'NGN '.number_format($shift['ending_cash'], 2),
                        'Expected cash' => 'NGN '.number_format($shift['expected_ending_cash'], 2),
                    ] as $label => $value)
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                {{ $label }}
                            </dt>
                            <dd class="text-lg font-semibold text-gray-950 dark:text-white">{{ $value }}</dd>
                        </div>
                    @endforeach

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Discrepancy
                        </dt>
                        <dd @class([
                            'text-lg font-semibold',
                            'text-gray-950 dark:text-white' => $shift['discrepancy'] === null || abs($shift['discrepancy']) < 0.005,
                            'text-danger-600 dark:text-danger-400' => $shift['discrepancy'] !== null && abs($shift['discrepancy']) >= 0.005,
                        ])>
                            @if ($shift['discrepancy'] === null)
                                Awaiting count
                            @else
                                {{ $shift['discrepancy'] > 0 ? 'Over ' : 'Short ' }}
                                NGN {{ number_format(abs($shift['discrepancy']), 2) }}
                            @endif
                        </dd>
                    </div>
                </dl>

                @if ($shift['payment_methods']->isNotEmpty())
                    <h3 class="mt-5 mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                        Takings by method
                    </h3>
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="py-1">Method</th>
                                <th class="py-1">Count</th>
                                <th class="py-1 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($shift['payment_methods'] as $method => $figures)
                                <tr class="border-t border-gray-100 dark:border-white/10">
                                    <td class="py-1.5 capitalize">{{ $method }}</td>
                                    <td class="py-1.5">{{ $figures['count'] }}</td>
                                    <td class="py-1.5 text-right">NGN {{ number_format($figures['total'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <h3 class="mt-5 mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                    Orders in this shift
                </h3>
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="py-1">Order</th>
                            <th class="py-1">Type</th>
                            <th class="py-1">Customer</th>
                            <th class="py-1 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shift['orders'] as $order)
                            <tr class="border-t border-gray-100 dark:border-white/10">
                                <td class="py-1.5">#{{ $order['id'] }}</td>
                                <td class="py-1.5">{{ $order['type'] }}</td>
                                <td class="py-1.5">{{ $order['customer'] }}</td>
                                <td class="py-1.5 text-right">NGN {{ number_format($order['total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-3 text-gray-500 dark:text-gray-400">
                                    No completed orders in this shift.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    @endif
</x-filament-panels::page>
