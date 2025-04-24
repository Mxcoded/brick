@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Booking Confirmation</h1>
        <div class="alert alert-success">
            Thank you for your booking, {{ $booking->guest_name }}!
        </div>

        <h2>Booking Details</h2>
        <table class="table table-bordered">
            <tr>
                <th>Booking Reference</th>
                <td>{{ $booking->booking_ref_number }}</td>
            </tr>
            <tr>
                <th>Room ID</th>
                <td>{{ $booking->room_id }}</td>
            </tr>
            <tr>
                <th>Check-in</th>
                <td>{{ $booking->check_in }}</td>
            </tr>
            <tr>
                <th>Check-out</th>
                <td>{{ $booking->check_out }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $booking->guest_email }}</td>
            </tr>
            <tr>
                <th>Phone</th>
                <td>{{ $booking->guest_phone }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ $booking->status ?? 'Pending' }}</td>
            </tr>
        </table>

        @if(Auth::check())
            <p>You can manage this booking in your <a href="{{ route('website.guest.dashboard') }}">Guest Dashboard</a>.</p>
        @else
            <div class="mt-4">
                <h3>Manage Your Booking</h3>
                <p>Create an account or log in to easily manage your bookings.</p>
                <div class="d-flex gap-3">
                    <a href="{{ route('register') . '?email=' . urlencode($booking->guest_email) }}" class="btn btn-primary">Register Now</a>
                    <a href="{{ route('login') . '?email=' . urlencode($booking->guest_email) }}" class="btn btn-secondary">Log In</a>
                </div>
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('website.home') }}" class="btn btn-outline-secondary">Return to Homepage</a>
        </div>
    </div>
@endsection