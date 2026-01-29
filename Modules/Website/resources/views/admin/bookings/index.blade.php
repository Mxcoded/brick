@extends('layouts.master')

@section('title', 'Manage Bookings')

@section('page-content')
<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Web Bookings</h1>
        <a href="{{ route('website.admin.bookings.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> New Reservation
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('website.admin.bookings.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="small text-muted text-uppercase fw-bold">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Ref, Guest Name, or Email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="small text-muted text-uppercase fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Request</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small text-muted text-uppercase fw-bold">Check-in Date</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
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
                    <tbody class="border-top-0">
                        @forelse($bookings as $booking)
                        <tr>
                            <td class="ps-4 fw-bold text-primary">{{ $booking->booking_reference }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $booking->guest_name }}</div>
                                <div class="small text-muted">{{ $booking->guest_phone }}</div>
                            </td>
                            <td>{{ $booking->room->name ?? 'Deleted Room' }}</td>
                            <td>
                                <div class="small">
                                    <span class="text-success"><i class="fas fa-sign-in-alt me-1"></i> {{ $booking->check_in_date->format('M d') }}</span>
                                    <br>
                                    <span class="text-danger"><i class="fas fa-sign-out-alt me-1"></i> {{ $booking->check_out_date->format('M d') }}</span>
                                </div>
                            </td>
                            <td class="fw-bold">₦{{ number_format($booking->total_amount, 2) }}</td>
                            <td>
                                @if($booking->status === 'confirmed')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3">Confirmed</span>
                                @elseif($booking->status === 'pending')
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3">Pending</span>
                                @elseif($booking->status === 'cancelled')
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3">Cancelled</span>
                                @else
                                    <span class="badge bg-secondary px-3">{{ ucfirst($booking->status) }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('website.admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-primary">
                                    Manage
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-calendar-times fa-3x mb-3 text-light"></i>
                                <p>No bookings found matching your criteria.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($bookings->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection