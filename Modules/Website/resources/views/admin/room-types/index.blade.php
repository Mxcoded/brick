@extends('layouts.master')

@section('title', 'Room Types')

@section('page-content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-gray-800 mb-1">Room Types</h1>
                <p class="text-muted small mb-0">Manage room categories and their physical units</p>
            </div>
            <a href="{{ route('website.admin.room-types.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus me-1"></i> Add Room Type
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4 py-3">Room Type</th>
                                <th>Price / Night</th>
                                <th>Capacity</th>
                                <th>Units</th>
                                <th>Availability</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($roomTypes as $roomType)
                                @php
                                    // Calculate real-time availability for today
                                    $today = \Carbon\Carbon::today();
                                    $totalUnits = $roomType->units_count;
                                    $maintenanceUnits = $roomType->units->where('status', 'maintenance')->count();
                                    
                                    // Get truly available units (considering bookings for today)
                                    $availableUnits = $roomType->getAvailabilityCountForDates($today, $today->copy()->addDay());
                                @endphp
                                <tr>
                                    {{-- Room Type Name & Image --}}
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            @if($roomType->image_url)
                                                <img src="{{ $roomType->image_url }}" class="rounded shadow-sm me-3" 
                                                     style="width: 60px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="rounded bg-secondary d-flex align-items-center justify-content-center me-3 text-white" 
                                                     style="width: 60px; height: 40px;">
                                                    <i class="fas fa-camera"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold text-dark">
                                                    {{ $roomType->name }}
                                                    @if($roomType->is_featured)
                                                        <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">
                                                            <i class="fas fa-star"></i> Featured
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="small text-muted">{{ $roomType->bed_type ?? 'Standard Bed' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Price --}}
                                    <td class="fw-bold text-success">₦{{ number_format($roomType->price, 2) }}</td>

                                    {{-- Capacity --}}
                                    <td><i class="fas fa-user-friends text-muted me-1"></i> {{ $roomType->capacity }}</td>

                                    {{-- Units Count --}}
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border">
                                            {{ $totalUnits }} unit{{ $totalUnits != 1 ? 's' : '' }}
                                        </span>
                                    </td>

                                    {{-- Availability --}}
                                    <td>
                                        @if($totalUnits == 0)
                                            <span class="text-muted small">No units</span>
                                        @else
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 8px; width: 80px;">
                                                    <div class="progress-bar bg-success" style="width: {{ ($availableUnits / $totalUnits) * 100 }}%"></div>
                                                    <div class="progress-bar bg-warning" style="width: {{ ($maintenanceUnits / $totalUnits) * 100 }}%"></div>
                                                </div>
                                                <span class="small text-muted">{{ $availableUnits }}/{{ $totalUnits }}</span>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        @if($roomType->is_active)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border rounded-pill">Inactive</span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="{{ route('website.admin.room-types.show', $roomType->id) }}" 
                                               class="btn btn-sm btn-outline-secondary" title="View & Manage Units">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('website.admin.room-types.edit', $roomType->id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('website.admin.room-types.destroy', $roomType->id) }}" 
                                                  method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Are you sure? This will delete the room type and all its units.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="mb-3"><i class="fas fa-bed fa-3x text-light"></i></div>
                                        <h5>No room types found</h5>
                                        <p class="small">Get started by creating your first room type.</p>
                                        <a href="{{ route('website.admin.room-types.create') }}" class="btn btn-sm btn-primary mt-2">
                                            Add Room Type
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($roomTypes->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $roomTypes->links() }}
                </div>
            @endif
        </div>

        {{-- Legend --}}
        <div class="mt-3 small text-muted">
            <span class="me-3"><i class="fas fa-circle text-success"></i> Available Today</span>
            <span class="me-3"><i class="fas fa-circle text-warning"></i> Maintenance</span>
            <span><i class="fas fa-circle text-danger"></i> Booked/Occupied</span>
            <span class="ms-3 text-muted">| Availability shown is real-time for today</span>
        </div>
    </div>
@endsection
