@extends('website::layouts.guest')

@section('content')
    <div class="container">
        <h1>Welcome, {{ Auth::user()->name }}!</h1>
        <p>This is your guest dashboard. Here you can manage your bookings and profile.</p>

        <h2>Your Bookings</h2>
        @if($bookings->isEmpty())
            <p>You have no bookings yet.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Room ID</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td>{{ $booking->room_id }}</td>
                            <td>{{ $booking->check_in }}</td>
                            <td>{{ $booking->check_out }}</td>
                            <td>{{ $booking->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
<div class="mt-4">
    <h3>Claim a Booking</h3>
    <form method="POST" action="{{ route('website.guest.claim-booking') }}">
        @csrf
        <div class="mb-3">
            <label for="booking_id" class="form-label">Booking ID</label>
            <input type="text" name="booking_id" id="booking_id" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="guest_email" class="form-label">Booking Email</label>
            <input type="email" name="guest_email" id="guest_email" class="form-control" value="{{ Auth::user()->email }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Claim Booking</button>
    </form>
    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif
</div>
        <a href="{{ route('website.guest.profile') }}" class="btn btn-primary">Manage Profile</a>
    </div>
@endsection