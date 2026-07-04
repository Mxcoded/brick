@extends('layouts.master')

@section('title', 'Website Dashboard')

@section('styles')
<style>
:root {
    --luxury-gold: #C8A165;
    --luxury-gold-light: #e8d5b7;
    --luxury-gold-dark: #a8864a;
    --luxury-navy: #1a1a2e;
    --luxury-charcoal: #2c3e50;
    --status-pending-bg: #fef3e2;
    --status-pending-text: #d68910;
    --status-pending-dot: #f39c12;
    --status-confirmed-bg: #e8f5e9;
    --status-confirmed-text: #1b7a34;
    --status-confirmed-dot: #27ae60;
    --status-checkedin-bg: #e3f2fd;
    --status-checkedin-text: #1565c0;
    --status-checkedin-dot: #2980b9;
    --status-completed-bg: #f0f0f0;
    --status-completed-text: #5a5a5a;
    --status-completed-dot: #7f8c8d;
    --status-cancelled-bg: #fde8e8;
    --status-cancelled-text: #c0392b;
    --status-cancelled-dot: #c0392b;
    --status-noshow-bg: #f2d7d5;
    --status-noshow-text: #8e1a1a;
    --status-noshow-dot: #8e1a1a;
}

.stat-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    cursor: default;
}
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.08) !important;
}
.stat-card .stat-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 1.3rem;
    flex-shrink: 0;
}
.stat-card .stat-value {
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: -0.5px;
    line-height: 1.2;
}
.stat-card .stat-label {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: #7f8c8d;
    margin-top: 2px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.3px;
    text-transform: capitalize;
    white-space: nowrap;
}
.status-badge .dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}
.status-badge.pending { background: var(--status-pending-bg); color: var(--status-pending-text); }
.status-badge.pending .dot { background: var(--status-pending-dot); }
.status-badge.confirmed { background: var(--status-confirmed-bg); color: var(--status-confirmed-text); }
.status-badge.confirmed .dot { background: var(--status-confirmed-dot); }
.status-badge.checked_in { background: var(--status-checkedin-bg); color: var(--status-checkedin-text); }
.status-badge.checked_in .dot { background: var(--status-checkedin-dot); }
.status-badge.completed { background: var(--status-completed-bg); color: var(--status-completed-text); }
.status-badge.completed .dot { background: var(--status-completed-dot); }
.status-badge.cancelled { background: var(--status-cancelled-bg); color: var(--status-cancelled-text); }
.status-badge.cancelled .dot { background: var(--status-cancelled-dot); }
.status-badge.no_show { background: var(--status-noshow-bg); color: var(--status-noshow-text); }
.status-badge.no_show .dot { background: var(--status-noshow-dot); }

.occupancy-bar {
    height: 6px;
    border-radius: 3px;
    background: #e9ecef;
    overflow: hidden;
}
.occupancy-bar .fill {
    height: 100%;
    border-radius: 3px;
    background: linear-gradient(90deg, #27ae60, #2ecc71);
    transition: width 0.8s ease;
}

.table-website {
    margin-bottom: 0;
}
.table-website thead th {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: #7f8c8d;
    border-bottom: 2px solid #f0f0f0;
    padding: 12px 16px;
    background: transparent;
}
.table-website tbody td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f5f5f5;
    font-size: 0.85rem;
}
.table-website tbody tr:hover td {
    background: #fafaf8;
}
.table-website .booking-ref {
    font-weight: 700;
    color: var(--luxury-charcoal);
    font-size: 0.78rem;
    letter-spacing: 0.3px;
    font-family: 'SF Mono', 'Consolas', monospace;
}

.section-header {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #2c3e50;
}
.section-header .icon {
    color: var(--luxury-gold);
    margin-right: 8px;
}

