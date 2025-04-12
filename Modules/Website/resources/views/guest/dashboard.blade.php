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

        <a href="{{ route('website.guest.profile') }}" class="btn btn-primary">Manage Profile</a>
    </div>
@endsection