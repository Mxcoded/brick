@extends('website::layouts.master')

@section('title', 'Book Your Stay')

@section('content')
    <div class="container">
        <h1 class="mb-4">Book Your Stay</h1>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('website.booking.submit') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="room_id" class="form-label">Room</label>
                        <select class="form-control @error('room_id') is-invalid @enderror" id="room_id" name="room_id"
                            required>
                            <option value="">Select Room</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                            @endforeach
                        </select>
                        @error('room_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="guest_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control @error('guest_name') is-invalid @enderror" id="guest_name"
                            name="guest_name" value="{{ old('guest_name') }}" required>
                        @error('guest_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="guest_email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('guest_email') is-invalid @enderror"
                            id="guest_email" name="guest_email" value="{{ old('guest_email') }}" required>
                        @error('guest_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="guest_phone" class="form-label">Phone</label>
                        <input type="text" class="form-control @error('guest_phone') is-invalid @enderror"
                            id="guest_phone" name="guest_phone" value="{{ old('guest_phone') }}" required>
                        @error('guest_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="guest_company" class="form-label">Company (Optional)</label>
                        <input type="text" class="form-control @error('guest_company') is-invalid @enderror"
                            id="guest_company" name="guest_company" value="{{ old('guest_company') }}">
                        @error('guest_company')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="guest_nationality" class="form-label">Nationality (Optional)</label>
                        <input type="text" class="form-control @error('guest_nationality') is-invalid @enderror"
                            id="guest_nationality" name="guest_nationality" value="{{ old('guest_nationality') }}">
                        @error('guest_nationality')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="check_in" class="form-label">Check-In Date</label>
                        <input type="date" class="form-control @error('check_in') is-invalid @enderror" id="check_in"
                            name="check_in" value="{{ old('check_in') }}" required>
                        @error('check_in')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="check_out" class="form-label">Check-Out Date</label>
                        <input type="date" class="form-control @error('check_out') is-invalid @enderror" id="check_out"
                            name="check_out" value="{{ old('check_out') }}" required>
                        @error('check_out')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="number_of_guests" class="form-label">Number of Guests</label>
                        <input type="number" class="form-control @error('number_of_guests') is-invalid @enderror"
                            id="number_of_guests" name="number_of_guests" value="{{ old('number_of_guests', 1) }}"
                            min="1" required>
                        @error('number_of_guests')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="number_of_children" class="form-label">Number of Children</label>
                        <input type="number" class="form-control @error('number_of_children') is-invalid @enderror"
                            id="number_of_children" name="number_of_children" value="{{ old('number_of_children', 0) }}"
                            min="0" required>
                        @error('number_of_children')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="special_requests" class="form-label">Special Requests</label>
                        <textarea class="form-control @error('special_requests') is-invalid @enderror" id="special_requests"
                            name="special_requests">{{ old('special_requests') }}</textarea>
                        @error('special_requests')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method (Optional)</label>
                        <select class="form-control @error('payment_method') is-invalid @enderror" id="payment_method"
                            name="payment_method">
                            <option value="">Select Payment Method</option>
                            <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>
                                Credit Card</option>
                            <option value="bank_transfer"
                                {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Submit Booking</button>
        </form>
    </div>

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

            .form-control:focus,
            .form-select:focus {
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
