@extends('website::layouts.guest')

@section('title', 'My Bookings')

@section('content')
    <div class="container py-5">
        <div class="row g-4">

            @include('website::guest.partials.sidebar', ['active' => 'bookings'])

            {{-- RIGHT CONTENT --}}
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-4">
                    <div
                        class="card-header bg-white py-4 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-list-alt me-2"></i>Booking History</h5>
                        <a href="{{ route('website.rooms.index') }}"
                            class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                            <i class="fas fa-plus me-1"></i> New Booking
                        </a>
                    </div>

                    <div class="card-body p-0">

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show m-4" role="alert">
                                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($bookings->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 py-3 text-uppercase small text-muted">Room Details</th>
                                            <th class="py-3 text-uppercase small text-muted">Date & Duration</th>
                                            <th class="py-3 text-uppercase small text-muted">Payment</th>
                                            <th class="py-3 text-uppercase small text-muted">Status</th>
                                            <th class="pe-4 py-3 text-end text-uppercase small text-muted">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($bookings as $booking)
                                            <tr>
                                                {{-- Room Type Info --}}
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        @if (optional($booking->roomType)->image_url || optional($booking->room)->image_url)
                                                            <img src="{{ optional($booking->roomType)->image_url ?? optional($booking->room)->image_url }}" class="rounded me-3"
                                                                style="width: 50px; height: 50px; object-fit: cover;">
                                                        @else
                                                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center text-muted"
                                                                style="width: 50px; height: 50px;">
                                                                <i class="fas fa-bed"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <span
                                                                class="d-block fw-bold text-dark">{{ optional($booking->roomType)->name ?? optional($booking->room)->name ?? 'Room' }}</span>
                                                            <span class="small text-muted">Ref:
                                                                #{{ $booking->booking_reference }}</span>
                                                        </div>
                                                    </div>
                                                </td>

                                                {{-- Dates --}}
                                                <td>
                                                    <div class="d-flex flex-column small">
                                                        <span><i class="fas fa-calendar-alt text-muted me-1"></i>
                                                            {{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d') }}
                                                            -
                                                            {{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') }}</span>
                                                        @php
                                                            $nights = \Carbon\Carbon::parse(
                                                                $booking->check_in_date,
                                                            )->diffInDays(
                                                                \Carbon\Carbon::parse($booking->check_out_date),
                                                            );
                                                        @endphp
                                                        <span class="text-muted">{{ $nights }}
                                                            Night{{ $nights > 1 ? 's' : '' }}</span>
                                                    </div>
                                                </td>

                                                {{-- Amount --}}
                                                <td>
                                                    <div class="fw-bold text-dark">
                                                        ₦{{ number_format($booking->total_amount) }}</div>
                                                    <span
                                                        class="badge {{ $booking->payment_status == 'paid' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-pill px-2"
                                                        style="font-size: 0.7rem;">
                                                        {{ ucfirst($booking->payment_status) }}
                                                    </span>
                                                </td>

                                                {{-- Status --}}
                                                <td>
                                                    @if ($booking->status === 'confirmed')
                                                        <span
                                                            class="badge bg-success-subtle text-success border border-success px-2 py-1 rounded-pill">Confirmed</span>
                                                    @elseif($booking->status === 'pending')
                                                        <span
                                                            class="badge bg-warning-subtle text-warning border border-warning px-2 py-1 rounded-pill">Pending</span>
                                                    @elseif($booking->status === 'checked_in')
                                                        <span
                                                            class="badge bg-primary-subtle text-primary border border-primary px-2 py-1 rounded-pill">Checked
                                                            In</span>
                                                    @elseif($booking->status === 'cancelled')
                                                        <span
                                                            class="badge bg-danger-subtle text-danger border border-danger px-2 py-1 rounded-pill">Cancelled</span>
                                                    @endif
                                                </td>

                                                {{-- Actions --}}
                                                <td class="text-end pe-4">
                                                    @if ($booking->status === 'pending' || $booking->status === 'confirmed')
                                                        <form action="{{ route('guest.bookings.cancel', $booking->id) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm">
                                                                <i class="fas fa-times me-1"></i> Cancel
                                                            </button>
                                                        </form>
                                                        <a href="{{ route('frontdesk.registrations.create', ['ref' => $booking->booking_reference]) }}"
                                                            class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm">
                                                            <i class="fas fa-check me-1"></i> Online Checkin
                                                        </a>
                                                    @elseif($booking->status === 'cancelled')
                                                        <span class="text-muted small fst-italic">No actions</span>
                                                    @else
                                                        <a href="{{ route('website.contact') }}"
                                                            class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                            Support
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            <div class="p-4 border-top">
                                {{ $bookings->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="mb-3 text-muted opacity-25">
                                    <i class="fas fa-calendar-times fa-4x"></i>
                                </div>
                                <h5 class="fw-bold text-dark">No bookings found</h5>
                                <p class="text-muted mb-4">You haven't made any reservations yet.</p>
                                <a href="{{ route('website.rooms.index') }}"
                                    class="btn btn-primary rounded-pill px-4 shadow-sm">
                                    Find a Room
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
