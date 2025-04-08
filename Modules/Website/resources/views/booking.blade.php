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
                                                    {{ $room->name }} - ${{ number_format($room->price_per_night) }}/night
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="room_id">Room Type</label>
                                    </div>
                                </div>
                                
                                <!-- Date Selection -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="date" class="form-control" name="check_in" id="check_in" 
                                               min="{{ date('Y-m-d') }}" required>
                                        <label for="check_in">Check-In Date</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="date" class="form-control" name="check_out" id="check_out" 
                                               min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
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
                                            <span id="roomRate">$0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Nights:</span>
                                            <span id="nightsCount">0</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between fw-bold">
                                            <span>Total Estimate:</span>
                                            <span id="totalEstimate">$0</span>
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
        const roomRate = document.getElementById('roomRate');
        const nightsCount = document.getElementById('nightsCount');
        const totalEstimate = document.getElementById('totalEstimate');
        
        // Set minimum checkout date based on checkin date
        checkIn.addEventListener('change', function() {
            if (this.value) {
                const nextDay = new Date(this.value);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOut.min = nextDay.toISOString().split('T')[0];
                
                if (checkOut.value && new Date(checkOut.value) < nextDay) {
                    checkOut.value = '';
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
                totalEstimate.textContent = `$${(price * nights).toLocaleString()}`;
            }
        }
        
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