.today-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}
.today-item:last-of-type {
    border-bottom: none;
}
.today-item .label {
    font-size: 0.8rem;
    color: #5a5a5a;
    font-weight: 500;
}
.today-item .value {
    font-size: 0.95rem;
    font-weight: 700;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 16px;
    border-radius: 10px;
    border: 1px solid #e8e8e8;
    background: #fff;
    transition: all 0.2s ease;
    text-decoration: none;
    color: #2c3e50;
    font-weight: 500;
    font-size: 0.85rem;
}
.action-btn:hover {
    border-color: var(--luxury-gold);
    background: #fdfaf5;
    color: var(--luxury-charcoal);
    transform: translateX(3px);
}
.action-btn .icon-wrap {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.help-card {
    background: linear-gradient(135deg, #1a1a2e 0%, #2c3e50 100%);
    border-radius: 14px;
    padding: 24px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.help-card::before {
    content: '';
    position: absolute;
    top: -40px;
    right: -40px;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: rgba(200, 161, 101, 0.1);
}
.help-card .gold-text {
    color: var(--luxury-gold);
}

.msg-item {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}
.msg-item:last-child {
    border-bottom: none;
}
.msg-item .msg-name {
    font-weight: 600;
    font-size: 0.83rem;
    color: #2c3e50;
}
.msg-item .msg-preview {
    font-size: 0.76rem;
    color: #7f8c8d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 160px;
}
.msg-item .msg-time {
    font-size: 0.65rem;
    color: #b0b0b0;
    white-space: nowrap;
}
</style>
@endsection

@section('page-content')
    <div class="container-fluid py-4 px-lg-4">

        {{-- Page Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h1 class="h3 fw-bold mb-1" style="color: #1a1a2e;">Website Dashboard</h1>
                <p class="text-secondary small mb-0">
                    <i class="far fa-calendar-alt me-1"></i> {{ now()->format('l, F j, Y') }} &mdash;
                    {{ $stats['total_bookings'] }} bookings &middot;
                    ₦{{ number_format($stats['revenue'] ?? 0) }} revenue &middot;
                    {{ $stats['pending_bookings'] }} pending
                </p>
            </div>
            <a href="{{ route('website.home') }}" target="_blank" class="btn btn-sm px-3 fw-semibold" style="background: #C8A165; color: #fff; border: none; border-radius: 8px;">
                <i class="fas fa-external-link-alt me-1"></i> View Live Site
            </a>
        </div>

        {{-- Row 1: Primary KPI Cards --}}
        <div class="row g-3 mb-4">
            {{-- Revenue --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card" style="border-left: 4px solid #C8A165;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon text-white" style="background: linear-gradient(135deg, #C8A165, #a8864a);">
                            <i class="fas fa-gem"></i>
                        </div>
                        <div>
                            <div class="stat-value" style="color: #C8A165;">₦{{ number_format($stats['revenue'] ?? 0) }}</div>
                            <div class="stat-label">Web Revenue</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Bookings --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card" style="border-left: 4px solid #1a1a2e;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon text-white" style="background: linear-gradient(135deg, #1a1a2e, #2c3e50);">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <div class="stat-value" style="color: #1a1a2e;">{{ $stats['total_bookings'] }}</div>
                            <div class="stat-label">Total Bookings</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pending --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card" style="border-left: 4px solid #e67e22;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon text-white" style="background: linear-gradient(135deg, #e67e22, #d35400);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <div class="stat-value" style="color: #e67e22;">{{ $stats['pending_bookings'] }}</div>
                            <div class="stat-label">Pending</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cancelled --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card" style="border-left: 4px solid #c0392b;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon text-white" style="background: linear-gradient(135deg, #c0392b, #96281b);">
                            <i class="fas fa-ban"></i>
                        </div>
                        <div>
                            <div class="stat-value" style="color: #c0392b;">{{ $stats['cancelled_bookings'] }}</div>
                            <div class="stat-label">Cancelled</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Main Content --}}
        <div class="row g-4">

            {{-- Left Column: Recent Bookings Table --}}
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h6 class="section-header mb-0"><i class="fas fa-calendar-check icon"></i>Recent Web Bookings</h6>
                        <a href="{{ route('website.admin.bookings.index') }}" class="btn btn-sm px-3 fw-semibold" style="color: #C8A165; border: 1px solid #C8A165; border-radius: 8px; font-size: 0.75rem;">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-website">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Guest</th>
                                    <th>Room</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentBookings as $booking)
                                    <tr>
                                        <td><span class="booking-ref">{{ $booking->booking_reference }}</span></td>
                                        <td><span class="fw-semibold" style="color: #2c3e50;">{{ $booking->guest_name }}</span></td>
                                        <td style="color: #5a5a5a;">{{ $booking->roomType->name ?? ($booking->room->name ?? '—') }}</td>
                                        <td style="color: #5a5a5a;">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d, Y') }}</td>
                                        <td style="color: #5a5a5a;">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') }}</td>
                                        <td>
                                            <span class="status-badge {{ $booking->status }}">
                                                <span class="dot"></span>
                                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('website.admin.bookings.show', $booking->id) }}"
                                                class="btn btn-sm px-3 fw-medium"
                                                style="background: #f8f8f8; color: #2c3e50; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 0.75rem;">
                                                Details
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5" style="color: #b0b0b0;">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            No recent bookings found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Right Column: Today + Quick Actions + Messages --}}
            <div class="col-xl-4 d-flex flex-column gap-4">

                {{-- Today at a Glance --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3 border-bottom">
                        <h6 class="section-header mb-0"><i class="fas fa-sun icon"></i>Today at a Glance</h6>
                    </div>
                    <div class="card-body py-3">
                        <div class="today-item">
                            <span class="label"><i class="fas fa-sign-in-alt me-2" style="color: #27ae60;"></i>Arrivals</span>
                            <span class="value" style="color: #27ae60;">{{ $stats['today_arrivals'] }}</span>
                        </div>
                        <div class="today-item">
                            <span class="label"><i class="fas fa-sign-out-alt me-2" style="color: #2980b9;"></i>Departures</span>
                            <span class="value" style="color: #2980b9;">{{ $stats['today_departures'] }}</span>
                        </div>
                        <div class="today-item">
                            <span class="label"><i class="fas fa-bed me-2" style="color: #1a1a2e;"></i>Occupancy</span>
                            <span class="value" style="color: #1a1a2e;">{{ $rooms['occupancy_pct'] }}%</span>
                        </div>
                        <div class="occupancy-bar mt-1 mb-3">
                            <div class="fill" style="width: {{ $rooms['occupancy_pct'] }}%;"></div>
                        </div>
                        <div class="d-flex justify-content-between small" style="color: #7f8c8d;">
                            <span>Occupied: <strong style="color:#2c3e50;">{{ $rooms['occupied'] }}</strong></span>
                            <span>Available: <strong style="color:#27ae60;">{{ $rooms['available'] }}</strong></span>
                            <span>Maint: <strong style="color:#e67e22;">{{ $rooms['maintenance'] }}</strong></span>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3 border-bottom">
                        <h6 class="section-header mb-0"><i class="fas fa-bolt icon"></i>Quick Actions</h6>
                    </div>
                    <div class="card-body py-3 d-grid gap-2">
                        <a href="{{ route('website.admin.room-types.index') }}" class="action-btn">
                            <span class="icon-wrap text-white" style="background: linear-gradient(135deg, #C8A165, #a8864a);">
                                <i class="fas fa-bed"></i>
                            </span>
                            Manage Room Types
                        </a>
                        <a href="{{ route('website.admin.rooms.calendar') }}" class="action-btn">
                            <span class="icon-wrap text-white" style="background: linear-gradient(135deg, #1a1a2e, #2c3e50);">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            Manage Calendar
                        </a>
                        <a href="{{ route('website.admin.settings.index') }}" class="action-btn">
                            <span class="icon-wrap text-white" style="background: linear-gradient(135deg, #7f8c8d, #5a5a5a);">
                                <i class="fas fa-cog"></i>
                            </span>
                            Site Configuration
                        </a>
                        <a href="{{ route('website.admin.bookings.index') }}" class="action-btn">
                            <span class="icon-wrap text-white" style="background: linear-gradient(135deg, #27ae60, #1e8449);">
                                <i class="fas fa-list"></i>
                            </span>
                            All Bookings
                        </a>
                    </div>
                </div>

                {{-- Unread Messages --}}
                @if($recentMessages->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="section-header mb-0"><i class="fas fa-envelope icon"></i>Unread Messages</h6>
                        <span class="badge rounded-pill" style="background: #c0392b; font-size: 0.65rem; font-weight: 600;">{{ $stats['unread_messages'] }}</span>
                    </div>
                    <div class="card-body py-2">
                        @foreach($recentMessages as $msg)
                            <div class="msg-item d-flex align-items-center gap-3">
                                <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle"
                                    style="width: 34px; height: 34px; background: #f5f0e8; color: #C8A165; font-weight: 700; font-size: 0.8rem;">
                                    {{ strtoupper(substr($msg->name ?? '?', 0, 1)) }}
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="msg-name">{{ $msg->name ?? 'Unknown' }}</div>
                                    <div class="msg-preview">{{ mb_substr($msg->message ?? $msg->subject ?? '', 0, 40) }}{{ strlen($msg->message ?? $msg->subject ?? '') > 40 ? '...' : '' }}</div>
                                </div>
                                <div class="msg-time">{{ $msg->created_at->diffForHumans() }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="card-footer bg-transparent border-top-0 py-2 text-center">
                        <a href="{{ route('website.admin.contact-messages.index') }}" class="btn btn-sm w-100 fw-semibold" style="color:#C8A165; font-size:0.75rem;">
                            View All Messages <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                @endif

                {{-- Help Card --}}
                <div class="help-card">
                    <div class="position-relative" style="z-index: 1;">
                        <i class="fas fa-concierge-bell fa-lg gold-text mb-2"></i>
                        <h6 class="fw-bold mb-1">Need Assistance?</h6>
                        <p class="small mb-3 opacity-75" style="font-size: 0.8rem; max-width: 220px;">
                            Manage your website rooms, bookings, and content from this dashboard.
                        </p>
                        <a href="{{ route('website.admin.settings.index') }}" class="btn btn-sm px-3 fw-semibold" style="background: #C8A165; color: #fff; border: none; border-radius: 8px;">
                            <i class="fas fa-cog me-1"></i> Site Settings
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
