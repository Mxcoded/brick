@extends('layouts.master')

@section('title', 'Manage Bookings')

@section('styles')
<style>
    .booking-index-header h1 {
        font-weight: 700;
        color: var(--bp-charcoal, #333);
    }

    .booking-index-header h1 i {
        color: var(--bp-gold, #C8A165);
    }

    .filter-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }

    .filter-card .card-body {
        background: var(--bp-neutral, #F5F3EF);
        border-radius: 12px;
    }

    .filter-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #888;
        margin-bottom: 0.3rem;
    }

    .filter-card .form-control,
    .filter-card .form-select {
        border: 1px solid #e0ddd8;
        border-radius: 8px;
        font-size: 0.85rem;
    }

    .filter-card .form-control:focus,
    .filter-card .form-select:focus {
        border-color: var(--bp-gold, #C8A165);
        box-shadow: 0 0 0 3px rgba(200,161,101,0.15);
    }

    .table-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .table-card .table thead th {
        background: var(--bp-neutral, #F5F3EF);
        color: #888;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--bp-neutral-dark, #E8E4DC);
    }

    .table-card .table tbody tr {
        transition: background 0.15s ease;
    }

    .table-card .table tbody tr:hover {
        background: rgba(200,161,101,0.04);
    }

    .table-card .table tbody td {
        vertical-align: middle;
        border-color: #f0eee9;
        font-size: 0.875rem;
    }

    .booking-ref {
        font-weight: 700;
        color: var(--bp-charcoal, #333);
        font-size: 0.85rem;
        letter-spacing: 0.3px;
    }

    .guest-name {
        font-weight: 600;
        color: var(--bp-charcoal, #333);
    }

    .guest-phone {
        font-size: 0.78rem;
        color: #999;
    }

    .room-name {
        color: #666;
        font-size: 0.85rem;
    }

    .date-cell {
        font-size: 0.82rem;
    }

    .date-checkin {
        color: #16a34a;
    }

    .date-checkout {
        color: #dc2626;
    }

    .amount-cell {
        font-weight: 700;
        color: var(--bp-charcoal, #333);
        font-size: 0.9rem;
    }

    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.85rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        border: 1px solid transparent;
    }

    .badge-status i {
        font-size: 0.6rem;
    }

    .badge-confirmed {
        background: rgba(74, 222, 128, 0.12);
        color: #16a34a;
        border-color: rgba(74, 222, 128, 0.3);
    }

    .badge-pending {
        background: rgba(200, 161, 101, 0.12);
        color: #b8915a;
        border-color: rgba(200, 161, 101, 0.3);
    }

    .badge-cancelled {
        background: rgba(248, 113, 113, 0.12);
        color: #dc2626;
        border-color: rgba(248, 113, 113, 0.3);
    }

    .badge-checked_in {
        background: rgba(59, 130, 246, 0.12);
        color: #2563eb;
        border-color: rgba(59, 130, 246, 0.3);
    }

    .badge-completed {
        background: rgba(99, 102, 241, 0.12);
        color: #4f46e5;
        border-color: rgba(99, 102, 241, 0.3);
    }

    .btn-manage {
        border: 1px solid #e0ddd8;
        color: #666;
        background: transparent;
        border-radius: 8px;
        padding: 0.35rem 1rem;
        font-size: 0.78rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-manage:hover {
        border-color: var(--bp-gold, #C8A165);
        color: var(--bp-gold, #C8A165);
        background: rgba(200,161,101,0.06);
    }

    .empty-state i {
        color: #ddd;
    }

    .empty-state p {
        color: #999;
        font-size: 0.9rem;
    }

    .table-card .card-footer {
        border-top: 1px solid var(--bp-neutral-dark, #E8E4DC);
        background: #fff;
    }

    .filter-btn {
        background: var(--bp-charcoal, #333);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 0.45rem 1rem;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .filter-btn:hover {
        background: #555;
        color: #fff;
    }

    .new-booking-btn {
        background: linear-gradient(135deg, #C8A165, #B8915A);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1.25rem;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .new-booking-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(200,161,101,0.35);
        color: #fff;
    }
</style>
@endsection

@section('page-content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 booking-index-header">
        <h1 class="h3 mb-0"><i class="fas fa-calendar-check me-2"></i>Web Bookings</h1>
        <a href="{{ route('website.admin.bookings.create') }}" class="new-booking-btn">
            <i class="fas fa-plus me-1"></i> New Reservation
        </a>
    </div>

    {{-- Filter Card --}}
    <div class="card filter-card mb-4">
        <div class="card-body p-3">
            <form action="{{ route('website.admin.bookings.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <div class="filter-label">Search</div>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search" style="color: #bbb;"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Ref, Guest Name, or Email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="filter-label">Status</div>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="filter-label">Check-in Date</div>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="filter-btn w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">Reference</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Dates</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                    <tr>
                        <td class="ps-4">
                            <div class="booking-ref">{{ $booking->booking_reference }}</div>
                            @if($booking->booking_group_id)
                                <div class="small" style="color: #999;">
                                    <i class="fas fa-layer-group me-1"></i>{{ $booking->booking_group_id }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="guest-name">{{ $booking->guest_name }}</div>
                            <div class="guest-phone">{{ $booking->guest_phone }}</div>
                        </td>
                        <td>
                            <span class="room-name">{{ optional($booking->roomType)->name ?? optional($booking->room)->name ?? 'Room' }}</span>
                        </td>
                        <td>
                            <div class="date-cell">
                                <span class="date-checkin"><i class="fas fa-sign-in-alt me-1"></i>{{ $booking->check_in_date->format('M d') }}</span>
                                <br>
                                <span class="date-checkout"><i class="fas fa-sign-out-alt me-1"></i>{{ $booking->check_out_date->format('M d') }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="amount-cell">₦{{ number_format($booking->total_amount, 2) }}</span>
                        </td>
                        <td>
                            <span class="badge-status badge-{{ $booking->status }}">
                                <i class="fas fa-circle"></i>
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('website.admin.bookings.show', $booking->id) }}" class="btn-manage">
                                <i class="fas fa-arrow-right me-1"></i> Manage
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 empty-state">
                            <i class="fas fa-calendar-times fa-3x mb-3 d-block"></i>
                            <p class="mb-0">No bookings found matching your criteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
        <div class="card-footer py-3">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection