@extends('layouts.master')

@section('title', 'New Reservation')

@section('page-content')
<div class="row justify-content-center">
    <div class="col-lg-8 grid-margin">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h4 class="card-title mb-0">Create Manual Reservation</h4>
            </div>
            <div class="card-body">
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('website.admin.bookings.store') }}" method="POST">
                    @csrf
                    
                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Room & Dates</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label>Select Room <span class="text-danger">*</span></label>
                            <select name="room_id" class="form-select" required>
                                <option value="">-- Select Room --</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                        {{ $room->name }} (₦{{ number_format($room->price) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Check-in Date <span class="text-danger">*</span></label>
                            <input type="date" name="check_in_date" class="form-control" value="{{ old('check_in_date') }}" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label>Check-out Date <span class="text-danger">*</span></label>
                            <input type="date" name="check_out_date" class="form-control" value="{{ old('check_out_date') }}" required min="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Guest Details</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label>Guest Name <span class="text-danger">*</span></label>
                            <input type="text" name="guest_name" class="form-control" value="{{ old('guest_name') }}" placeholder="Full Name" required>
                        </div>
                        <div class="col-md-6">
                            <label>Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="guest_phone" class="form-control" value="{{ old('guest_phone') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label>Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="guest_email" class="form-control" value="{{ old('guest_email') }}" required>
                        </div>
                    </div>

                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Occupancy & Payment</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label>Adults</label>
                            <input type="number" name="adults" class="form-control" value="1" min="1">
                        </div>
                        <div class="col-md-4">
                            <label>Children</label>
                            <input type="number" name="children" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label>Payment Status</label>
                            <select name="payment_status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label>Special Requests</label>
                        <textarea name="special_requests" class="form-control" rows="3">{{ old('special_requests') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('website.admin.bookings.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection