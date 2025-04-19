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
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('website.admin.bookings.update', $booking) }}" method="POST" id="bookingForm">
                            @csrf
                            @method('PUT')
                            <div class="row g-4">
                                <!-- Room Selection -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select @error('room_id') is-invalid @enderror" id="room_id" name="room_id" required>
                                            <option value="" disabled>Select a room</option>
                                            @foreach ($rooms as $room)
                                                <option value="{{ $room->id }}" data-price="{{ $room->price_per_night }}" {{ old('room_id', $booking->room_id) == $room->id ? 'selected' : '' }}>
                                                    {{ $room->name }} - ${{ number_format($room->price_per_night) }}/night
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="room_id">Room Type</label>
                                        @error('room_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <!-- User ID -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id" value="{{ old('user_id', $booking->user_id) }}" placeholder="Enter User ID if applicable">
                                        <label for="user_id">Registered User (Optional)</label>
                                        @error('user_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Guest Information -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control @error('guest_name') is-invalid @enderror" id="guest_name" name="guest_name" value="{{ old('guest_name', $booking->guest_name) }}" pattern="[A-Za-z ]+" required>
                                        <label for="guest_name">Guest Name</label>
                                        @error('guest_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control @error('guest_email') is-invalid @enderror" id="guest_email" name="guest_email" value="{{ old('guest_email', $booking->guest_email) }}" required>
                                        <label for="guest_email">Guest Email</label>
                                        @error('guest_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control @error('guest_phone') is-invalid @enderror" id="guest_phone" name="guest_phone" value="{{ old('guest_phone', $booking->guest_phone) }}" pattern="[0-9]{10,15}" required>
                                        <label for="guest_phone">Guest Phone</label>
                                        @error('guest_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control @error('guest_company') is-invalid @enderror" id="guest_company" name="guest_company" value="{{ old('guest_company', $booking->guest_company) }}">
                                        <label for="guest_company">Company (Optional)</label>
                                        @error('guest_company')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <textarea class="form-control @error('guest_address') is-invalid @enderror" id="guest_address" name="guest_address" style="height: 100px">{{ old('guest_address', $booking->guest_address) }}</textarea>
                                        <label for="guest_address">Address (Optional)</label>
                                        @error('guest_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control @error('guest_nationality') is-invalid @enderror" id="guest_nationality" name="guest_nationality" value="{{ old('guest_nationality', $booking->guest_nationality) }}">
                                        <label for="guest_nationality">Nationality (Optional)</label>
                                        @error('guest_nationality')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select @error('guest_id_type') is-invalid @enderror" id="guest_id_type" name="guest_id_type">
                                            <option value="">Select ID Type</option>
                                            <option value="passport" {{ old('guest_id_type', $booking->guest_id_type) == 'passport' ? 'selected' : '' }}>Passport</option>
                                            <option value="driver_license" {{ old('guest_id_type', $booking->guest_id_type) == 'driver_license' ? 'selected' : '' }}>Driver’s License</option>
                                            <option value="national_id" {{ old('guest_id_type', $booking->guest_id_type) == 'national_id' ? 'selected' : '' }}>National ID</option>
                                        </select>
                                        <label for="guest_id_type">ID Type (Optional)</label>
                                        @error('guest_id_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control @error('guest_id_number') is-invalid @enderror" id="guest_id_number" name="guest_id_number" value="{{ old('guest_id_number', $booking->guest_id_number) }}">
                                        <label for="guest_id_number">ID Number (Optional)</label>
                                        @error('guest_id_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Date Selection -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="date" class="form-control @error('check_in') is-invalid @enderror" id="check_in" name="check_in" value="{{ old('check_in', \Carbon\Carbon::parse($booking->check_in)->format('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                                        <label for="check_in">Check-In Date</label>
                                        @error('check_in')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="date" class="form-control @error('check_out') is-invalid @enderror" id="check_out" name="check_out" value="{{ old('check_out', \Carbon\Carbon::parse($booking->check_out)->format('Y-m-d')) }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                        <label for="check_out">Check-Out Date</label>
                                        @error('check_out')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Guest Counts -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" class="form-control @error('number_of_guests') is-invalid @enderror" id="number_of_guests" name="number_of_guests" value="{{ old('number_of_guests', $booking->number_of_guests) }}" min="1" max="10" required>
                                        <label for="number_of_guests">Number of Guests</label>
                                        @error('number_of_guests')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" class="form-control @error('number_of_children') is-invalid @enderror" id="number_of_children" name="number_of_children" value="{{ old('number_of_children', $booking->number_of_children) }}" min="0" max="10" required>
                                        <label for="number_of_children">Number of Children</label>
                                        @error('number_of_children')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Special Requests -->
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control @error('special_requests') is-invalid @enderror" id="special_requests" name="special_requests" style="height: 100px">{{ old('special_requests', $booking->special_requests) }}</textarea>
                                        <label for="special_requests">Special Requests (Optional)</label>
                                        @error('special_requests')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Financial Details -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" class="form-control @error('total_price') is-invalid @enderror" id="total_price" name="total_price" value="{{ old('total_price', $booking->total_price) }}" required>
                                        <label for="total_price">Total Price ($)</label>
                                        @error('total_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" class="form-control @error('deposit_amount') is-invalid @enderror" id="deposit_amount" name="deposit_amount" value="{{ old('deposit_amount', $booking->deposit_amount) }}">
                                        <label for="deposit_amount">Deposit Amount (Optional, $)</label>
                                        @error('deposit_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select @error('payment_status') is-invalid @enderror" id="payment_status" name="payment_status" required>
                                            <option value="pending" {{ old('payment_status', $booking->payment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="partial" {{ old('payment_status', $booking->payment_status) == 'partial' ? 'selected' : '' }}>Partial</option>
                                            <option value="paid" {{ old('payment_status', $booking->payment_status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                            <option value="refunded" {{ old('payment_status', $booking->payment_status) == 'refunded' ? 'selected' : '' }}>Refunded</option>
                                        </select>
                                        <label for="payment_status">Payment Status</label>
                                        @error('payment_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method">
                                            <option value="">Select Payment Method</option>
                                            <option value="credit_card" {{ old('payment_method', $booking->payment_method) == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                            <option value="bank_transfer" {{ old('payment_method', $booking->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                            <option value="cash" {{ old('payment_method', $booking->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                        </select>
                                        <label for="payment_method">Payment Method (Optional)</label>
                                        @error('payment_method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Operational Details -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="time" class="form-control @error('check_in_time') is-invalid @enderror" id="check_in_time" name="check_in_time" value="{{ old('check_in_time', $booking->check_in_time) }}">
                                        <label for="check_in_time">Check-In Time (Optional)</label>
                                        @error('check_in_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="time" class="form-control @error('check_out_time') is-invalid @enderror" id="check_out_time" name="check_out_time" value="{{ old('check_out_time', $booking->check_out_time) }}">
                                        <label for="check_out_time">Check-Out Time (Optional)</label>
                                        @error('check_out_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select @error('source') is-invalid @enderror" id="source" name="source">
                                            <option value="">Select Source</option>
                                            <option value="website" {{ old('source', $booking->source) == 'website' ? 'selected' : '' }}>Website</option>
                                            <option value="phone" {{ old('source', $booking->source) == 'phone' ? 'selected' : '' }}>Phone</option>
                                            <option value="OTA" {{ old('source', $booking->source) == 'OTA' ? 'selected' : '' }}>OTA</option>
                                            <option value="walk_in" {{ old('source', $booking->source) == 'walk_in' ? 'selected' : '' }}>Walk-in</option>
                                        </select>
                                        <label for="source">Booking Source (Optional)</label>
                                        @error('source')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="pending" {{ old('status', $booking->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="confirmed" {{ old('status', $booking->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                            <option value="checked_in" {{ old('status', $booking->status) == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                                            <option value="checked_out" {{ old('status', $booking->status) == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                                            <option value="cancelled" {{ old('status', $booking->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            <option value="no_show" {{ old('status', $booking->status) == 'no_show' ? 'selected' : '' }}>No Show</option>
                                        </select>
                                        <label for="status">Status</label>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Booking Summary -->
                                <div class="col-12 mt-4">
                                    <div class="booking-summary p-4 bg-light rounded">
                                        <h5 class="mb-3">Booking Summary</h5>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Room Rate:</span>
                                            <span id="roomRate">${{ number_format($booking->room->price_per_night) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Nights:</span>
                                            <span id="nightsCount">{{ \Carbon\Carbon::parse($booking->check_out)->diffInDays(\Carbon\Carbon::parse($booking->check_in)) }}</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between fw-bold">
                                            <span>Total Estimate:</span>
                                            <span id="totalEstimate">${{ number_format($booking->total_price) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Cancellation Reason (if cancelled) -->
                                @if ($booking->status == 'cancelled')
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control @error('cancellation_reason') is-invalid @enderror" id="cancellation_reason" name="cancellation_reason" style="height: 100px">{{ old('cancellation_reason', $booking->cancellation_reason) }}</textarea>
                                            <label for="cancellation_reason">Cancellation Reason</label>
                                            @error('cancellation_reason')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                @endif
                                <!-- Submit Button -->
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3">
                                        <i class="fas fa-save me-2"></i> Update Booking
                                    </button>
                                    <a href="{{ route('website.admin.bookings.index') }}" class="btn btn-secondary btn-lg w-100 py-3 mt-2">Cancel</a>
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
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roomSelect = document.getElementById('room_id');
        const checkIn = document.getElementById('check_in');
        const checkOut = document.getElementById('check_out');
        const totalPriceInput = document.getElementById('total_price');
        const roomRate = document.getElementById('roomRate');
        const nightsCount = document.getElementById('nightsCount');
        const totalEstimate = document.getElementById('totalEstimate');
        
        // Set minimum checkout date based on checkin date
        checkIn.addEventListener('change', function() {
            if (this.value) {
                const nextDay = new Date(this.value);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOut.min = nextDay.toISOString().split('T')[0];
                
                if (checkOut.value && new Date(checkOut.value) <= nextDay) {
                    checkOut.value = nextDay.toISOString().split('T')[0];
                }
                
                calculateTotal();
            }
        });
        
        // Calculate total when dates or room change
        checkOut.addEventListener('change', calculateTotal);
        roomSelect.addEventListener('change', calculateTotal);
        
        function calculateTotal() {
            if (roomSelect.value && checkIn.value && checkOut.value) {
                const price = roomSelect.options[roomSelect.selectedIndex].dataset.price;
                const checkInDate = new Date(checkIn.value);
                const checkOutDate = new Date(checkOut.value);
                
                // Calculate nights
                const timeDiff = checkOutDate - checkInDate;
                const nights = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
                
                // Update display
                roomRate.textContent = `$${parseFloat(price).toLocaleString()}`;
                nightsCount.textContent = nights;
                const total = (price * nights).toFixed(2);
                totalEstimate.textContent = `$${parseFloat(total).toLocaleString()}`;
                totalPriceInput.value = total;
            }
        }
        
        // Initialize total on page load
        calculateTotal();
        
        // Form validation
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        }, false);
    });
</script>
@endpush
@endsection