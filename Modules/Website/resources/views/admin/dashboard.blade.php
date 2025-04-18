@extends('website::layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-0">Website Admin Dashboard</h1>
        </div>
        <div class="card-body">
            <p>Welcome to the website admin dashboard. Use the sidebar to manage rooms, bookings, settings, and more.</p>
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Rooms</h5>
                            <p class="card-text display-4">{{ $stats['total_rooms'] }}</p>
                            <a href="{{ route('website.admin.rooms.index') }}" class="btn btn-sm btn-primary">Manage Rooms</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5 class="card-title">Active Bookings</h5>
                            <p class="card-text display-4">{{ $stats['active_bookings'] }}</p>
                            {{-- <a href="{{ route('website.admin.bookings.index') }}" class="btn btn-sm btn-primary">Manage Bookings</a> --}}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5 class="card-title">Unread Messages</h5>
                            <p class="card-text display-4">{{ $stats['unread_messages'] }}</p>
                            {{-- <a href="{{ route('website.admin.contact-messages.index') }}" class="btn btn-sm btn-primary">View Messages</a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection