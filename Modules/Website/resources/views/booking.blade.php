@extends('website::layouts.master')

@section('title', 'Book Your Stay')

@section('content')
<section class="booking-section py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 overflow-hidden">
                    <div class="card-header btn-primary text-white py-4">
                        <h1 class="h3 mb-0 text-center fw-bold">Reserve Your Luxury Stay</h1>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        @if ($errors->any())
                            <div class="alert alert-danger mb-4">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('website.booking.store') }}" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="room_id" class="form-label fw-bold">Select Room</label>
                                <select name="room_id" id="room_id" class="form-select" required>
                                    <option value="">-- Choose Your Accommodation --</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" 
                                            {{-- Pre-selection Logic: Controller Variable OR Request Param OR Validation Old Input --}}
                                            {{ (isset($selectedRoom) && $selectedRoom->id == $room->id) || request('room_id') == $room->id || old('room_id') == $room->id ? 'selected' : '' }}>
                                            {{ $room->name }} - ₦{{ number_format($room->price, 2) }}/night
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">All rooms include complimentary breakfast, Wi-Fi, and gym access.</div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="check_in_date" class="form-label fw-bold">Check-in Date</label>
                                    <input type="date" class="form-control" id="check_in_date" name="check_in_date" 
                                           value="{{ request('check_in_date') ?? old('check_in_date') }}" 
                                           min="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="check_out_date" class="form-label fw-bold">Check-out Date</label>
                                    <input type="date" class="form-control" id="check_out_date" name="check_out_date" 
                                           value="{{ request('check_out_date') ?? old('check_out_date') }}" 
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="adults" class="form-label fw-bold">Adults</label>
                                    <select name="adults" id="adults" class="form-select">
                                        @for($i=1; $i<=6; $i++)
                                            <option value="{{ $i }}" {{ (request('adults') == $i || old('adults') == $i) ? 'selected' : '' }}>
                                                {{ $i }} Adult{{ $i > 1 ? 's' : '' }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="children" class="form-label fw-bold">Children</label>
                                    <select name="children" id="children" class="form-select">
                                        <option value="0">0 Children</option>
                                        @for($i=1; $i<=4; $i++)
                                            <option value="{{ $i }}" {{ old('children') == $i ? 'selected' : '' }}>
                                                {{ $i }} Child{{ $i > 1 ? 'ren' : '' }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4 text-muted">
                            <h5 class="mb-3 fw-bold">Guest Details</h5>

                            <div class="mb-3">
                                <label for="guest_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="guest_name" name="guest_name" 
                                       value="{{ old('guest_name') }}" placeholder="John Doe" required>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="guest_email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="guest_email" name="guest_email" 
                                           value="{{ old('guest_email') }}" placeholder="john@example.com" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="guest_phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="guest_phone" name="guest_phone" 
                                           value="{{ old('guest_phone') }}" placeholder="+234..." required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="special_requests" class="form-label">Special Requests (Optional)</label>
                                <textarea class="form-control" id="special_requests" name="special_requests" rows="3" 
                                          placeholder="Late check-in, dietary restrictions, airport pickup...">{{ old('special_requests') }}</textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm">Confirm Reservation</button>
                                <a href="{{ route('website.home') }}" class="btn btn-outline-secondary">Cancel</a>
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

            .form-control:focus,
            .form-select:focus {
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
                        totalEstimate.textContent =
                            `₦${parseFloat(total).toLocaleString('en-NG', { minimumFractionDigits: 2 })}`;
                    }
                }

                document.getElementById('bookingForm').addEventListener('submit', function(e) {
                    try {
                        const checkInDate = new Date(checkIn.value);
                        const checkOutDate = new Date(checkOut.value);
                        if (!checkIn.value || !checkOut.value || isNaN(checkInDate) || isNaN(checkOutDate)) {
                            e.preventDefault();
                            alert('Please select valid check-in and check-out dates.');
                            console.error('Invalid date values', {
                                checkIn: checkIn.value,
                                checkOut: checkOut.value
                            });
                            return;
                        }
                        if (checkOutDate <= checkInDate) {
                            e.preventDefault();
                            alert('Check-out date must be after check-in date.');
                            const nextDay = new Date(checkIn.value);
                            nextDay.setDate(nextDay.getDate() + 1);
                            checkOut.value = nextDay.toISOString().split('T')[0];
                            calculateTotal();
                            console.warn('Adjusted check-out date', {
                                newCheckOut: checkOut.value
                            });
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
