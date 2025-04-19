@extends('website::layouts.admin')

@section('title', 'View Booking')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-0">Booking Details</h1>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Booking ID</dt>
                <dd class="col-sm-9">{{ $booking->id }}</dd>
                <dt class="col-sm-3">Reference Number</dt>
                <dd class="col-sm-9">{{ $booking->booking_ref_number }}</dd>
                <dt class="col-sm-3">Room</dt>
                <dd class="col-sm-9">{{ $booking->room->name }}</dd>
                <dt class="col-sm-3">Guest</dt>
                <dd class="col-sm-9">{{ $booking->user ? $booking->user->name : $booking->guest_name }}</dd>
                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9">{{ $booking->guest_email ?? 'N/A' }}</dd>
                <dt class="col-sm-3">Phone</dt>
                <dd class="col-sm-9">{{ $booking->guest_phone ?? 'N/A' }}</dd>
                <dt class="col-sm-3">Check-In Date</dt>
                <dd class="col-sm-9">{{ \Carbon\Carbon::parse($booking->check_in)->format('Y-m-d') }}</dd>
                <dt class="col-sm-3">Check-Out Date</dt>
                <dd class="col-sm-9">{{ \Carbon\Carbon::parse($booking->check_out)->format('Y-m-d') }}</dd>
                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">{{ ucfirst($booking->status) }}</dd>
            </dl>
            <div class="mt-4">
                <a href="{{ route('website.admin.bookings.edit', $booking) }}" class="btn btn-warning me-2">Edit</a>
                <a href="{{ route('website.admin.bookings.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection