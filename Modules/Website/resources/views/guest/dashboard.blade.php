@extends('website::layouts.guest')

@section('title', 'My Dashboard')

@section('content')
    <div class="container py-5">
        <div class="row g-4">

            @include('website::guest.partials.sidebar', ['active' => 'dashboard'])

            {{-- RIGHT CONTENT --}}
            <div class="col-lg-9">

                {{-- Welcome Message --}}
                <div class="mb-4">
                    <h4 class="fw-bold text-dark">Welcome back, {{ explode(' ', Auth::user()->name)[0] }}! 👋</h4>
                    <p class="text-muted">Here is an overview of your account activity.</p>
                </div>

                {{-- Stats Cards --}}
                <div class="row g-3 mb-4">
                    {{-- Active Bookings --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-primary">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase text-muted small fw-bold mb-1">Active Bookings</p>
                                    <h2 class="fw-bold text-dark mb-0">{{ $activeBookingsCount }}</h2>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                                    <i class="fas fa-suitcase-rolling fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pending Payments --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-warning">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase text-muted small fw-bold mb-1">Pending Payment</p>
                                    <h2 class="fw-bold text-dark mb-0">{{ $pendingPaymentCount }}</h2>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                                    <i class="fas fa-clock fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Total Spent --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-success">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase text-muted small fw-bold mb-1">Total Spent</p>
                                    <h3 class="fw-bold text-dark mb-0">₦{{ number_format($totalSpent) }}</h3>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                                    <i class="fas fa-coins fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recent Activity --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div
                        class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-primary"></i>Recent Activity
                        </h5>
                        <a href="{{ route('guest.bookings') }}"
                            class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-uppercase small text-muted">Room</th>
                                        <th class="py-3 text-uppercase small text-muted">Dates</th>
                                        <th class="py-3 text-uppercase small text-muted">Status</th>
                                        <th class="pe-4 py-3 text-end text-uppercase small text-muted">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentBookings as $booking)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded p-2 me-3 text-primary">
                                                        <i class="fas fa-bed"></i>
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="d-block fw-bold text-dark">{{ optional($booking->roomType)->name ?? optional($booking->room)->name ?? 'Room' }}</span>
                                                        <span class="small text-muted">Ref:
                                                            {{ $booking->booking_reference }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div class="fw-bold"><i
                                                            class="fas fa-arrow-right text-success me-1"></i>
                                                        {{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d, Y') }}
                                                    </div>
                                                    <div class="text-muted"><i
                                                            class="fas fa-arrow-left text-danger me-1"></i>
                                                        {{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($booking->status === 'confirmed')
                                                    <span
                                                        class="badge bg-success-subtle text-success border border-success px-2 py-1 rounded-pill">Confirmed</span>
                                                @elseif($booking->status === 'pending')
                                                    <span
                                                        class="badge bg-warning-subtle text-warning border border-warning px-2 py-1 rounded-pill">Pending</span>
                                                @elseif($booking->status === 'cancelled')
                                                    <span
                                                        class="badge bg-danger-subtle text-danger border border-danger px-2 py-1 rounded-pill">Cancelled</span>
                                                @else
                                                    <span
                                                        class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">{{ ucfirst($booking->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="{{ route('guest.bookings') }}"
                                                    class="btn btn-sm btn-light rounded-circle shadow-sm"
                                                    title="View Details">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="fas fa-box-open fa-2x mb-2 opacity-50"></i>
                                                <p class="mb-0">No recent activity found.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
