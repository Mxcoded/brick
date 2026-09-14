@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Banquet Operations</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 banquet-theme">
    
    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 1. DASHBOARD STATS ROW --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-gold text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Total Revenue</h6>
                            <h3 class="fw-bold mb-0">₦{{ number_format($stats['total_revenue']) }}</h3>
                        </div>
                        <div class="icon-circle bg-white text-gold rounded-circle p-3">
                            <i class="fas fa-coins fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Amount Paid</h6>
                            <h3 class="fw-bold mb-0">₦{{ number_format($stats['total_paid']) }}</h3>
                        </div>
                        <div class="icon-circle bg-white text-success rounded-circle p-3">
                            <i class="fas fa-hand-holding-usd fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Outstanding Balance</h6>
                            <h3 class="fw-bold mb-0">₦{{ number_format($stats['total_balance']) }}</h3>
                        </div>
                        <div class="icon-circle bg-white text-danger rounded-circle p-3">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Total Customers</h6>
                            <h3 class="fw-bold mb-0">{{ $stats['total_customers'] }}</h3>
                        </div>
                        <div class="icon-circle bg-white text-info rounded-circle p-3">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. SECOND STATS ROW --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small text-muted">Total Orders</h6>
                            <h3 class="fw-bold mb-0 text-charcoal">{{ $stats['total_orders'] }}</h3>
                        </div>
                        <div class="icon-circle bg-light text-charcoal rounded-circle p-3">
                            <i class="fas fa-file-invoice fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small text-muted">Pending Actions</h6>
                            <h3 class="fw-bold mb-0 text-gold">{{ $stats['pending_orders'] }}</h3>
                        </div>
                        <div class="icon-circle bg-gold-subtle text-gold rounded-circle p-3">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <button type="button" class="card-link w-100 border-0 bg-transparent p-0" data-bs-toggle="modal" data-bs-target="#eventsThisMonthModal">
                <div class="card border-0 shadow-sm h-100 bg-white hover-scale">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1 small text-muted">Events This Month</h6>
                                <h3 class="fw-bold mb-0 text-success">{{ $stats['this_month_events'] }}</h3>
                            </div>
                            <div class="icon-circle bg-success-subtle text-success rounded-circle p-3">
                                <i class="fas fa-calendar-check fa-lg"></i>
                            </div>
                        </div>
                        <div class="mt-2 text-end">
                            <small class="text-muted">View details <i class="fas fa-arrow-right"></i></small>
                        </div>
                    </div>
                </div>
            </button>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small text-muted">Order Status</h6>
                            <div class="d-flex gap-2 flex-wrap mt-2">
                                @foreach ($statusBreakdown as $status => $count)
                                    @php $colors = ['Pending' => 'warning', 'Confirmed' => 'primary', 'Completed' => 'success', 'Cancelled' => 'danger']; @endphp
                                    <span class="badge bg-{{ $colors[$status] }} rounded-pill px-2">{{ $status }}: {{ $count }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="icon-circle bg-light text-muted rounded-circle p-3 align-self-start">
                            <i class="fas fa-chart-pie fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Events This Month Modal --}}
    <div class="modal fade" id="eventsThisMonthModal" tabindex="-1" aria-labelledby="eventsThisMonthModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="eventsThisMonthModalLabel">
                        <i class="fas fa-calendar-check me-2"></i>Events This Month ({{ now()->format('F Y') }})
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    @if($thisMonthOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light text-uppercase small text-muted">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Client / Org</th>
                                        <th>Event Date</th>
                                        <th>Type</th>
                                        <th>Guests</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($thisMonthOrders as $order)
                                        @foreach($order->eventDays as $day)
                                            <tr>
                                                <td><span class="fw-bold text-gold">#{{ $order->order_id }}</span></td>
                                                <td>
                                                    <span class="fw-bold">{{ $order->customer?->name }}</span>
                                                    @if($order->customer?->organization && $order->customer->organization !== 'Private')
                                                        <br><small class="text-muted"><i class="fas fa-building me-1"></i>{{ $order->customer->organization }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($day->event_date)->format('d M Y') }}</td>
                                                <td><span class="badge bg-info">{{ $day->event_type ?? '—' }}</span></td>
                                                <td class="text-center">{{ number_format($day->guest_count) }}</td>
                                                <td class="text-center">
                                                    @php
                                                        $colors = ['Pending' => 'warning', 'Confirmed' => 'primary', 'Completed' => 'success', 'Cancelled' => 'danger'];
                                                    @endphp
                                                    <span class="badge bg-{{ $colors[$order->status] ?? 'secondary' }} rounded-pill px-3">{{ $order->status }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar text-muted fa-3x mb-3"></i>
                            <h5>No events this month</h5>
                            <p class="text-muted mb-0">There are no events scheduled for {{ now()->format('F Y') }}.</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <a href="{{ route('banquet.reports.form') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-file-pdf me-1"></i> Generate Report
                    </a>
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. UPCOMING THIS WEEK --}}
    @if($weeklyUpcoming->isNotEmpty())
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-gold"><i class="fas fa-calendar-week me-2"></i>Upcoming This Week</h5>
            @can('banquet.create')
            <a href="{{ route('banquet.orders.create') }}" class="btn btn-sm btn-gold"><i class="fas fa-plus me-1"></i> New Event</a>
            @endcan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light text-uppercase small text-muted">
                        <tr>
                            <th>Date</th>
                            <th>Event</th>
                            <th>Client</th>
                            <th>Venue</th>
                            <th>Guests</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weeklyUpcoming as $day)
                            <tr>
                                <td class="fw-bold">{{ \Carbon\Carbon::parse($day->event_date)->format('d M') }}</td>
                                <td>{{ $day->event_type ?? '—' }}</td>
                                <td>{{ $day->banquetOrder->customer?->name ?? $day->banquetOrder->contact_person_name ?? '—' }}</td>
                                <td>{{ $day->venue?->name ?? '—' }}</td>
                                <td class="text-center">{{ number_format($day->guest_count) }}</td>
                                <td>{{ $day->start_time ? \Carbon\Carbon::parse($day->start_time)->format('g:i A') : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- 4. EVENT CALENDAR + VENUE DENSITY --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-bold text-gold"><i class="fas fa-calendar-alt me-2"></i>Event Calendar
                <span class="fs-6 text-muted fw-normal ms-1">{{ $calendar['month']->format('F Y') }}</span>
            </h5>
            <div class="btn-group" role="group" aria-label="Calendar navigation">
                <a href="{{ route('banquet.index', ['month' => $calendar['prevMonth']->format('Y-m')]) }}" class="btn btn-sm btn-outline-charcoal" title="Previous month">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <a href="{{ route('banquet.index') }}" class="btn btn-sm btn-outline-charcoal">Today</a>
                <a href="{{ route('banquet.index', ['month' => $calendar['nextMonth']->format('Y-m')]) }}" class="btn btn-sm btn-outline-charcoal" title="Next month">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="cal-grid">
                        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                            <div class="cal-weekday">{{ $weekday }}</div>
                        @endforeach
                        @foreach ($calendar['weeks'] as $week)
                            @foreach ($week as $day)
                                @php
                                    $dayKey = $day->toDateString();
                                    $dayEvents = $calendar['eventsByDate'][$dayKey] ?? collect();
                                    $count = $dayEvents->count();
                                    $inMonth = $day->isSameMonth($calendar['month']);
                                    $isToday = $day->isToday();
                                    $densityClass = match (true) {
                                        $count >= 4 => 'cal-density-4',
                                        $count === 3 => 'cal-density-3',
                                        $count === 2 => 'cal-density-2',
                                        $count === 1 => 'cal-density-1',
                                        default => '',
                                    };
                                    $tooltip = $dayEvents
                                        ->map(fn ($ev) => ($ev->venue?->name ?? '—').' · '.($ev->event_type ?? 'Event'))
                                        ->implode('<br>');
                                @endphp
                                <div class="cal-cell {{ $inMonth ? '' : 'out-month' }} {{ $isToday ? 'today' : '' }} {{ $densityClass }}"
                                    @if ($count > 0) data-bs-toggle="tooltip" data-bs-html="true" data-bs-title="{{ $tooltip }}" @endif>
                                    <span class="cal-day-num">{{ $day->day }}</span>
                                    @if ($count > 0)
                                        <span class="cal-count-badge">{{ $count }}</span>
                                    @endif
                                    <div class="cal-event-list">
                                        @foreach ($dayEvents->take(2) as $ev)
                                            <a href="{{ route('banquet.orders.show', $ev->banquetOrder->order_id) }}"
                                               class="cal-chip" title="{{ $ev->event_description }}">
                                                {{ $ev->venue?->name ?? '—' }}
                                            </a>
                                        @endforeach
                                        @if ($count > 2)
                                            <span class="cal-chip cal-chip-more">+{{ $count - 2 }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                    <div class="d-flex align-items-center justify-content-between small mt-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <i class="fas fa-chart-line text-gold"></i>
                            <span>{{ $calendar['monthEventTotal'] }} event day{{ $calendar['monthEventTotal'] === 1 ? '' : 's' }} scheduled this month.</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted">Busy:</span>
                            <span class="cal-legend cal-lg-1"></span>
                            <span class="cal-legend cal-lg-2"></span>
                            <span class="cal-legend cal-lg-3"></span>
                            <span class="cal-legend cal-lg-4"></span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 h-100 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-charcoal mb-3"><i class="fas fa-chart-area text-gold me-2"></i>Venue Density — {{ $calendar['month']->format('M Y') }}</h6>

                            @if ($calendar['monthEventTotal'] === 0)
                                <div class="text-center py-5">
                                    <i class="fas fa-chart-bar text-muted fa-2x mb-2"></i>
                                    <p class="text-muted small mb-0">No bookings this month. Bookings per venue will appear here.</p>
                                </div>
                            @else
                                @foreach ($calendar['venueDensity'] as $venue)
                                    @php
                                        $ratio = $venue['count'] / $calendar['densityMax'];
                                        $barColor = match (true) {
                                            $ratio >= 0.75 => '#b08c54',
                                            $ratio >= 0.5 => '#C8A165',
                                            $ratio >= 0.25 => '#dcc491',
                                            default => '#eadfc9',
                                        };
                                    @endphp
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between small align-items-center mb-1">
                                            <span class="fw-semibold text-charcoal text-truncate me-2" title="{{ $venue['name'] }}">{{ $venue['name'] }}</span>
                                            <span class="fw-bold text-gold">{{ $venue['count'] }}</span>
                                        </div>
                                        <div class="progress" style="height: 8px;" role="progressbar" aria-label="{{ $venue['name'] }} bookings" aria-valuenow="{{ $venue['count'] }}" aria-valuemin="0" aria-valuemax="{{ $calendar['densityMax'] }}">
                                            <div class="progress-bar" style="width: {{ $ratio * 100 }}%; background-color: {{ $barColor }};"></div>
                                        </div>
                                    </div>
                                @endforeach

                                <hr class="my-3">

                                @if ($calendar['topVenue'])
                                    <div class="d-flex justify-content-between small mb-2">
                                        <span class="text-muted">Most booked venue</span>
                                        <span class="fw-bold text-charcoal">{{ $calendar['topVenue']['name'] }} ({{ $calendar['topVenue']['count'] }})</span>
                                    </div>
                                @endif
                                @if ($calendar['busiest'])
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">Busiest day</span>
                                        <span class="fw-bold text-charcoal">{{ \Carbon\Carbon::parse($calendar['busiest']['date'])->format('d M') }} ({{ $calendar['busiest']['count'] }} {{ Str::plural('event', $calendar['busiest']['count']) }})</span>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. MAIN CONTENT AREA --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-gold"><i class="fas fa-list me-2"></i>Order Management</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('banquet.reports.form') }}" class="btn btn-outline-charcoal btn-sm">
                        <i class="fas fa-file-pdf me-2"></i>Reports
                    </a>
                    @can('banquet.create')
                    <a href="{{ route('banquet.orders.create') }}" class="btn btn-gold btn-sm">
                        <i class="fas fa-plus me-2"></i>New Order
                    </a>
                    @endcan
                </div>
        </div>
        
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="customSearch" class="form-control border-start-0 ps-0" placeholder="Search client, ID, or phone...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="statusFilter" class="form-select">
                        <option value="">Filter by Status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5 text-end align-self-center">
                    <small class="text-muted" id="resultCount">Loading data...</small>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="ordersTable" width="100%">
                    <thead class="bg-light text-uppercase small text-muted">
                        <tr>
                            <th>#</th>
                            <th>Order ID</th>
                            <th>Client / Org</th>
                            <th>Event Dates</th>
                            <th class="text-center">Guests</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-center">Margin</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* BANQUET MODULE LUXURY THEME */
    .banquet-theme {
        font-family: 'Proxima Nova', Arial, Helvetica, sans-serif;
        color: #333333;
    }
    
    /* COLORS */
    .text-gold { color: #C8A165 !important; }
    .text-charcoal { color: #333333 !important; }
    
    .bg-gold { background-color: #C8A165 !important; }
    .bg-gold-subtle { background-color: rgba(200, 161, 101, 0.1) !important; }
    
    /* BUTTONS */
    .btn-gold {
        background-color: #C8A165;
        border-color: #C8A165;
        color: #FFFFFF;
    }
    .btn-gold:hover {
        background-color: #b08d55;
        border-color: #b08d55;
        color: #FFFFFF;
    }
    
    .btn-outline-charcoal {
        color: #333333;
        border-color: #333333;
    }
    .btn-outline-charcoal:hover {
        background-color: #333333;
        color: #FFFFFF;
    }

    /* CLICKABLE CARD */
    .card-link {
        text-decoration: none;
        color: inherit;
        cursor: pointer;
    }
    .card-link:focus-visible {
        outline: 2px solid #C8A165;
        outline-offset: 2px;
        border-radius: 0.375rem;
    }
    .hover-scale {
        transition: transform 0.2s ease-in-out;
    }
    .hover-scale:hover {
        transform: scale(1.03);
    }

    /* TABLE */
    .table-hover tbody tr:hover {
        background-color: #f9f8f6; /* Soft Neutral */
    }
    
    .badge-gold { background-color: #C8A165; color: white; }

    /* ─── EVENT CALENDAR ─── */
    .cal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 6px;
    }
    .cal-weekday {
        text-align: center;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #8a8a8a;
        font-weight: 600;
        padding: 4px 0;
    }
    .cal-cell {
        min-height: 92px;
        border: 1px solid #eeeeee;
        border-radius: 10px;
        background: #ffffff;
        padding: 6px;
        display: flex;
        flex-direction: column;
        position: relative;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .cal-cell:hover {
        border-color: #C8A165;
        box-shadow: 0 4px 14px rgba(200, 161, 101, .20);
        z-index: 2;
    }
    .cal-cell.out-month { background: #fafafa; opacity: .55; }
    .cal-cell.today {
        border-color: #C8A165;
        box-shadow: inset 0 0 0 2px rgba(200, 161, 101, .45);
    }
    .cal-day-num {
        align-self: flex-start;
        font-size: .8rem;
        font-weight: 700;
        color: #333333;
        background: #f5f3ef;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .cal-cell.today .cal-day-num { background: #C8A165; color: #ffffff; }
    .cal-count-badge {
        position: absolute;
        top: 6px;
        right: 6px;
        font-size: .62rem;
        font-weight: 700;
        background: #333333;
        color: #ffffff;
        border-radius: 999px;
        padding: 1px 6px;
    }
    .cal-density-1 { background: rgba(200, 161, 101, .10); }
    .cal-density-2 { background: rgba(200, 161, 101, .22); }
    .cal-density-3 { background: rgba(200, 161, 101, .40); }
    .cal-density-4 { background: rgba(200, 161, 101, .58); }
    .cal-event-list {
        margin-top: auto;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .cal-chip {
        font-size: .68rem;
        padding: 2px 6px;
        border-radius: 6px;
        background: #ffffff;
        border: 1px solid #e3dccf;
        color: #6b5b3e;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        text-decoration: none;
        display: block;
    }
    .cal-chip:hover { background: #C8A165; color: #ffffff; border-color: #C8A165; }
    .cal-chip-more { background: #f5f3ef; border-style: dashed; cursor: default; }
    .cal-legend { width: 16px; height: 12px; border-radius: 3px; border: 1px solid #dcdcdc; display: inline-block; }
    .cal-lg-1 { background: rgba(200, 161, 101, .10); }
    .cal-lg-2 { background: rgba(200, 161, 101, .22); }
    .cal-lg-3 { background: rgba(200, 161, 101, .40); }
    .cal-lg-4 { background: rgba(200, 161, 101, .58); }
</style>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        }
        const table = $('#ordersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('banquet.orders.datatable') }}",
            columns: [
                { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false },
                { 
                    data: 'order_id', 
                    name: 'order_id',
                    render: data => `<span class="fw-bold text-gold">#${data}</span>` 
                },
                { 
                    data: 'customer',
                    name: 'customer',
                    orderable: false,
                    searchable: false,
                    render: (data, type, row) => {
                        let org = row.organization !== 'Private' ? `<br><small class="text-muted"><i class="fas fa-building me-1"></i>${row.organization}</small>` : '';
                        return `<span class="fw-bold text-charcoal">${data.name}</span>${org}`;
                    }
                },
                { data: 'event_dates', name: 'event_dates', orderable: false, searchable: false },
                { data: 'total_guests', name: 'total_guests', className: 'text-center', orderable: false, searchable: false },
                { data: 'total_revenue', name: 'total_revenue', className: 'text-end fw-bold text-charcoal', searchable: false },
                { data: 'profit_margin', name: 'profit_margin', className: 'text-center', searchable: false },
                { 
                    data: 'status', 
                    name: 'status',
                    className: 'text-center',
                    render: function(data) {
                        const colors = {'Pending': 'warning', 'Confirmed': 'primary', 'Completed': 'success', 'Cancelled': 'danger'};
                        return `<span class="badge bg-${colors[data] || 'secondary'} rounded-pill px-3">${data}</span>`;
                    }
                },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ],
            dom: 'tp',
            language: { emptyTable: "No orders found" },
            initComplete: function() {
                $('#customSearch').on('keyup', function() { table.search(this.value).draw(); });
                $('#statusFilter').on('change', function() { table.column(7).search(this.value).draw(); });
                this.api().on('draw', function() {
                    $('#resultCount').text(`Showing ${table.page.info().recordsDisplay} records`);
                });
            }
        });
        
        // Delete functionality
        $(document).on('click', '.delete-order', function() {
            const orderId = $(this).data('order-id');
            const deleteUrl = $(this).data('url');

            if (confirm(`Are you sure you want to delete order ${orderId}? This action cannot be undone.`)) {
                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    dataType: 'json', // <--- IMPORTANT: Forces Laravel to see this as a JSON request
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            table.ajax.reload();
                            alert(response.message || 'Order deleted successfully.');
                        } else {
                            alert('Failed to delete: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        console.error('Delete Error:', xhr.responseText);
                        let msg = 'Failed to delete order.';
                        
                        // Parse error message from Laravel response
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg += '\nReason: ' + xhr.responseJSON.message;
                        } else if (xhr.status === 403) {
                            msg += '\nReason: Unauthorized action.';
                        } else if (xhr.status === 404) {
                            msg += '\nReason: Order not found (it may have already been deleted).';
                        }
                        
                        alert(msg);
                    }
                });
            }
        });
    });
</script>
@endsection