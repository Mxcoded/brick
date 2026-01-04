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

                        <form id="bookingForm" action="{{ route('website.booking.store') }}" method="POST">
                            @csrf
                            
                            <div class="mb-4 position-relative">
                                <label for="room_id" class="form-label fw-bold">Select Room</label>
                                <select name="room_id" id="room_id" class="form-select live-check" required>
                                    <option value="">-- Choose Your Accommodation --</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" 
                                            {{ (isset($selectedRoom) && $selectedRoom->id == $room->id) || request('room_id') == $room->id || old('room_id') == $room->id ? 'selected' : '' }}>
                                            {{ $room->name }} - ₦{{ number_format($room->price, 2) }}/night
                                        </option>
                                    @endforeach
                                </select>
                                <div id="availability-feedback" class="mt-2 text-danger small fw-bold d-none"></div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="check_in_date" class="form-label fw-bold">Check-in Date</label>
                                    <input type="date" class="form-control live-check" id="check_in_date" name="check_in_date" 
                                           value="{{ request('check_in_date') ?? old('check_in_date') }}" 
                                           min="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="check_out_date" class="form-label fw-bold">Check-out Date</label>
                                    <input type="date" class="form-control live-check" id="check_out_date" name="check_out_date" 
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
                                <button type="submit" id="submitBtn" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm">
                                    <span class="btn-text">Confirm Reservation</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                                <a href="{{ route('website.home') }}" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.live-check');
    const feedback = document.getElementById('availability-feedback');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const spinner = submitBtn.querySelector('.spinner-border');

    // Debounce timer to prevent spamming server
    let timeout = null;

    inputs.forEach(input => {
        input.addEventListener('change', () => {
            clearTimeout(timeout);
            timeout = setTimeout(checkAvailability, 500);
        });
    });

    function checkAvailability() {
        const roomId = document.getElementById('room_id').value;
        const checkIn = document.getElementById('check_in_date').value;
        const checkOut = document.getElementById('check_out_date').value;

        // Reset UI
        feedback.classList.add('d-none');
        feedback.className = 'mt-2 small fw-bold d-none'; // Reset colors
        submitBtn.disabled = false;

        // Only check if all 3 fields are filled
        if (!roomId || !checkIn || !checkOut) return;

        // Show loading state
        btnText.textContent = 'Checking availability...';
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');

        fetch("{{ route('website.room.checkAvailability') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                room_id: roomId,
                check_in_date: checkIn,
                check_out_date: checkOut
            })
        })
        .then(response => response.json())
        .then(data => {
            feedback.classList.remove('d-none');
            
            if (data.available) {
                // SUCCESS
                feedback.classList.add('text-success');
                feedback.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                submitBtn.disabled = false;
            } else {
                // FAIL
                feedback.classList.add('text-danger');
                feedback.innerHTML = '<i class="fas fa-times-circle"></i> ' + data.message;
                submitBtn.disabled = true; // Keep disabled so they can't submit
            }
        })
        .catch(error => {
            console.error('Error:', error);
        })
        .finally(() => {
            btnText.textContent = 'Confirm Reservation';
            spinner.classList.add('d-none');
        });
    }
    
    // Run check immediately if pre-filled (e.g. redirected from home)
    if(document.getElementById('room_id').value && document.getElementById('check_in_date').value) {
        checkAvailability();
    }
});
</script>
@endpush