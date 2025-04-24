@extends('website::layouts.master')

@section('title', 'Book Your Stay')

@section('content')
<section class="booking-section py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 overflow-hidden">
                    <div class="card-header bg-primary text-white py-4">
                        <h1 class="h3 mb-0 text-center">Reserve Your Luxury Stay</h1>
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
                        <form action="{{ route('website.booking.submit') }}" method="POST" id="bookingForm">
                            @csrf
                            <div class="row g-4">
                                <!-- Room Selection -->
                                <div class="col-md-12">
                                    <div class="form-floating">
                                        <select class="form-select" name="room_id" id="room_id" required>
                                            <option value="" selected disabled>Select a room</option>
                                            @foreach ($rooms as $room)
                                                <option value="{{ $room->id }}" data-price="{{ $room->price_per_night }}">
                                                    {{ $room->name }} - ₦{{ number_format($room->price_per_night, 2) }}/night
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="room_id">Room Type</label>
                                    </div>
                                </div>
                                <!-- Date Selection -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control flatpickr" name="check_in" id="check_in" 
                                               placeholder="Check-In Date" required>
                                        <label for="check_in">Check-In Date</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control flatpickr" name="check_out" id="check_out" 
                                               placeholder="Check-Out Date" required>
                                        <label for="check_out">Check-Out Date</label>
                                    </div>
                                </div>
                                <!-- Guest Information -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="guest_name" id="guest_name" 
                                               pattern="[A-Za-z ]+" required>
                                        <label for="guest_name">Full Name</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" name="guest_email" id="guest_email" required>
                                        <label for="guest_email">Email Address</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" name="guest_phone" id="guest_phone" 
                                               pattern="[0-9]{10,15}" required>
                                        <label for="guest_phone">Phone Number</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" name="guests" id="guests" 
                                               min="1" max="10" value="1" required>
                                        <label for="guests">Number of Guests</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" name="number_of_children" id="number_of_children" 
                                               min="0" max="10" value="0" required>
                                        <label for="number_of_children">Number of Children</label>
                                    </div>
                                </div>
                                <!-- Special Requests -->
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" name="special_requests" id="special_requests" 
                                                  style="height: 100px"></textarea>
                                        <label for="special_requests">Special Requests (Optional)</label>
                                    </div>
                                </div>
                                <!-- Booking Summary -->
                                <div class="col-12 mt-4">
                                    <div class="booking-summary p-4 bg-light rounded">
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
                                <!-- Submit Button -->
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3">
                                        <i class="fas fa-calendar-check me-2"></i> Confirm Reservation
                                    </button>
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
        const roomRate = document.getElementById('roomRate');
        const nightsCount = document.getElementById('nightsCount');
        const totalEstimate = document.getElementById('totalEstimate');

        // Initialize Flatpickr
        const checkInPicker = flatpickr('#check_in', {
            dateFormat: 'Y-m-d',
            minDate: 'today',
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
            minDate: new Date().setDate(new Date().getDate() + 1),
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
            }
        }

        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            try {
                const checkInDate = new Date(checkIn.value);
                const checkOutDate = new Date(checkOut.value);
                if (!checkIn.value || !checkOut.value || isNaN(checkInDate) || isNaN(checkOutDate)) {
                    e.preventDefault();
                    alert('Please select valid check-in and check-out dates.');
                    console.error('Invalid date values', { checkIn: checkIn.value, checkOut: checkOut.value });
                    return;
                }
                if (checkOutDate <= checkInDate) {
                    e.preventDefault();
                    alert('Check-out date must be after check-in date.');
                    const nextDay = new Date(checkIn.value);
                    nextDay.setDate(nextDay.getDate() + 1);
                    checkOut.value = nextDay.toISOString().split('T')[0];
                    calculateTotal();
                    console.warn('Adjusted check-out date', { newCheckOut: checkOut.value });
                    return;
                }
                console.log('Submitting form:', {
                    room_id: roomSelect.value,
                    check_in: checkIn.value,
                    check_out: checkOut.value,
                    guest_name: document.getElementById('guest_name').value,
                    guest_email: document.getElementById('guest_email').value,
                    guest_phone: document.getElementById('guest_phone').value,
                    guests: document.getElementById('guests').value,
                    number_of_children: document.getElementById('number_of_children').value
                });
            } catch (error) {
                e.preventDefault();
                console.error('Form submission error:', error);
                alert('An error occurred while submitting the form. Please try again.');
            }
        });

        calculateTotal();
    });
</script>
@endpush
@endsection