@extends('website::layouts.guest')

@section('content')
    <h1>My Bookings</h1>
    @if($bookings->isEmpty())
        <div class="alert alert-info">You have no bookings yet.</div>
    @else
        <table class="table table-striped">
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
                        <td>{{ $booking->status ?? 'Pending' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection