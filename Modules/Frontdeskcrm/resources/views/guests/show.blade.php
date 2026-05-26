@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('frontdesk.registrations.dashboard') }}">Front Desk</a></li>
    <li class="breadcrumb-item"><a href="{{ route('frontdesk.guests.index') }}">Guests</a></li>
    <li class="breadcrumb-item active">{{ $guest->full_name }}</li>
@endsection

@section('page-content')
<div class="container-fluid py-4">
    
    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fas fa-user me-2 text-primary"></i>{{ $guest->title }} {{ $guest->full_name }}
            </h1>
            <p class="text-muted mb-0">{{ $guest->company_name ?? 'Individual Guest' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontdesk.guests.edit', $guest->id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="{{ route('frontdesk.guests.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Guest Info Card --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title mb-0"><i class="fas fa-id-card me-2"></i>Guest Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="small text-muted">Full Name</label>
                        <p class="mb-0 fw-bold">{{ $guest->title }} {{ $guest->full_name }}</p>
                    </div>
                    @if($guest->gender)
                    <div class="mb-3">
                        <label class="small text-muted">Gender</label>
                        <p class="mb-0">{{ $guest->gender }}</p>
                    </div>
                    @endif
                    @if($guest->birthday)
                    <div class="mb-3">
                        <label class="small text-muted">Date of Birth</label>
                        <p class="mb-0">{{ $guest->birthday->format('F d, Y') }}</p>
                    </div>
                    @endif
                    @if($guest->nationality)
                    <div class="mb-3">
                        <label class="small text-muted">Nationality</label>
                        <p class="mb-0">{{ $guest->nationality }}</p>
                    </div>
                    @endif
                    @if($guest->identification_type)
                    <div class="mb-3">
                        <label class="small text-muted">{{ $guest->identification_type }}</label>
                        <p class="mb-0">{{ $guest->identification_number ?? 'N/A' }}</p>
                    </div>
                    @endif
                    <hr>
                    <div class="mb-3">
                        <label class="small text-muted">Phone Number</label>
                        <p class="mb-0">
                            <a href="tel:{{ $guest->contact_number }}" class="text-primary">
                                <i class="fas fa-phone me-1"></i>{{ $guest->contact_number }}
                            </a>
                        </p>
                    </div>
                    @if($guest->email)
                    <div class="mb-3">
                        <label class="small text-muted">Email Address</label>
                        <p class="mb-0">
                            <a href="mailto:{{ $guest->email }}" class="text-primary">
                                <i class="fas fa-envelope me-1"></i>{{ $guest->email }}
                            </a>
                        </p>
                    </div>
                    @endif
                    @if($guest->home_address)
                    <div class="mb-3">
                        <label class="small text-muted">Address</label>
                        <p class="mb-0">{{ $guest->home_address }}</p>
                        <small class="text-muted">{{ implode(', ', array_filter([$guest->city, $guest->state, $guest->zip_code])) }}</small>
                    </div>
                    @endif
                    <div>
                        <label class="small text-muted">Guest Since</label>
                        <p class="mb-0">{{ $guest->created_at->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>

            {{-- Emergency Contact --}}
            @if($guest->emergency_name)
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-danger text-white py-3">
                    <h6 class="card-title mb-0"><i class="fas fa-first-aid me-2"></i>Emergency Contact</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1 fw-bold">{{ $guest->emergency_name }}</p>
                    <p class="mb-1 text-muted small">{{ $guest->emergency_relationship }}</p>
                    <a href="tel:{{ $guest->emergency_contact }}" class="text-danger">
                        <i class="fas fa-phone me-1"></i>{{ $guest->emergency_contact }}
                    </a>
                </div>
            </div>
            @endif
        </div>

        {{-- Statistics & Stay History --}}
        <div class="col-md-8">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                        <div class="card-body text-center">
                            <h3 class="fw-bold mb-0">{{ number_format($stats['total_stays']) }}</h3>
                            <small class="opacity-75">Total Stays</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <h3 class="fw-bold mb-0 text-success">{{ number_format($stats['total_nights']) }}</h3>
                            <small class="text-muted">Total Nights</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <h6 class="fw-bold mb-0 text-info">
                                {{ $stats['first_visit'] ? $stats['first_visit']->format('M Y') : 'N/A' }}
                            </h6>
                            <small class="text-muted">First Visit</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <h6 class="fw-bold mb-0 text-warning">
                                {{ $stats['last_visit'] ? $stats['last_visit']->format('M d, Y') : 'N/A' }}
                            </h6>
                            <small class="text-muted">Last Visit</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stay History --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-history me-2"></i>Stay History</h5>
                </div>
                <div class="card-body">
                    @if($guest->registrations->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No stay history yet</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Ref #</th>
                                        <th>Check-In</th>
                                        <th>Check-Out</th>
                                        <th>Room</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($guest->registrations as $registration)
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-primary">{{ $registration->reg_number ?? '#' . $registration->id }}</span>
                                            </td>
                                            <td>{{ $registration->check_in?->format('M d, Y') ?? 'N/A' }}</td>
                                            <td>{{ $registration->check_out?->format('M d, Y') ?? 'N/A' }}</td>
                                            <td>{{ $registration->room?->room_number ?? 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'Draft' => 'secondary', 
                                                        'Pending' => 'warning', 
                                                        'Checked-In' => 'success', 
                                                        'Checked-Out' => 'info',
                                                        'No-Show' => 'danger'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$registration->status] ?? 'secondary' }}">
                                                    {{ $registration->status }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('frontdesk.registrations.show', $registration->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
