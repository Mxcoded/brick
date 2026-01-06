@extends('layouts.master')

@section('title', 'Website Overview')

@section('page-content')
<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Website Management</h1>
        <a href="{{ route('website.home') }}" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-external-link-alt me-1"></i> Visit Live Site
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase text-muted small fw-bold mb-1">Web Revenue</p>
                            <h3 class="fw-bold text-success mb-0">₦{{ number_format($stats['revenue'] ?? 0) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                            <i class="fas fa-money-bill-wave fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase text-muted small fw-bold mb-1">Pending Requests</p>
                            <h3 class="fw-bold text-warning mb-0">{{ $stats['pending_bookings'] ?? 0 }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase text-muted small fw-bold mb-1">Rooms Online</p>
                            <h3 class="fw-bold text-primary mb-0">{{ $rooms['available'] ?? 0 }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                            <i class="fas fa-bed fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase text-muted small fw-bold mb-1">New Messages</p>
                            <h3 class="fw-bold text-info mb-0">{{ $recentMessages->count() ?? 0 }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info">
                            <i class="fas fa-envelope fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-primary">Recent Web Bookings</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Ref</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBookings as $booking)
                            <tr>
                                <td class="ps-4 fw-bold small">{{ $booking->booking_reference }}</td>
                                <td>{{ $booking->guest_name }}</td>
                                <td>{{ $booking->room->name ?? 'Unknown' }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d') }}</td>
                                <td>
                                    <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('website.admin.bookings.show', $booking->id) }}" class="btn btn-xs btn-outline-dark">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No recent bookings found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-dark">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('website.admin.rooms.create') }}" class="btn btn-outline-primary text-start">
                            <i class="fas fa-plus-circle me-2"></i> Add New Room
                        </a>
                        <a href="{{ route('website.admin.bookings.index') }}" class="btn btn-outline-dark text-start">
                            <i class="fas fa-calendar-alt me-2"></i> Manage Calendar
                        </a>
                        <a href="{{ route('website.admin.settings.index') }}" class="btn btn-outline-secondary text-start">
                            <i class="fas fa-sliders-h me-2"></i> Site Configuration
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection