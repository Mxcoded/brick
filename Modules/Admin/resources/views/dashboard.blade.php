@extends('layouts.master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-charcoal mb-1">Executive Dashboard</h3>
        <p class="text-muted mb-0">{{ now()->format('l, F d, Y') }} &middot; {{ $activeUsersToday }} active users today</p>
    </div>
    <div class="d-flex align-items-center gap-3">
        @if($failedJobs > 0)
        <span class="badge bg-danger rounded-pill px-3 py-2"><i class="fas fa-exclamation-triangle me-1"></i> {{ $failedJobs }} failed jobs</span>
        @endif
        <span class="text-muted small">Welcome, {{ Auth::user()->name }}</span>
    </div>
</div>

{{-- ═══════════════════ STRATEGIC KPI BANNER ═══════════════════ --}}
<div class="row g-2 mb-4">
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;" title="Total income from Banquet, Front Desk, Gym, and Website modules combined. Compare month-over-month to see growth trends.">
            <div class="card-body py-3">
                <p class="x-small text-muted text-uppercase mb-0">Total Revenue</p>
                <h4 class="fw-bold mb-0">₦{{ number_format($totalRevenue) }}</h4>
                @if($revenueChange != 0)
                <small class="text-{{ $revenueDirection === 'up' ? 'success' : 'danger' }}">
                    <i class="fas fa-arrow-{{ $revenueDirection }} me-1"></i>{{ abs($revenueChange) }}% vs last month
                </small>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;" title="Cumulative income from January 1st to today across all revenue-generating departments.">
            <div class="card-body py-3">
                <p class="x-small text-muted text-uppercase mb-0">YTD Revenue</p>
                <h4 class="fw-bold mb-0">₦{{ number_format($ytdRevenue) }}</h4>
                <small class="text-muted">{{ now()->year }} year-to-date</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0dcaf0 !important;" title="Percentage of hotel room units currently occupied. Higher occupancy means better room revenue. Target is 70%+.">
            <div class="card-body py-3">
                <p class="x-small text-muted text-uppercase mb-0">Occupancy</p>
                <h4 class="fw-bold mb-0">{{ $occupancyRate }}%</h4>
                <small class="text-{{ $occupancyChange >= 0 ? 'success' : 'danger' }}">
                    <i class="fas fa-arrow-{{ $occupancyChange >= 0 ? 'up' : 'down' }} me-1"></i>{{ abs($occupancyChange) }}pp
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6f42c1 !important;" title="Employees currently employed (no end date recorded). Shows total headcount vs active workforce.">
            <div class="card-body py-3">
                <p class="x-small text-muted text-uppercase mb-0">Active Staff</p>
                <h4 class="fw-bold mb-0">{{ $activeEmployees }}</h4>
                <small class="text-muted">of {{ $totalEmployees }} total</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;" title="Total urgent items requiring your decision: critical maintenance, overdue tasks, low stock, unread messages, POs awaiting approval, and failed jobs.">
            <div class="card-body py-3">
                <p class="x-small text-muted text-uppercase mb-0">Critical Alerts</p>
                <h4 class="fw-bold mb-0 @if($criticalAlerts > 0) text-danger @endif">{{ $criticalAlerts }}</h4>
                <small class="text-muted">needs attention</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #20c997 !important;" title="Number of guests currently staying in the hotel. Shows how many rooms are occupied out of the total available units.">
            <div class="card-body py-3">
                <p class="x-small text-muted text-uppercase mb-0">Checked In</p>
                <h4 class="fw-bold mb-0">{{ $checkedIn }}</h4>
                <small class="text-muted">{{ $totalRoomUnits }} total units</small>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════ REVENUE TREND CHART ═══════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100" title="Monthly revenue broken down by department. Stacked bars show each department's contribution. Hover over the legend to see which color represents which department.">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2 text-gold"></i>Revenue Trend (Last 6 Months)</h6>
                <small class="text-muted">This month: ₦{{ number_format($revenueThisMonth) }}</small>
            </div>
            <div class="card-body">
                @php
                    $maxRev = max(array_merge($monthlyBanquet, $monthlyFrontdesk, $monthlyGym, $monthlyWebsite));
                    $maxRev = $maxRev > 0 ? $maxRev : 1;
                    $totals = array_map(function($b, $f, $g, $w) { return $b + $f + $g + $w; }, $monthlyBanquet, $monthlyFrontdesk, $monthlyGym, $monthlyWebsite);
                    $maxTotal = max($totals) ?: 1;
                @endphp
                <div class="d-flex align-items-end gap-2" style="height: 200px;">
                    @foreach($revenueMonths as $idx => $month)
                    @php
                        $total = $totals[$idx];
                        $pct = round(($total / $maxTotal) * 100);
                        $bPct = $maxRev > 0 ? round(($monthlyBanquet[$idx] / $maxRev) * 100) : 0;
                        $fPct = $maxRev > 0 ? round(($monthlyFrontdesk[$idx] / $maxRev) * 100) : 0;
                        $gPct = $maxRev > 0 ? round(($monthlyGym[$idx] / $maxRev) * 100) : 0;
                        $wPct = $maxRev > 0 ? round(($monthlyWebsite[$idx] / $maxRev) * 100) : 0;
                    @endphp
                    <div class="flex-fill d-flex flex-column align-items-center">
                        <small class="fw-bold mb-1">₦{{ number_format($total) }}</small>
                        <div class="w-100 d-flex flex-column-reverse" style="height: 160px;">
                            <div class="w-100 rounded-top" style="height: {{ $pct }}%; background: linear-gradient(to top, #C5A572, #e8d5a3); transition: height 0.3s;">
                                <div class="d-flex flex-column-reverse h-100">
                                    @if($wPct > 0)<div style="height: {{ $total > 0 ? round(($monthlyWebsite[$idx]/$total)*$pct) : 0 }}%; background: #20c997; opacity: 0.85;"></div>@endif
                                    @if($gPct > 0)<div style="height: {{ $total > 0 ? round(($monthlyGym[$idx]/$total)*$pct) : 0 }}%; background: #6f42c1; opacity: 0.85;"></div>@endif
                                    @if($fPct > 0)<div style="height: {{ $total > 0 ? round(($monthlyFrontdesk[$idx]/$total)*$pct) : 0 }}%; background: #0dcaf0; opacity: 0.85;"></div>@endif
                                    @if($bPct > 0)<div style="height: {{ $total > 0 ? round(($monthlyBanquet[$idx]/$total)*$pct) : 0 }}%; background: #0d6efd; opacity: 0.85;"></div>@endif
                                </div>
                            </div>
                        </div>
                        <small class="mt-1 text-muted">{{ $month }}</small>
                    </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-center gap-4 mt-3">
                    <span><span class="badge" style="background:#0d6efd">&nbsp;</span> Banquet</span>
                    <span><span class="badge" style="background:#0dcaf0">&nbsp;</span> Front Desk</span>
                    <span><span class="badge" style="background:#6f42c1">&nbsp;</span> Gym</span>
                    <span><span class="badge" style="background:#20c997">&nbsp;</span> Website</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" title="Side-by-side comparison of total revenue for the current month versus the previous month. The arrow indicator shows whether revenue is growing or declining.">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-flag me-2 text-gold"></i>This Month vs Last Month</h6>
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="text-center mb-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="p-3 rounded bg-light">
                                <small class="text-muted text-uppercase d-block">This Month</small>
                                <h4 class="fw-bold mb-0 text-success">₦{{ number_format($revenueThisMonth) }}</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded bg-light">
                                <small class="text-muted text-uppercase d-block">Last Month</small>
                                <h4 class="fw-bold mb-0 text-muted">₦{{ number_format($revenueLastMonth) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    @if($revenueChange > 0)
                    <div class="fs-1 text-success"><i class="fas fa-arrow-circle-up"></i></div>
                    <h5 class="text-success fw-bold">+{{ $revenueChange }}% growth</h5>
                    @elseif($revenueChange < 0)
                    <div class="fs-1 text-danger"><i class="fas fa-arrow-circle-down"></i></div>
                    <h5 class="text-danger fw-bold">{{ $revenueChange }}% decline</h5>
                    @else
                    <div class="fs-1 text-secondary"><i class="fas fa-minus-circle"></i></div>
                    <h5 class="text-secondary fw-bold">No change</h5>
                    @endif
                    <p class="text-muted small mb-0">Month-over-month revenue change</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════ MODULE HEALTH & ALERTS ═══════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" title="Hotel front desk status: how many guests are checked in, have future reservations, and are arriving or leaving today. The green/orange/red badge shows occupancy health.">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold small"><i class="fas fa-hotel me-1 text-gold"></i>Front Desk</h6>
                <span class="badge bg-{{ $occupancyRate >= 70 ? 'success' : ($occupancyRate >= 40 ? 'warning' : 'danger') }} rounded-pill">{{ $occupancyRate }}%</span>
            </div>
            <div class="card-body py-2">
                <div class="d-flex justify-content-between small mb-1"><span>Checked In</span><span class="fw-bold">{{ $checkedIn }}</span></div>
                <div class="d-flex justify-content-between small mb-1"><span>Reserved</span><span class="fw-bold">{{ $reservations }}</span></div>
                <div class="d-flex justify-content-between small mb-1"><span>Check-ins Today</span><span class="fw-bold">{{ $todayCheckins }}</span></div>
                <div class="d-flex justify-content-between small"><span>Check-outs Today</span><span class="fw-bold">{{ $todayCheckouts }}</span></div>
                <hr class="my-1">
                <div class="d-flex justify-content-between small"><span>Revenue (Month)</span><span class="fw-bold">₦{{ number_format($frontdeskRevenueMonth) }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" title="Banquet and event orders overview: tracks event bookings from pending through to completion, plus new enquiries that haven't converted yet. Outstanding shows unpaid balances.">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold small"><i class="fas fa-utensils me-1 text-gold"></i>Banquet &amp; Events</h6>
                <span class="badge bg-{{ $banquetOrdersPending == 0 ? 'success' : 'warning' }} rounded-pill">{{ $banquetOrdersTotal }} orders</span>
            </div>
            <div class="card-body py-2">
                <div class="d-flex justify-content-between small mb-1"><span>Pending</span><span class="fw-bold text-warning">{{ $banquetOrdersPending }}</span></div>
                <div class="d-flex justify-content-between small mb-1"><span>Confirmed</span><span class="fw-bold text-success">{{ $banquetOrdersConfirmed }}</span></div>
                <div class="d-flex justify-content-between small mb-1"><span>Completed</span><span class="fw-bold text-info">{{ $banquetOrdersCompleted }}</span></div>
                <div class="d-flex justify-content-between small"><span>Enquiries</span><span class="fw-bold">{{ $pendingEnquiries }}</span></div>
                <hr class="my-1">
                <div class="d-flex justify-content-between small"><span>Revenue</span><span class="fw-bold">₦{{ number_format($banquetRevenue) }}</span></div>
                <div class="d-flex justify-content-between small"><span>Outstanding</span><span class="fw-bold text-danger">₦{{ number_format($banquetOutstanding) }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" title="Restaurant order activity: shows how many food and beverage orders were placed today, how many are still pending preparation, and the monthly total volume.">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold small"><i class="fas fa-concierge-bell me-1 text-gold"></i>Restaurant</h6>
                <span class="badge bg-info rounded-pill">{{ $restaurantOrdersMonth }} month</span>
            </div>
            <div class="card-body py-2">
                <div class="d-flex justify-content-between small mb-1"><span>Orders Today</span><span class="fw-bold fs-5 text-primary">{{ $restaurantOrdersToday }}</span></div>
                <div class="d-flex justify-content-between small mb-1"><span>Pending Orders</span><span class="fw-bold text-warning">{{ $restaurantOrdersPending }}</span></div>
                <div class="d-flex justify-content-between small"><span>This Month</span><span class="fw-bold">{{ $restaurantOrdersMonth }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" title="Gym membership status: number of active members, revenue collected this month, upcoming billing dates within 7 days, and total lifetime revenue from the gym.">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold small"><i class="fas fa-dumbbell me-1 text-gold"></i>Gym</h6>
                <span class="badge bg-{{ $activeMemberships > 0 ? 'success' : 'secondary' }} rounded-pill">{{ $activeMemberships }} active</span>
            </div>
            <div class="card-body py-2">
                <div class="d-flex justify-content-between small mb-1"><span>Active Memberships</span><span class="fw-bold">{{ $activeMemberships }}</span></div>
                <div class="d-flex justify-content-between small mb-1"><span>Revenue (Month)</span><span class="fw-bold">₦{{ number_format($gymPaymentsMonth) }}</span></div>
                <div class="d-flex justify-content-between small"><span>Billing Due (&le;7d)</span><span class="fw-bold @if($membershipDueSoon > 0) text-warning @endif">{{ $membershipDueSoon }}</span></div>
                <hr class="my-1">
                <div class="d-flex justify-content-between small"><span>Total Revenue</span><span class="fw-bold">₦{{ number_format($gymRevenue) }}</span></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" title="Facility maintenance summary: open work tickets, critical issues needing urgent response, repairs completed this month, total maintenance costs incurred, and breakdown by department (IT, Electrical, Plumbing, etc.).">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold small"><i class="fas fa-wrench me-1 text-gold"></i>Maintenance</h6>
                <span class="badge bg-{{ $maintenanceCritical > 0 ? 'danger' : ($maintenanceOpen > 0 ? 'warning' : 'success') }} rounded-pill">{{ $maintenanceOpen }} open</span>
            </div>
            <div class="card-body py-2">
                <div class="d-flex justify-content-between small mb-1"><span>Open</span><span class="fw-bold @if($maintenanceOpen > 0) text-warning @endif">{{ $maintenanceOpen }}</span></div>
                <div class="d-flex justify-content-between small mb-1"><span>Critical</span><span class="fw-bold @if($maintenanceCritical > 0) text-danger @endif">{{ $maintenanceCritical }}</span></div>
                <div class="d-flex justify-content-between small mb-1"><span>Completed (Month)</span><span class="fw-bold text-success">{{ $maintenanceCompletedMonth }}</span></div>
                <hr class="my-1">
                <div class="d-flex justify-content-between small"><span>Total Cost</span><span class="fw-bold">₦{{ number_format($maintenanceCost) }}</span></div>
                @if($latestGenReadings->isNotEmpty())
                <hr class="my-1">
                <p class="x-small text-muted text-uppercase mb-1">Today's Gen Readings</p>
                @foreach($latestGenReadings as $gen)
                <div class="d-flex justify-content-between x-small">
                    <span>{{ str_replace('_', ' ', ucfirst($gen->category)) }}</span>
                    <span class="fw-bold">{{ $gen->reading_value }}% ({{ number_format($gen->calculated_value, 2) }})</span>
                </div>
                @endforeach
                @endif
                @if($latestDieselReading)
                <div class="d-flex justify-content-between x-small">
                    <span>Diesel Reservoir</span>
                    <span class="fw-bold">{{ number_format($latestDieselReading->reading_value) }}L</span>
                </div>
                @endif
                @if($latestWaterReading)
                <div class="d-flex justify-content-between x-small">
                    <span>Water Tank</span>
                    <span class="fw-bold">{{ $latestWaterReading->reading_value }}%</span>
                </div>
                @endif
                @if($maintenanceByDept->count())
                <div class="mt-1">
                    @foreach($maintenanceByDept as $dept)
                    <div class="d-flex justify-content-between small text-muted">
                        <span>{{ $dept->department }}</span><span>{{ $dept->total }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" title="Task management overview: tracks tasks at each stage from pending to completed. Overdue tasks have passed their deadline. High priority items need immediate assignment. Monthly completions show team productivity.">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold small"><i class="fas fa-tasks me-1 text-gold"></i>Tasks</h6>
                <span class="badge bg-{{ $tasksOverdue > 0 ? 'danger' : ($tasksPending > 0 ? 'warning' : 'success') }} rounded-pill">{{ $tasksPending }} pending</span>
            </div>
            <div class="card-body py-2">
                <div class="d-flex justify-content-between small mb-1"><span>Pending</span><span class="fw-bold text-warning">{{ $tasksPending }}</span></div>
                <div class="d-flex justify-content-between small mb-1"><span>In Progress</span><span class="fw-bold text-primary">{{ $tasksInProgress }}</span></div>
                <div class="d-flex justify-content-between small mb-1"><span>Overdue</span><span class="fw-bold @if($tasksOverdue > 0) text-danger @endif">{{ $tasksOverdue }}</span></div>
                <div class="d-flex justify-content-between small mb-1"><span>High Priority</span><span class="fw-bold @if($highPriorityTasks > 0) text-danger @endif">{{ $highPriorityTasks }}</span></div>
                <div class="d-flex justify-content-between small"><span>Completed (Month)</span><span class="fw-bold text-success">{{ $tasksCompletedMonth }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" title="Stock and purchasing status: items that have fallen below minimum stock levels, total purchase orders still open, and POs specifically waiting for your approval to proceed.">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold small"><i class="fas fa-boxes me-1 text-gold"></i>Inventory</h6>
                <span class="badge bg-{{ $lowStockItems > 0 ? 'danger' : 'success' }} rounded-pill">{{ $lowStockItems }} low</span>
            </div>
            <div class="card-body py-2">
                <div class="d-flex justify-content-between small mb-1"><span>Low Stock Alerts</span><span class="fw-bold @if($lowStockItems > 0) text-danger @endif">{{ $lowStockItems }}</span></div>
                <div class="d-flex justify-content-between small mb-1"><span>Pending POs</span><span class="fw-bold text-warning">{{ $pendingPOs }}</span></div>
                <div class="d-flex justify-content-between small"><span>Awaiting Approval</span><span class="fw-bold @if($pendingApprovalPOs > 0) text-danger @endif">{{ $pendingApprovalPOs }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" title="Online channel performance: bookings placed via the website this month, revenue generated online, confirmed reservations, and customer contact messages awaiting a reply.">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold small"><i class="fas fa-globe me-1 text-gold"></i>Website</h6>
                <span class="badge bg-{{ $unreadMessages > 0 ? 'warning' : 'success' }} rounded-pill">{{ $unreadMessages }} unread</span>
            </div>
            <div class="card-body py-2">
                <div class="d-flex justify-content-between small mb-1"><span>Bookings (Month)</span><span class="fw-bold">{{ $websiteBookingsMonth }}</span></div>
                <div class="d-flex justify-content-between small mb-1"><span>Revenue (Month)</span><span class="fw-bold">₦{{ number_format($websiteRevenueMonth) }}</span></div>
                <div class="d-flex justify-content-between small mb-1"><span>Confirmed</span><span class="fw-bold text-success">{{ $confirmedBookings }}</span></div>
                <div class="d-flex justify-content-between small"><span>Unread Msgs</span><span class="fw-bold @if($unreadMessages > 0) text-danger @endif">{{ $unreadMessages }}</span></div>
                <hr class="my-1">
                <div class="d-flex justify-content-between small"><span>Total Revenue</span><span class="fw-bold">₦{{ number_format($websiteRevenue) }}</span></div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════ BOTTOM ROW: ALERTS, HR, ACTIVITY ═══════════════════ --}}
<div class="row g-4">
    {{-- Alerts Panel --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" title="Consolidated list of items needing your review or approval across all departments. Red items are urgent, yellow/warning items need attention soon, blue items are informational.">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-bell me-2 text-gold"></i>Action Items</h6>
                @if($criticalAlerts > 0)
                <span class="badge bg-danger rounded-pill">{{ $criticalAlerts }} alerts</span>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @if($maintenanceCritical > 0)
                    <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-wrench text-danger me-2"></i> Critical Maintenance</div>
                        <span class="badge bg-danger rounded-pill">{{ $maintenanceCritical }}</span>
                    </div>
                    @endif
                    @if($tasksOverdue > 0)
                    <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-clock text-danger me-2"></i> Overdue Tasks</div>
                        <span class="badge bg-danger rounded-pill">{{ $tasksOverdue }}</span>
                    </div>
                    @endif
                    @if($lowStockItems > 0)
                    <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-exclamation-triangle text-warning me-2"></i> Low Stock Items</div>
                        <span class="badge bg-warning rounded-pill">{{ $lowStockItems }}</span>
                    </div>
                    @endif
                    @if($pendingApprovalPOs > 0)
                    <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-file-invoice text-warning me-2"></i> POs Awaiting Approval</div>
                        <span class="badge bg-warning rounded-pill">{{ $pendingApprovalPOs }}</span>
                    </div>
                    @endif
                    @if($pendingLeaves > 0)
                    <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-calendar-alt text-info me-2"></i> Pending Leave Requests</div>
                        <span class="badge bg-info rounded-pill">{{ $pendingLeaves }}</span>
                    </div>
                    @endif
                    @if($unreadMessages > 0)
                    <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-envelope text-warning me-2"></i> Unread Messages</div>
                        <span class="badge bg-warning rounded-pill">{{ $unreadMessages }}</span>
                    </div>
                    @endif
                    @if($membershipDueSoon > 0)
                    <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-credit-card text-info me-2"></i> Gym Billing Due Soon</div>
                        <span class="badge bg-info rounded-pill">{{ $membershipDueSoon }}</span>
                    </div>
                    @endif
                    @if($failedJobs > 0)
                    <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-exclamation-circle text-danger me-2"></i> Failed Queue Jobs</div>
                        <span class="badge bg-danger rounded-pill">{{ $failedJobs }}</span>
                    </div>
                    @endif
                    @if($criticalAlerts == 0)
                    <div class="text-center text-muted py-4 small">All clear — no action items</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- HR Overview --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" title="Human resources summary: total and active employees, pending leave requests awaiting approval, and headcount distribution across departments.">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-users me-2 text-gold"></i>HR Overview</h6>
                <a href="{{ route('staff.index') }}" class="small text-decoration-none">Manage &raquo;</a>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-4 text-center">
                        <div class="fw-bold fs-5">{{ $totalEmployees }}</div>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="col-4 text-center">
                        <div class="fw-bold fs-5 text-success">{{ $activeEmployees }}</div>
                        <small class="text-muted">Active</small>
                    </div>
                    <div class="col-4 text-center">
                        <div class="fw-bold fs-5 @if($pendingLeaves > 0) text-warning @endif">{{ $pendingLeaves }}</div>
                        <small class="text-muted">Pending Leaves</small>
                    </div>
                </div>
                @if($departments->count())
                <hr class="my-2">
                <p class="small text-muted text-uppercase mb-2">By Department</p>
                @php $maxDept = $departments->max('total') ?: 1; @endphp
                @foreach($departments as $dept)
                <div class="d-flex align-items-center gap-2 small mb-1">
                    <span style="min-width:100px;">{{ $dept->department }}</span>
                    <div class="flex-grow-1 bg-light rounded" style="height: 8px;">
                        <div class="rounded h-100" style="width: {{ round(($dept->total/$maxDept)*100) }}%; background: #C5A572;"></div>
                    </div>
                    <span class="fw-bold">{{ $dept->total }}</span>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Activity / Upcoming Events --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" title="Real-time log of user actions across the system — who did what and when. Shows the last 10 activities including logins, record creation, updates, and more.">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-gold"></i>Recent Activity</h6>
                <span class="small text-muted">{{ $recentLogins }} logins today</span>
            </div>
            <div class="card-body p-0">
                @if($recentActivity->count())
                <ul class="list-group list-group-flush">
                    @foreach($recentActivity as $log)
                    <li class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between">
                            <span class="small fw-semibold">{{ $log->user?->name ?? 'System' }}</span>
                            <span class="small text-muted">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <small class="text-muted">{{ Str::limit($log->description, 70) }}</small>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center text-muted py-4 small">No recent activity</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════ UPCOMING EVENTS ═══════════════════ --}}
<div class="row g-3 mt-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm" title="Scheduled banquet and event bookings in the coming days. Shows customer name, event date, current status (Confirmed/Warning), and expected revenue.">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-alt me-2 text-gold"></i>Upcoming Banquet Events</h6>
            </div>
            <div class="card-body p-0">
                @if($upcomingEvents->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small">Customer</th>
                                <th class="small">Date</th>
                                <th class="small">Status</th>
                                <th class="small text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingEvents as $event)
                            <tr>
                                <td><a href="{{ route('banquet.orders.show', $event->order_id) }}" class="text-decoration-none">{{ $event->customer?->name ?? 'N/A' }}</a></td>
                                <td class="small">{{ $event->earliest_event_date?->format('M d, Y') ?: '—' }}</td>
                                <td><span class="badge bg-{{ $event->status === 'Confirmed' ? 'success' : 'warning' }}">{{ $event->status }}</span></td>
                                <td class="small text-end fw-bold">₦{{ number_format($event->total_revenue) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center text-muted py-4 small">
                    <i class="fas fa-calendar-day fa-2x mb-2 opacity-25 d-block"></i>
                    No upcoming events scheduled
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .x-small { font-size: 0.7rem; }
    .card { border-radius: 10px; }
    .card-header { border-radius: 10px 10px 0 0 !important; border-bottom: 2px solid #f0f0f0; }
    .list-group-item { border-left: none; border-right: none; }
</style>
@endsection