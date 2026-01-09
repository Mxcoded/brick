@extends('layouts.master')

@section('title', 'Room Inventory')

@section('page-content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-gray-800">Room Inventory</h1>
            <a href="{{ route('website.admin.rooms.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus me-1"></i> Add New Room
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4 py-3">Room</th>
                                <th>Price / Night</th>
                                <th>Capacity</th>
                                <th>Amenities</th> {{-- Added Header --}}
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($rooms as $room)
                                <tr>
                                    {{-- Room Name & Image --}}
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            @if($room->image_url)
                                                <img src="{{ $room->image_url }}" class="rounded shadow-sm me-3" style="width: 60px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="rounded bg-secondary d-flex align-items-center justify-content-center me-3 text-white" style="width: 60px; height: 40px;">
                                                    <i class="fas fa-camera"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold text-dark">{{ $room->name }}</div>
                                                <div class="small text-muted">{{ $room->bed_type ?? 'Standard Bed' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Price --}}
                                    <td class="fw-bold text-success">₦{{ number_format($room->price, 2) }}</td>

                                    {{-- Capacity --}}
                                    <td><i class="fas fa-user-friends text-muted me-1"></i> {{ $room->capacity }}</td>

                                    {{-- ✅ AMENITIES COLUMN --}}
                                    <td style="width: 30%;">
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($room->amenities->take(3) as $amenity)
                                                <span class="badge bg-light text-dark border fw-normal">
                                                    <i class="{{ $amenity->icon ?? 'fas fa-check' }} text-primary me-1" style="font-size: 10px;"></i> 
                                                    {{ $amenity->name }}
                                                </span>
                                            @endforeach
                                            
                                            @if ($room->amenities->count() > 3)
                                                <span class="badge bg-light text-muted border">
                                                    +{{ $room->amenities->count() - 3 }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        @if($room->status === 'available')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Available</span>
                                        @elseif($room->status === 'booked')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Booked</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Maintenance</span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="{{ route('website.admin.rooms.show', $room->id) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('website.admin.rooms.edit', $room->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('website.admin.rooms.destroy', $room->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure? This will delete the room and all its images.');">
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
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <div class="mb-3"><i class="fas fa-bed fa-3x text-light"></i></div>
                                        <h5>No rooms found</h5>
                                        <p class="small">Get started by creating your first room type.</p>
                                        <a href="{{ route('website.admin.rooms.create') }}" class="btn btn-sm btn-primary mt-2">Add Room</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($rooms->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $rooms->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection