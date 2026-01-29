{{-- @extends('layouts.master')

@section('title', 'Edit Booking')

@section('page-content')
<div class="row justify-content-center">
    <div class="col-lg-8 grid-margin">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h4 class="card-title mb-0">Edit Booking: {{ $booking->booking_reference }}</h4>
            </div>
            <div class="card-body">
                
                <form action="{{ route('website.admin.bookings.update', $booking->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label>Room Type</label>
                            <select name="room_id" class="form-select">
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" {{ $booking->room_id == $room->id ? 'selected' : '' }}>
                                        {{ $room->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Check-in Date</label>
                            <input type="date" name="check_in_date" class="form-control" 
                                   value="{{ $booking->check_in_date->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label>Check-out Date</label>
                            <input type="date" name="check_out_date" class="form-control" 
                                   value="{{ $booking->check_out_date->format('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label>Reservation Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="checked_in" {{ $booking->status == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                                <option value="checked_out" {{ $booking->status == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Payment Status</label>
                            <select name="payment_status" class="form-select">
                                <option value="pending" {{ $booking->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $booking->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="refunded" {{ $booking->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('website.admin.bookings.show', $booking->id) }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection --}}
@extends('layouts.master')

@section('title', 'Edit Booking Details')

@section('page-content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Edit Booking: <span class="text-primary">{{ $booking->booking_reference }}</span></h5>
            </div>
            <div class="card-body">
                {{-- ✅ NICE UI: SUCCESS MESSAGE --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-2 fs-4 text-success"></i>
                                <div>
                                    <strong>Success!</strong> {{ session('success') }}
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- ⚠️ NICE UI: ERROR MESSAGES --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-exclamation-circle me-2 fs-4 text-danger"></i>
                                <strong>Please fix the following errors:</strong>
                            </div>
                            <ul class="mb-0 mt-2 ps-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif


                <form action="{{ route('website.admin.bookings.update', $booking->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        {{-- 1. Room Selection --}}
                        <div class="col-12">
                            <label class="form-label fw-bold">Room</label>
                            <select name="room_id" class="form-select" required>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" {{ $booking->room_id == $room->id ? 'selected' : '' }}>
                                        {{ $room->name }} - ₦{{ number_format($room->price, 2) }}/night
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Changing the room will check availability and update the total price.</small>
                        </div>

                        {{-- 2. Dates --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Check-in Date</label>
                            <input type="date" name="check_in_date" class="form-control" 
                                   value="{{ $booking->check_in_date->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Check-out Date</label>
                            <input type="date" name="check_out_date" class="form-control" 
                                   value="{{ $booking->check_out_date->format('Y-m-d') }}" required>
                        </div>

                        {{-- 3. Guest Info --}}
                        <div class="col-12"><hr></div>
                        <div class="col-md-6">
                            <label class="form-label">Guest Name</label>
                            <input type="text" class="form-control" value="{{ $booking->guest_name }}" disabled>
                            <small class="text-muted">Guest name cannot be changed here.</small>
                        </div>
                        
                        {{-- 4. Status Overrides --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Status</label>
                            <select name="payment_status" class="form-select">
                                <option value="pending" {{ $booking->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $booking->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="failed" {{ $booking->payment_status == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Booking Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="checked_in" {{ $booking->status == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                                <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Admin Notes</label>
                            <textarea name="admin_notes" class="form-control" rows="3">{{ $booking->admin_notes }}</textarea>
                        </div>

                        <div class="col-12 mt-4 d-flex justify-content-between">
                            <a href="{{ route('website.admin.bookings.show', $booking->id) }}" class="btn btn-light border"><span class="fas fa-arrow-left me-1"></span>Back to list</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Booking
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection