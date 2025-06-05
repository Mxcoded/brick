@extends('admin::layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('page-content')
    <h1>Admin Dashboard</h1>
    <p>Welcome to the admin panel.</p>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Statistics</h5>
                </div>
                <div class="card-body">
                    <p>Some statistics here...</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Upcoming Events</h5>
                        </div>
                        <div class="card-body">
                            @if($upcomingEvents->isEmpty())
                                <p class="text-muted">No upcoming events.</p>
                            @else
                                <ul class="list-group">
                                    @foreach($upcomingEvents as $event)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Order #{{ $event->order_id }}</strong> - {{ $event->customer->name }} <br>
                                                Starts on {{ \Carbon\Carbon::parse($event->earliest_event_date)->format('M d, Y') }} <br>
                                                Status: <span class="badge bg-{{ $event->status == 'Confirmed' ? 'success' : 'warning' }}">{{ $event->status }}</span>
                                            </div>
                                            <a href="{{ route('banquet.orders.show', $event->order_id) }}" class="btn btn-sm btn-outline-primary">
                                                View Details
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection