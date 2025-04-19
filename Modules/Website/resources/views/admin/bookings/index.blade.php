@extends('website::layouts.admin')

@section('title', 'Bookings')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-0">Bookings</h1>
            <a href="{{ route('website.admin.bookings.create') }}" class="btn btn-primary float-end">Create Booking</a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($bookings->isEmpty())
                <p class="text-muted">No bookings found.</p>
            @else
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ref Number</th>
                            <th>Room</th>
                            <th>Guest</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $booking)
                            <tr>
                                <td>{{ $booking->id }}</td>
                                <td>{{ $booking->booking_ref_number }}</td>
                                <td>{{ $booking->room->name }}</td>
                                <td>{{ $booking->user ? $booking->user->name : $booking->guest_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->check_in)->format('Y-m-d') }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->check_out)->format('Y-m-d') }}</td>
                                <td>{{ ucfirst($booking->status) }}</td>
                                <td>
                                    <a href="{{ route('website.admin.bookings.show', $booking) }}" class="btn btn-info btn-sm">View</a>
                                    <a href="{{ route('website.admin.bookings.edit', $booking) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('website.admin.bookings.destroy', $booking) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this booking?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection