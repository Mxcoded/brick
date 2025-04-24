@extends('website::layouts.admin')

@section('title', 'Edit Booking')

@section('content')
<section class="booking-section py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 overflow-hidden">
                    <div class="card-header bg-primary text-white py-4">
                        <h1 class="h3 mb-0 text-center">Edit Booking #{{ $booking->booking_ref_number }}</h1>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('website.admin.bookings.update', $booking) }}" method="POST" id="bookingForm">
                            @csrf
                            @method('PUT')
                            <div class="row g-4">
                                <!-- Booking Information -->
                                <div class="col-md-6">
                                    <h5 class="mb-3">Booking Information</h5>
                                    <div class="form-floating mb-3">
                                        <select class="form-select" name="room_id" id="room_id" required>
                                            <option value="" disabled>Select a room</option>
                                            @foreach ($rooms as $room)
                                                <option value="{{ $room->id }}" data-price="{{ $room->price_per_night }}"
                                                        {{ $booking->room_id == $room->id ? 'selected' : '' }}>
                                                    {{ $room->name }} - ₦{{ number_format($room->price_per_night, 2) }}/night
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="room_id">Room</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control flatpickr" name="check_in" id="check_in" 
                                               value="{{ $booking->check_in }}" required>
                                        <label for="check_in">Check-In Date</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control flatpickr" name="check_out" id="check_out" 
                                               value="{{ $booking->check_out }}" required>
                                        <label for="check_out">Check-Out Date</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="number" class="form-control" name="number_of_guests" id="number_of_guests" 
                                               min="1" max="10" value="{{ $booking->number_of_guests }}" required>
                                        <label for="number_of_guests">Number of Guests</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="number" class="form-control" name="number_of_children" id="number_of_children" 
                                               min="0" max="10" value="{{ $booking->number_of_children }}" required>
                                        <label for="number_of_children">Number of Children</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <textarea class="form-control" name="special_requests" id="special_requests" 
                                                  style="height: 100px">{{ $booking->special_requests }}</textarea>
                                        <label for="special_requests">Special Requests</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <select class="form-select" name="status" id="status" required>
                                            <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                            <option value="checked_in" {{ $booking->status == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                                            <option value="checked_out" {{ $booking->status == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                                            <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            <option value="no_show" {{ $booking->status == 'no_show' ? 'selected' : '' }}>No Show</option>
                                        </select>
                                        <label for="status">Status</label>
                                    </div>
                                </div>
                                <!-- Guest Information -->
                                <div class="col-md-6">
                                    <h5 class="mb-3">Guest Information</h5>
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="guest_name" id="guest_name" 
                                               value="{{ $booking->guest_name }}" pattern="[A-Za-z ]+" required>
                                        <label for="guest_name">Full Name</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" name="guest_email" id="guest_email" 
                                               value="{{ $booking->guest_email }}" required>
                                        <label for="guest_email">Email Address</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="tel" class="form-control" name="guest_phone" id="guest_phone" 
                                               value="{{ $booking->guest_phone }}" pattern="[0-9]{10,15}" required>
                                        <label for="guest_phone">Phone Number</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="guest_company" id="guest_company" 
                                               value="{{ $booking->guest_company }}">
                                        <label for="guest_company">Company (Optional)</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="guest_address" id="guest_address" 
                                               value="{{ $booking->guest_address }}">
                                        <label for="guest_address">Address (Optional)</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="guest_nationality" id="guest_nationality" 
                                               value="{{ $booking->guest_nationality }}">
                                        <label for="guest_nationality">Nationality (Optional)</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <select class="form-select" name="guest_id_type" id="guest_id_type">
                                            <option value="">Select ID Type</option>
                                            <option value="passport" {{ $booking->guest_id_type == 'passport' ? 'selected' : '' }}>Passport</option>
                                            <option value="driver_license" {{ $booking->guest_id_type == 'driver_license' ? 'selected' : '' }}>Driver's License</option>
                                            <option value="national_id" {{ $booking->guest_id_type == 'national_id' ? 'selected' : '' }}>National ID</option>
                                        </select>
                                        <label for="guest_id_type">ID Type (Optional)</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="guest_id_number" id="guest_id_number" 
                                               value="{{ $booking->guest_id_number }}">
                                        <label for="guest_id_number">ID Number (Optional)</label>
                                    </div>
                                </div>
                                <!-- Financial Information -->
                                <div class="col-md-6">
                                    <h5 class="mb-3">Financial Information</h5>
                                    <div class="form-floating mb-3">
                                        <input type="number" class="form-control" name="total_price" id="total_price" 
                                               step="0.01" min="0" value="{{ $booking->total_price }}" required>
                                        <label for="total_price">Total Price (₦)</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="number" class="form-control" name="deposit_amount" id="deposit_amount" 
                                               step="0.01" min="0" value="{{ $booking->deposit_amount }}">
                                        <label for="deposit_amount">Deposit Amount (₦, Optional)</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <select class="form-select" name="payment_status" id="payment_status" required>
                                            <option value="pending" {{ $booking->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="partial" {{ $booking->payment_status == 'partial' ? 'selected' : '' }}>Partial</option>
                                            <option value="paid" {{ $booking->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                            <option value="refunded" {{ $booking->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                                        </select>
                                        <label for="payment_status">Payment Status</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <select class="form-select" name="payment_method" id="payment_method">
                                            <option value="">Select Payment Method</option>
                                            <option value="credit_card" {{ $booking->payment_method == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                            <option value="bank_transfer" {{ $booking->payment_method == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                            <option value="cash" {{ $booking->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                                        </select>
                                        <label for="payment_method">Payment Method (Optional)</label>
                                    </div>
                                    <!-- Booking Summary -->
                                    <div class="booking-summary p-4 bg-light rounded mt-4">
                                        <h5 class="mb-3">Booking Summary</h5>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Room Rate:</span>
                                            <span id="roomRate">₦0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Nights:</span>
                                            <span id="nightsCount">0</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between fw-bold">
                                            <span>Total Estimate:</span>
                                            <span id="totalEstimate">₦0.00</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Operational Information -->
                                <div class="col-md-6">
                                    <h5 class="mb-3">Operational Information</h5>
                                    <div class="form-floating mb-3">
                                        <select class="form-select" name="source" id="source">
                                            <option value="">Select Source</option>
                                            <option value="website" {{ $booking->source == 'website' ? 'selected' : '' }}>Website</option>
                                            <option value="phone" {{ $booking->source == 'phone' ? 'selected' : '' }}>Phone</option>
                                            <option value="OTA" {{ $booking->source == 'OTA' ? 'selected' : '' }}>OTA</option>
                                            <option value="walk_in" {{ $booking->source == 'walk_in' ? 'selected' : '' }}>Walk-In</option>
                                        </select>
                                        <label for="source">Source (Optional)</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="time" class="form-control" name="check_in_time" id="check_in_time" 
                                               value="{{ $booking->check_in_time }}">
                                        <label for="check_in_time">Check-In Time (Optional)</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="time" class="form-control" name="check_out_time" id="check_out_time" 
                                               value="{{ $booking->check_out_time }}">
                                        <label for="check_out_time">Check-Out Time (Optional)</label>
                                    </div>
                                    @if ($booking->status == 'cancelled')
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" name="cancellation_reason" id="cancellation_reason" 
                                                   value="{{ $booking->cancellation_reason }}" required>
                                            <label for="cancellation_reason">Cancellation Reason</label>
                                        </div>
                                    @endif
                                </div>
                                <!-- Submit Buttons -->
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-save me-2"></i> Update Booking
                                    </button>
                                    <a href="{{ route('website.admin.bookings.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i> Back to List
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .booking-summary {
        border: 1px solid #dee2e6;
    }
    .flatpickr-input {
        background: transparent;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roomSelect = document.getElementById('room_id');
        const checkIn = document.getElementById('check_in');
        const checkOut = document.getElementById('check_out');
        const totalPriceInput = document.getElementById('total_price');
        const roomRate = document.getElementById('roomRate');
        const nightsCount = document.getElementById('nightsCount');
        const totalEstimate = document.getElementById('totalEstimate');

        // Initialize Flatpickr
        const checkInPicker = flatpickr('#check_in', {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            defaultDate: '{{ $booking->check_in }}',
            onChange: function(selectedDates, dateStr) {
                const nextDay = new Date(selectedDates[0]);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOutPicker.set('minDate', nextDay);
                if (checkOut.value && new Date(checkOut.value) <= nextDay) {
                    checkOutPicker.setDate(nextDay);
                }
                calculateTotal();
            }
        });

        const checkOutPicker = flatpickr('#check_out', {
            dateFormat: 'Y-m-d',
            minDate: new Date().setDate(new Date('{{ $booking->check_in }}').getDate() + 1),
            defaultDate: '{{ $booking->check_out }}',
            onChange: function(selectedDates, dateStr) {
                const checkInDate = new Date(checkIn.value);
                if (selectedDates[0] <= checkInDate) {
                    const nextDay = new Date(checkInDate);
                    nextDay.setDate(nextDay.getDate() + 1);
                    checkOutPicker.setDate(nextDay);
                }
                calculateTotal();
            }
        });

        checkIn.addEventListener('change', calculateTotal);
        checkOut.addEventListener('change', calculateTotal);
        roomSelect.addEventListener('change', calculateTotal);

        function calculateTotal() {
            if (roomSelect.value && checkIn.value && checkOut.value) {
                const price = Math.abs(parseFloat(roomSelect.options[roomSelect.selectedIndex].dataset.price));
                const checkInDate = new Date(checkIn.value);
                const checkOutDate = new Date(checkOut.value);

                if (checkOutDate <= checkInDate) {
                    console.warn('Invalid dates: check-out must be after check-in', {
                        checkIn: checkIn.value,
                        checkOut: checkOut.value
                    });
                    const nextDay = new Date(checkIn.value);
                    nextDay.setDate(nextDay.getDate() + 1);
                    checkOut.value = nextDay.toISOString().split('T')[0];
                    checkOutDate.setTime(nextDay.getTime());
                }

                const timeDiff = checkOutDate - checkInDate;
                const nights = Math.max(1, Math.ceil(timeDiff / (1000 * 60 * 60 * 24)));

                roomRate.textContent = `₦${price.toLocaleString('en-NG', { minimumFractionDigits: 2 })}`;
                nightsCount.textContent = nights;
                const total = Math.max(0, (price * nights).toFixed(2));
                totalEstimate.textContent = `₦${parseFloat(total).toLocaleString('en-NG', { minimumFractionDigits: 2 })}`;
                totalPriceInput.value = total;
            }
        }

        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const checkInDate = new Date(checkIn.value);
            const checkOutDate = new Date(checkOut.value);
            if (checkOutDate <= checkInDate) {
                e.preventDefault();
                alert('Check-out date must be after check-in date.');
                const nextDay = new Date(checkIn.value);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOut.value = nextDay.toISOString().split('T')[0];
                calculateTotal();
            }
        });

        calculateTotal();
    });
</script>
@endpush
@endsection