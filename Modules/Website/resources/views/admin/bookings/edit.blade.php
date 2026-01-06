@extends('layouts.master')

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
@endsection