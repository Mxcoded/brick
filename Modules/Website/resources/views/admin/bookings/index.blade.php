@extends('website::layouts.admin')

@section('title', 'Bookings Management')

@section('content')
<section class="bookings-section py-5">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white py-4 d-flex justify-content-between align-items-center">
                        <h1 class="h3 mb-0">Bookings</h1>
                        <a href="{{ route('website.admin.bookings.create') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-2"></i> Create Booking
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <!-- Filters (from March 22, 2025) -->
                        <form class="mb-4" method="GET" action="{{ route('website.admin.bookings.index') }}">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" class="form-control flatpickr" name="check_in" placeholder="Check-In Date" value="{{ request('check_in') }}">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control flatpickr" name="check_out" placeholder="Check-Out Date" value="{{ request('check_out') }}">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                            </div>
                        </form>

                        <!-- Bookings Table -->
                        @if ($bookings->isEmpty())
                            <div class="alert alert-info">No bookings found.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Booking Ref</th>
                                            <th>Guest Name</th>
                                            <th>Room Type</th>
                                            <th>Check-In</th>
                                            <th>Check-Out</th>
                                            <th>Nights</th>
                                            <th>Total Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($bookings as $booking)
                                            <tr>
                                                <td>{{ $booking->booking_reference }}</td>
                                                <td>{{ $booking->guest_name }}</td>
                                                <td>{{ $booking->room->name }}</td>
                                                <td>{{ \Carbon\Carbon::parse($booking->check_in_date)->format('Y-m-d') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($booking->check_out_date)->format('Y-m-d') }}</td>
                                                <td>{{ max(1, \Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date))) }}</td>
                                                <td>₦{{ number_format($booking->total_amount, 2) }}</td>
                                                <td>
                                                    <span class="badge {{ $booking->status == 'pending' ? 'bg-warning' : ($booking->status == 'confirmed' ? 'bg-success' : 'bg-danger') }}">
                                                        {{ ucfirst($booking->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('website.admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('website.admin.bookings.edit', $booking->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if ($booking->status != 'cancelled')
                                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal{{ $booking->id }}">
                                                            <i class="fas fa-ban me-2"></i> Cancel
                                                        </button>
                                                        <!-- Cancel Modal -->
                                                        <div class="modal fade" id="cancelModal{{ $booking->id }}" tabindex="-1" aria-labelledby="cancelModalLabel{{ $booking->id }}" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="cancelModalLabel{{ $booking->id }}">Cancel Booking</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <form action="{{ route('website.admin.bookings.cancel', $booking->id) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <div class="modal-body">
                                                                            <p>Are you sure you want to cancel booking <strong>{{ $booking->booking_ref_number }}</strong> for <strong>{{ $booking->guest_name }}</strong>?</p>
                                                                            <div class="mb-3">
                                                                                <label for="cancellation_reason{{ $booking->id }}" class="form-label">Cancellation Reason (optional)</label>
                                                                                <textarea class="form-control" id="cancellation_reason{{ $booking->id }}" name="cancellation_reason" rows="4" placeholder="e.g., Guest request"></textarea>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                            <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <!-- Delete Button (Optional, for Admins) -->
                                                    @if (auth()->user()->hasRole('super-admin'))
                                                        <form action="{{ route('website.admin.bookings.destroy', $booking->id) }}" method="POST" style="display:inline;" class="delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to permanently delete this booking?')">
                                                                <i class="fas fa-trash me-2"></i> Delete
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{ $bookings->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .bookings-section {
        background: #f8f9fa;
    }
    .card {
        border-radius: 10px;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.9em;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr('.flatpickr', {
            dateFormat: 'Y-m-d',
            minDate: '2025-01-01'
        });
    });
</script>
@endpush
@endsection