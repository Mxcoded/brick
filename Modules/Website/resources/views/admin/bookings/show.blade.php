@extends('website::layouts.admin')

@section('title', 'Booking Details')

@section('content')
<section class="booking-section py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 overflow-hidden">
                    <div class="card-header bg-primary text-white py-4">
                        <h1 class="h3 mb-0 text-center">Booking #{{ $booking->booking_ref_number }}</h1>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <div class="row g-4">
                            <!-- Booking Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Booking Information</h5>
                                <div class="mb-2">
                                    <strong>Reference Number:</strong> {{ $booking->booking_ref_number }}
                                </div>
                                <div class="mb-2">
                                    <strong>Room:</strong> {{ $booking->room ? $booking->room->name : 'N/A' }}
                                </div>
                                <div class="mb-2">
                                    <strong>Check-In:</strong> {{ \Carbon\Carbon::parse($booking->check_in)->format('Y-m-d') }}
                                    @if ($booking->check_in_time)
                                        at {{ $booking->check_in_time }}
                                    @endif
                                </div>
                                <div class="mb-2">
                                    <strong>Check-Out:</strong> {{ \Carbon\Carbon::parse($booking->check_out)->format('Y-m-d') }}
                                    @if ($booking->check_out_time)
                                        at {{ $booking->check_out_time }}
                                    @endif
                                </div>
                                <div class="mb-2">
                                    <strong>Guests:</strong> {{ $booking->number_of_guests }} Adult(s), {{ $booking->number_of_children }} Child(ren)
                                </div>
                                <div class="mb-2">
                                    <strong>Special Requests:</strong> {{ $booking->special_requests ?? 'None' }}
                                </div>
                                <div class="mb-2">
                                    <strong>Status:</strong>
                                    <span class="badge bg-{{ $booking->status == 'confirmed' ? 'success' : ($booking->status == 'pending' ? 'warning' : ($booking->status == 'cancelled' ? 'danger' : 'info')) }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </div>
                                @if ($booking->status == 'cancelled')
                                    <div class="mb-2">
                                        <strong>Cancelled At:</strong> {{ \Carbon\Carbon::parse($booking->cancelled_at)->format('Y-m-d H:i:s') }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Cancellation Reason:</strong> {{ $booking->cancellation_reason ?? 'N/A' }}
                                    </div>
                                @endif
                            </div>
                            <!-- Guest Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Guest Information</h5>
                                <div class="mb-2">
                                    <strong>Name:</strong> {{ $booking->guest_name ?? ($booking->user ? $booking->user->name : 'N/A') }}
                                </div>
                                <div class="mb-2">
                                    <strong>Email:</strong> {{ $booking->guest_email ?? ($booking->user ? $booking->user->email : 'N/A') }}
                                </div>
                                <div class="mb-2">
                                    <strong>Phone:</strong> {{ $booking->guest_phone ?? 'N/A' }}
                                </div>
                                <div class="mb-2">
                                    <strong>Company:</strong> {{ $booking->guest_company ?? 'N/A' }}
                                </div>
                                <div class="mb-2">
                                    <strong>Address:</strong> {{ $booking->guest_address ?? 'N/A' }}
                                </div>
                                <div class="mb-2">
                                    <strong>Nationality:</strong> {{ $booking->guest_nationality ?? 'N/A' }}
                                </div>
                                <div class="mb-2">
                                    <strong>ID Type:</strong> {{ $booking->guest_id_type ?? 'N/A' }}
                                </div>
                                <div class="mb-2">
                                    <strong>ID Number:</strong> {{ $booking->guest_id_number ?? 'N/A' }}
                                </div>
                            </div>
                            <!-- Financial Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Financial Information</h5>
                                <div class="mb-2">
                                    <strong>Total Price:</strong><span class="fa-naira">₦{{ number_format($booking->total_amount, 2) }}</span>
                                </div>
                                <div class="mb-2">
                                    <strong>Deposit Amount:</strong> <span class="fa-naira">₦{{ number_format($booking->amount_paid ?? 0, 2) }}</span>
                                </div>
                                <div class="mb-2">
                                    <strong>Payment Status:</strong> {{ ucfirst($booking->payment_status) }}
                                </div>
                                <div class="mb-2">
                                    <strong>Payment Method:</strong> {{ $booking->payment_method ?? 'N/A' }}
                                </div>
                            </div>
                            <!-- Operational Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Operational Information</h5>
                                <div class="mb-2">
                                    <strong>Source:</strong> {{ $booking->source ?? 'Hotel Website' }}
                                </div>
                                <div class="mb-2">
                                    <strong>Room Status:</strong> {{ $booking->room->status ?? 'N/A' }}
                                </div>
                                <div class="mb-2">
                                    <strong>Assigned Staff:</strong> {{ $booking->assignedStaff ? $booking->assignedStaff->name : 'N/A' }}
                                </div>
                                <div class="mb-2">
                                    <strong>Created By:</strong> {{ $booking->creator ? $booking->creator->name : 'N/A' }}
                                </div>
                                <div class="mb-2">
                                    <strong>Updated By:</strong> {{ $booking->updater ? $booking->updater->name : 'N/A' }}
                                </div>
                                <div class="mb-2">
                                    <strong>Created At:</strong> {{ \Carbon\Carbon::parse($booking->created_at)->format('Y-m-d H:i:s') }}
                                </div>
                                <div class="mb-2">
                                    <strong>Updated At:</strong> {{ \Carbon\Carbon::parse($booking->updated_at)->format('Y-m-d H:i:s') }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('website.admin.bookings.edit', $booking) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i> Edit Booking
                            </a>
                            @if ($booking->status != 'cancelled')
                                <form action="{{ route('website.admin.bookings.destroy', $booking) }}" method="POST" style="display:inline;" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger delete-btn">
                                        <i class="fas fa-trash me-2"></i> Cancel Booking
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('website.admin.bookings.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    .booking-section {
        background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), 
                    url('{{ asset('images/booking-bg.jpg') }}') no-repeat center center;
        background-size: cover;
    }
    .card {
        border-radius: 10px;
        overflow: hidden;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Confirm deletion
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to cancel this booking?')) {
                    this.closest('form').submit();
                }
            });
        });
    });
</script>
@endpush
@endsection