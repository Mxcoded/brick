<x-filament-panels::page>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ now()->format('l, F d, Y') }} &middot; {{ $activeUsersToday }} active users today
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if ($failedJobs > 0)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-danger-500/10 px-3 py-1 text-xs font-medium text-danger-600 dark:text-danger-400">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-4 w-4" />
                    {{ $failedJobs }} failed jobs
                </span>
            @endif
            <span class="text-sm text-gray-600 dark:text-gray-400">Welcome, {{ auth()->user()->name }}</span>
        </div>
    </div>

    {{-- ═══════════════════ STRATEGIC KPI BANNER ═══════════════════ --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-xl border-s-4 border-success-500 bg-white p-4 shadow-sm ring-1 ring-primary-100/50 transition-shadow duration-200 hover:shadow-md dark:border-success-500 dark:bg-white/5 dark:ring-primary-500/10"
             title="Total income from Banquet, Front Desk, Gym, and Website modules combined.">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Revenue</p>
            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">₦{{ number_format($totalRevenue) }}</p>
            @if ($revenueChange != 0)
                <p class="mt-0.5 text-xs font-medium {{ $revenueDirection === 'up' ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                    {{ $revenueDirection === 'up' ? '▲' : '▼' }} {{ abs($revenueChange) }}% vs last month
                </p>
            @endif
        </div>
        <div class="rounded-xl border-s-4 border-primary-600 bg-white p-4 shadow-sm ring-1 ring-primary-100/50 transition-shadow duration-200 hover:shadow-md dark:border-primary-500 dark:bg-white/5 dark:ring-primary-500/10"
             title="Cumulative income from January 1st to today across all departments.">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">YTD Revenue</p>
            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">₦{{ number_format($ytdRevenue) }}</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ now()->year }} year-to-date</p>
        </div>
        <div class="rounded-xl border-s-4 border-info-500 bg-white p-4 shadow-sm ring-1 ring-primary-100/50 transition-shadow duration-200 hover:shadow-md dark:border-info-400 dark:bg-white/5 dark:ring-primary-500/10"
             title="Percentage of hotel room units currently occupied.">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Occupancy</p>
            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $occupancyRate }}%</p>
            <p class="mt-0.5 text-xs font-medium {{ $occupancyChange >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                {{ $occupancyChange >= 0 ? '▲' : '▼' }} {{ abs($occupancyChange) }}pp
            </p>
        </div>
        <div class="rounded-xl border-s-4 border-gray-400 bg-white p-4 shadow-sm ring-1 ring-primary-100/50 transition-shadow duration-200 hover:shadow-md dark:border-gray-500 dark:bg-white/5 dark:ring-primary-500/10"
             title="Employees currently employed (no end date recorded).">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Active Staff</p>
            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $activeEmployees }}</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">of {{ $totalEmployees }} total</p>
        </div>
        <div class="rounded-xl border-s-4 border-danger-500 bg-white p-4 shadow-sm ring-1 ring-primary-100/50 transition-shadow duration-200 hover:shadow-md dark:border-danger-500 dark:bg-white/5 dark:ring-primary-500/10"
             title="Total urgent items requiring your decision across the hotel.">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Critical Alerts</p>
            <p class="mt-1 text-lg font-bold {{ $criticalAlerts > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-900 dark:text-white' }}">
                {{ $criticalAlerts }}
            </p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">needs attention</p>
        </div>
        <div class="rounded-xl border-s-4 border-teal-500 bg-white p-4 shadow-sm ring-1 ring-primary-100/50 transition-shadow duration-200 hover:shadow-md dark:border-teal-400 dark:bg-white/5 dark:ring-primary-500/10"
             title="Number of guests currently staying in the hotel.">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Checked In</p>
            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $checkedIn }}</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $totalRoomUnits }} total units</p>
        </div>
    </div>

    {{-- ═══════════════════ REVENUE TREND CHART ═══════════════════ --}}
    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="rounded-xl border border-primary-200/40 bg-white p-4 shadow-sm transition-all duration-200 hover:border-primary-300/60 hover:shadow-md dark:border-primary-500/15 dark:bg-white/5 dark:hover:border-primary-500/40 xl:col-span-2"
             title="Monthly revenue broken down by department.">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Revenue Trend (Last 6 Months)</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">This month: ₦{{ number_format($revenueThisMonth) }}</span>
            </div>
            @php
                $maxRev = max(array_merge($monthlyBanquet, $monthlyFrontdesk, $monthlyGym, $monthlyWebsite));
                $maxRev = $maxRev > 0 ? $maxRev : 1;
                $totals = array_map(fn ($b, $f, $g, $w) => $b + $f + $g + $w, $monthlyBanquet, $monthlyFrontdesk, $monthlyGym, $monthlyWebsite);
                $maxTotal = max($totals) ?: 1;
            @endphp
            <div class="flex items-end gap-2" style="height: 200px;">
                @foreach ($revenueMonths as $idx => $month)
                    @php
                        $total = $totals[$idx];
                        $pct = round(($total / $maxTotal) * 100);
                        $bPct = $maxRev > 0 ? round(($monthlyBanquet[$idx] / $maxRev) * 100) : 0;
                        $fPct = $maxRev > 0 ? round(($monthlyFrontdesk[$idx] / $maxRev) * 100) : 0;
                        $gPct = $maxRev > 0 ? round(($monthlyGym[$idx] / $maxRev) * 100) : 0;
                        $wPct = $maxRev > 0 ? round(($monthlyWebsite[$idx] / $maxRev) * 100) : 0;
                    @endphp
                    <div class="flex flex-1 flex-col items-center">
                        <small class="mb-1 text-xs font-semibold text-gray-600 dark:text-gray-300">₦{{ number_format($total) }}</small>
                        <div class="flex w-full flex-col-reverse" style="height: 160px;">
                            <div class="w-full rounded-t bg-gray-200 dark:bg-white/10" style="height: {{ $pct }}%;">
                                <div class="flex h-full flex-col-reverse">
                                    @if ($wPct > 0)<div style="height: {{ $total > 0 ? round(($monthlyWebsite[$idx] / $total) * $pct) : 0 }}%; background: #20c997; opacity: 0.85;"></div>@endif
                                    @if ($gPct > 0)<div style="height: {{ $total > 0 ? round(($monthlyGym[$idx] / $total) * $pct) : 0 }}%; background: #7c3aed; opacity: 0.85;"></div>@endif
                                    @if ($fPct > 0)<div style="height: {{ $total > 0 ? round(($monthlyFrontdesk[$idx] / $total) * $pct) : 0 }}%; background: #06b6d4; opacity: 0.85;"></div>@endif
                                    @if ($bPct > 0)<div style="height: {{ $total > 0 ? round(($monthlyBanquet[$idx] / $total) * $pct) : 0 }}%; background: #2563eb; opacity: 0.85;"></div>@endif
                                </div>
                            </div>
                        </div>
                        <small class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $month }}</small>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 flex flex-wrap items-center justify-center gap-4 text-xs text-gray-600 dark:text-gray-300">
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm" style="background:#2563eb"></span> Banquet</span>
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm" style="background:#06b6d4"></span> Front Desk</span>
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm" style="background:#7c3aed"></span> Gym</span>
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm" style="background:#20c997"></span> Website</span>
            </div>
        </div>

        <div class="rounded-xl border border-primary-200/40 bg-white p-4 shadow-sm transition-all duration-200 hover:border-primary-300/60 hover:shadow-md dark:border-primary-500/15 dark:bg-white/5 dark:hover:border-primary-500/40"
             title="Side-by-side comparison of the current month versus the previous month.">
            <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">This Month vs Last Month</h3>
            <div class="grid grid-cols-2 gap-2">
                <div class="rounded-lg bg-success-50 p-3 text-center dark:bg-white/5">
                    <span class="block text-xs uppercase text-gray-500 dark:text-gray-400">This Month</span>
                    <span class="mt-1 block text-base font-bold text-success-700 dark:text-success-400">₦{{ number_format($revenueThisMonth) }}</span>
                </div>
                <div class="rounded-lg bg-gray-100 p-3 text-center dark:bg-white/5">
                    <span class="block text-xs uppercase text-gray-500 dark:text-gray-400">Last Month</span>
                    <span class="mt-1 block text-base font-bold text-gray-600 dark:text-gray-300">₦{{ number_format($revenueLastMonth) }}</span>
                </div>
            </div>
            <div class="mt-4 text-center">
                @if ($revenueChange > 0)
                    <p class="text-3xl font-bold text-success-600 dark:text-success-400">▲</p>
                    <p class="text-sm font-bold text-success-600 dark:text-success-400">+{{ $revenueChange }}% growth</p>
                @elseif ($revenueChange < 0)
                    <p class="text-3xl font-bold text-danger-600 dark:text-danger-400">▼</p>
                    <p class="text-sm font-bold text-danger-600 dark:text-danger-400">{{ $revenueChange }}% decline</p>
                @else
                    <p class="text-3xl font-bold text-gray-400">—</p>
                    <p class="text-sm font-bold text-gray-500 dark:text-gray-400">No change</p>
                @endif
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Month-over-month revenue change</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ MODULE HEALTH (ROW 1) ═══════════════════ --}}
    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        @php
            $moduleCards = [
                [
                    'title' => 'Front Desk',
                    'badge' => ["$occupancyRate%", $occupancyRate >= 70 ? 'bg-success-500/15 text-success-700 dark:text-success-400' : ($occupancyRate >= 40 ? 'bg-warning-500/15 text-warning-700 dark:text-warning-400' : 'bg-danger-500/15 text-danger-700 dark:text-danger-400')],
                    'rows' => [
                        ['Checked In', "$checkedIn", ''],
                        ['Reserved', "$reservations", ''],
                        ['Check-ins Today', "$todayCheckins", ''],
                        ['Check-outs Today', "$todayCheckouts", ''],
                        ['-', '-', 'hr'],
                    ],
                    'footer' => [['Revenue (Month)', '₦' . number_format($frontdeskRevenueMonth), 'font-semibold text-gray-900 dark:text-white']],
                ],
                [
                    'title' => 'Banquet & Events',
                    'badge' => ["$banquetOrdersTotal orders", $banquetOrdersPending == 0 ? 'bg-success-500/15 text-success-700 dark:text-success-400' : 'bg-warning-500/15 text-warning-700 dark:text-warning-400'],
                    'rows' => [
                        ['Pending', "$banquetOrdersPending", 'text-warning-600 dark:text-warning-400'],
                        ['Confirmed', "$banquetOrdersConfirmed", 'text-success-600 dark:text-success-400'],
                        ['Completed', "$banquetOrdersCompleted", 'text-info-600 dark:text-info-400'],
                        ['Enquiries', "$pendingEnquiries", ''],
                        ['-', '-', 'hr'],
                    ],
                    'footer' => [
                        ['Revenue', '₦' . number_format($banquetRevenue), 'font-semibold'],
                        ['Outstanding', '₦' . number_format($banquetOutstanding), 'font-semibold text-danger-600 dark:text-danger-400'],
                    ],
                ],
                [
                    'title' => 'Restaurant',
                    'badge' => ["$restaurantOrdersMonth month", 'bg-info-500/15 text-info-700 dark:text-info-400'],
                    'rows' => [
                        ['Orders Today', "$restaurantOrdersToday", 'text-lg font-bold text-primary-600 dark:text-primary-400'],
                        ['Pending Orders', "$restaurantOrdersPending", 'text-warning-600 dark:text-warning-400'],
                        ['This Month', "$restaurantOrdersMonth", ''],
                    ],
                    'footer' => [],
                ],
                [
                    'title' => 'Gym',
                    'badge' => ["$activeMemberships active", $activeMemberships > 0 ? 'bg-success-500/15 text-success-700 dark:text-success-400' : 'bg-gray-500/15 text-gray-600 dark:text-gray-400'],
                    'rows' => [
                        ['Active Memberships', "$activeMemberships", ''],
                        ['Revenue (Month)', '₦' . number_format($gymPaymentsMonth), ''],
                        ['Billing Due (≤7d)', "$membershipDueSoon", $membershipDueSoon > 0 ? 'text-warning-600 dark:text-warning-400' : ''],
                        ['-', '-', 'hr'],
                    ],
                    'footer' => [['Total Revenue', '₦' . number_format($gymRevenue), 'font-semibold text-gray-900 dark:text-white']],
                ],
            ];
        @endphp
        @foreach ($moduleCards as $card)
            <div class="rounded-xl border border-primary-200/40 bg-white p-4 shadow-sm transition-all duration-200 hover:border-primary-300/60 hover:shadow-md dark:border-primary-500/15 dark:bg-white/5 dark:hover:border-primary-500/40">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $card['title'] }}</h3>
                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $card['badge'][1] }}">{{ $card['badge'][0] }}</span>
                </div>
                <div class="space-y-1">
                    @foreach ($card['rows'] as [$label, $value, $cls])
                        @if ($label === '-')
                            <hr class="dark:border-white/10">
                        @else
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-300">{{ $label }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white {{ $cls }}">{{ $value }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
                @if (count($card['footer']))
                    <div class="mt-2 space-y-1 border-t border-gray-100 pt-2 dark:border-white/10">
                        @foreach ($card['footer'] as [$label, $value, $cls])
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-300">{{ $label }}</span>
                                <span class="{{ $cls }}">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- ═══════════════════ MODULE HEALTH (ROW 2) ═══════════════════ --}}
    <div class="mt-4">
        <div class="rounded-xl border border-primary-200/40 bg-white p-4 shadow-sm transition-all duration-200 hover:border-primary-300/60 hover:shadow-md dark:border-primary-500/15 dark:bg-white/5 dark:hover:border-primary-500/40">
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Maintenance</h3>
                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $maintenanceCritical > 0 ? 'bg-danger-500/15 text-danger-700 dark:text-danger-400' : ($maintenanceOpen > 0 ? 'bg-warning-500/15 text-warning-700 dark:text-warning-400' : 'bg-success-500/15 text-success-700 dark:text-success-400') }}">{{ $maintenanceOpen }} open</span>
            </div>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-6">
                <div>
                    <div class="space-y-1 text-sm">
                        <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Open</span><span class="font-semibold {{ $maintenanceOpen > 0 ? 'text-warning-600 dark:text-warning-400' : 'text-gray-900 dark:text-white' }}">{{ $maintenanceOpen }}</span></div>
                        <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Critical</span><span class="font-semibold {{ $maintenanceCritical > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-900 dark:text-white' }}">{{ $maintenanceCritical }}</span></div>
                        <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Completed (M)</span><span class="font-semibold text-success-600 dark:text-success-400">{{ $maintenanceCompletedMonth }}</span></div>
                        <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Total Cost</span><span class="font-semibold text-gray-900 dark:text-white">₦{{ number_format($maintenanceCost) }}</span></div>
                    </div>
                </div>
                @if ($latestGenReadings->isNotEmpty())
                    <div>
                        <p class="mb-1 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Today's Gen Readings</p>
                        @foreach ($latestGenReadings as $gen)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-300">{{ str_replace('_', ' ', ucfirst($gen->category)) }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $gen->reading_value }}% ({{ number_format($gen->calculated_value, 2) }})</span>
                            </div>
                        @endforeach
                    </div>
                @endif
                @if ($latestDieselReading)
                    <div>
                        <p class="mb-1 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Diesel Reservoir</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($latestDieselReading->reading_value) }}L</p>
                    </div>
                @endif
                @if ($latestWaterReading)
                    <div>
                        <p class="mb-1 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Water Tank</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $latestWaterReading->reading_value }}%</p>
                    </div>
                @endif
                @if ($maintenanceByDept->count())
                    <div>
                        <p class="mb-1 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">By Department</p>
                        @foreach ($maintenanceByDept as $dept)
                            <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-300">
                                <span>{{ $dept->department }}</span><span class="font-semibold text-gray-900 dark:text-white">{{ $dept->total }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-primary-200/40 bg-white p-4 shadow-sm transition-all duration-200 hover:border-primary-300/60 hover:shadow-md dark:border-primary-500/15 dark:bg-white/5 dark:hover:border-primary-500/40">
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tasks</h3>
                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $tasksOverdue > 0 ? 'bg-danger-500/15 text-danger-700 dark:text-danger-400' : ($tasksPending > 0 ? 'bg-warning-500/15 text-warning-700 dark:text-warning-400' : 'bg-success-500/15 text-success-700 dark:text-success-400') }}">{{ $tasksPending }} pending</span>
            </div>
            <div class="space-y-1 text-sm">
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Pending</span><span class="font-semibold text-warning-600 dark:text-warning-400">{{ $tasksPending }}</span></div>
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">In Progress</span><span class="font-semibold text-primary-600 dark:text-primary-400">{{ $tasksInProgress }}</span></div>
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Overdue</span><span class="font-semibold {{ $tasksOverdue > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-900 dark:text-white' }}">{{ $tasksOverdue }}</span></div>
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">High Priority</span><span class="font-semibold {{ $highPriorityTasks > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-900 dark:text-white' }}">{{ $highPriorityTasks }}</span></div>
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Completed (Month)</span><span class="font-semibold text-success-600 dark:text-success-400">{{ $tasksCompletedMonth }}</span></div>
            </div>
        </div>

        <div class="rounded-xl border border-primary-200/40 bg-white p-4 shadow-sm transition-all duration-200 hover:border-primary-300/60 hover:shadow-md dark:border-primary-500/15 dark:bg-white/5 dark:hover:border-primary-500/40">
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Inventory</h3>
                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $lowStockItems > 0 ? 'bg-danger-500/15 text-danger-700 dark:text-danger-400' : 'bg-success-500/15 text-success-700 dark:text-success-400' }}">{{ $lowStockItems }} low</span>
            </div>
            <div class="space-y-1 text-sm">
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Low Stock Alerts</span><span class="font-semibold {{ $lowStockItems > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-900 dark:text-white' }}">{{ $lowStockItems }}</span></div>
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Pending POs</span><span class="font-semibold text-warning-600 dark:text-warning-400">{{ $pendingPOs }}</span></div>
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Awaiting Approval</span><span class="font-semibold {{ $pendingApprovalPOs > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-900 dark:text-white' }}">{{ $pendingApprovalPOs }}</span></div>
            </div>
        </div>

        <div class="rounded-xl border border-primary-200/40 bg-white p-4 shadow-sm transition-all duration-200 hover:border-primary-300/60 hover:shadow-md dark:border-primary-500/15 dark:bg-white/5 dark:hover:border-primary-500/40">
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Website</h3>
                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $unreadMessages > 0 ? 'bg-warning-500/15 text-warning-700 dark:text-warning-400' : 'bg-success-500/15 text-success-700 dark:text-success-400' }}">{{ $unreadMessages }} unread</span>
            </div>
            <div class="space-y-1 text-sm">
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Bookings (Month)</span><span class="font-semibold text-gray-900 dark:text-white">{{ $websiteBookingsMonth }}</span></div>
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Revenue (Month)</span><span class="font-semibold text-gray-900 dark:text-white">₦{{ number_format($websiteRevenueMonth) }}</span></div>
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Confirmed</span><span class="font-semibold text-success-600 dark:text-success-400">{{ $confirmedBookings }}</span></div>
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Unread Msgs</span><span class="font-semibold {{ $unreadMessages > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-900 dark:text-white' }}">{{ $unreadMessages }}</span></div>
                <div class="flex items-center justify-between border-t border-gray-100 pt-2 dark:border-white/10"><span class="text-gray-600 dark:text-gray-300">Total Revenue</span><span class="font-semibold text-gray-900 dark:text-white">₦{{ number_format($websiteRevenue) }}</span></div>
            </div>
        </div>

        <div class="rounded-xl border border-primary-200/40 bg-white p-4 shadow-sm transition-all duration-200 hover:border-primary-300/60 hover:shadow-md dark:border-primary-500/15 dark:bg-white/5 dark:hover:border-primary-500/40">
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">KPI Snapshot</h3>
            </div>
            <div class="space-y-1 text-sm">
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Revenue (Month)</span><span class="font-semibold text-gray-900 dark:text-white">₦{{ number_format($revenueThisMonth) }}</span></div>
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Maintenance Cost</span><span class="font-semibold text-gray-900 dark:text-white">₦{{ number_format($maintenanceCost) }}</span></div>
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Banquet Outstanding</span><span class="font-semibold text-danger-600 dark:text-danger-400">₦{{ number_format($banquetOutstanding) }}</span></div>
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Active Modules</span><span class="font-semibold text-gray-900 dark:text-white">{{ $activeModules }}</span></div>
                <div class="flex items-center justify-between"><span class="text-gray-600 dark:text-gray-300">Total Users</span><span class="font-semibold text-gray-900 dark:text-white">{{ $totalUsers }}</span></div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ BOTTOM ROW: ACTION ITEMS, HR, ACTIVITY ═══════════════════ --}}
    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="rounded-xl border border-primary-200/40 bg-white p-4 shadow-sm transition-all duration-200 hover:border-primary-300/60 hover:shadow-md dark:border-primary-500/15 dark:bg-white/5 dark:hover:border-primary-500/40">
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Action Items</h3>
                @if ($criticalAlerts > 0)
                    <span class="rounded-full bg-danger-500/15 px-2 py-0.5 text-xs font-medium text-danger-700 dark:text-danger-400">{{ $criticalAlerts }} alerts</span>
                @endif
            </div>
            <div class="space-y-1">
                @php
                    $items = [
                        ['hazard', 'text-danger-600 dark:text-danger-400', 'bg-danger-500/15 text-danger-700 dark:text-danger-400', $maintenanceCritical, 'Critical Maintenance'],
                        ['clock', 'text-danger-600 dark:text-danger-400', 'bg-danger-500/15 text-danger-700 dark:text-danger-400', $tasksOverdue, 'Overdue Tasks'],
                        ['exclamation', 'text-warning-600 dark:text-warning-400', 'bg-warning-500/15 text-warning-700 dark:text-warning-400', $lowStockItems, 'Low Stock Items'],
                        ['document', 'text-warning-600 dark:text-warning-400', 'bg-warning-500/15 text-warning-700 dark:text-warning-400', $pendingApprovalPOs, 'POs Awaiting Approval'],
                        ['calendar', 'text-info-600 dark:text-info-400', 'bg-info-500/15 text-info-700 dark:text-info-400', $pendingLeaves, 'Pending Leave Requests'],
                        ['envelope', 'text-warning-600 dark:text-warning-400', 'bg-warning-500/15 text-warning-700 dark:text-warning-400', $unreadMessages, 'Unread Messages'],
                        ['credit-card', 'text-info-600 dark:text-info-400', 'bg-info-500/15 text-info-700 dark:text-info-400', $membershipDueSoon, 'Gym Billing Due Soon'],
                        ['circle', 'text-danger-600 dark:text-danger-400', 'bg-danger-500/15 text-danger-700 dark:text-danger-400', $failedJobs, 'Failed Queue Jobs'],
                    ];
                    $icons = [
                        'hazard' => 'heroicon-o-exclamation-triangle',
                        'clock' => 'heroicon-o-clock',
                        'exclamation' => 'heroicon-o-bell-alert',
                        'document' => 'heroicon-o-document-text',
                        'calendar' => 'heroicon-o-calendar-days',
                        'envelope' => 'heroicon-o-envelope',
                        'credit-card' => 'heroicon-o-credit-card',
                        'circle' => 'heroicon-o-x-circle',
                    ];
                    $placed = false;
                @endphp
                @foreach ($items as [$icon, $textCls, $badgeCls, $count, $label])
                    @if ($count > 0)
                        @php $placed = true; @endphp
                        <div class="flex items-center justify-between rounded-lg px-2 py-1.5">
                            <span class="inline-flex items-center gap-2 text-sm {{ $textCls }}">
                                <x-filament::icon :icon="$icons[$icon]" class="h-4 w-4" />
                                {{ $label }}
                            </span>
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeCls }}">{{ $count }}</span>
                        </div>
                    @endif
                @endforeach
                @if (! $placed)
                    <p class="py-4 text-center text-sm text-gray-400 dark:text-gray-500">All clear — no action items</p>
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-primary-200/40 bg-white p-4 shadow-sm transition-all duration-200 hover:border-primary-300/60 hover:shadow-md dark:border-primary-500/15 dark:bg-white/5 dark:hover:border-primary-500/40">
            <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">HR Overview</h3>
            <div class="mb-3 grid grid-cols-3 gap-2 text-center">
                <div>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $totalEmployees }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                </div>
                <div>
                    <p class="text-lg font-bold text-success-600 dark:text-success-400">{{ $activeEmployees }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Active</p>
                </div>
                <div>
                    <p class="text-lg font-bold {{ $pendingLeaves > 0 ? 'text-warning-600 dark:text-warning-400' : 'text-gray-900 dark:text-white' }}">{{ $pendingLeaves }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pending Leaves</p>
                </div>
            </div>
            @if ($departments->count())
                @php $maxDept = $departments->max('total') ?: 1; @endphp
                <div class="space-y-1.5">
                    @foreach ($departments as $dept)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-24 truncate text-gray-600 dark:text-gray-300">{{ $dept->department }}</span>
                            <div class="h-2 flex-1 overflow-hidden rounded bg-gray-100 dark:bg-white/10">
                                <div class="h-full rounded" style="width: {{ round(($dept->total / $maxDept) * 100) }}%; background-color: oklch(var(--primary-500));"></div>
                            </div>
                            <span class="text-xs font-semibold text-gray-900 dark:text-white">{{ $dept->total }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-primary-200/40 bg-white p-4 shadow-sm transition-all duration-200 hover:border-primary-300/60 hover:shadow-md dark:border-primary-500/15 dark:bg-white/5 dark:hover:border-primary-500/40">
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $recentLogins }} logins today</span>
            </div>
            @if ($recentActivity->count())
                <div class="space-y-2">
                    @foreach ($recentActivity as $log)
                        <div class="rounded-lg bg-gray-50 px-3 py-2 dark:bg-white/5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-900 dark:text-white">{{ $log->user?->name ?? 'System' }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300">{{ Str::limit($log->description, 70) }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="py-4 text-center text-sm text-gray-400 dark:text-gray-500">No recent activity</p>
            @endif
        </div>
    </div>

    {{-- ═══════════════════ UPCOMING BANQUET EVENTS ═══════════════════ --}}
    <div class="mt-4 rounded-xl border border-primary-200/40 bg-white p-4 shadow-sm transition-all duration-200 hover:border-primary-300/60 hover:shadow-md dark:border-primary-500/15 dark:bg-white/5 dark:hover:border-primary-500/40">
        <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Upcoming Banquet Events</h3>
        @if ($upcomingEvents->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <th class="py-2 pr-4 font-medium">Customer</th>
                            <th class="py-2 pr-4 font-medium">Date</th>
                            <th class="py-2 pr-4 font-medium">Status</th>
                            <th class="py-2 text-right font-medium">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($upcomingEvents as $event)
                            <tr class="border-b border-gray-100 last:border-0 dark:border-white/5">
                                <td class="py-2 pr-4 font-medium text-gray-900 dark:text-white">{{ $event->customer?->name ?? 'N/A' }}</td>
                                <td class="py-2 pr-4 text-gray-600 dark:text-gray-300">{{ $event->earliest_event_date ? \Carbon\Carbon::parse($event->earliest_event_date)->format('M d, Y') : '—' }}</td>
                                <td class="py-2 pr-4">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $event->status === 'Confirmed' ? 'bg-success-500/15 text-success-700 dark:text-success-400' : 'bg-warning-500/15 text-warning-700 dark:text-warning-400' }}">{{ $event->status }}</span>
                                </td>
                                <td class="py-2 text-right font-semibold text-gray-900 dark:text-white">₦{{ number_format($event->total_revenue) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="py-4 text-center text-sm text-gray-400 dark:text-gray-500">No upcoming events scheduled</p>
        @endif
    </div>
</x-filament-panels::page>