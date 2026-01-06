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

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Room</th>
                            <th>Price / Night</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Features</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($rooms as $room)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    @dd($room->image_url)
                                    <img src="{{ $room->image_url ?? 'https://via.placeholder.com/50' }}" 
                                         class="rounded me-3" style="width: 48px; height: 48px; object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $room->name }}</div>
                                        <div class="small text-muted">{{ $room->bed_type }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-bold text-primary">₦{{ number_format($room->price, 2) }}</td>
                            <td><i class="fas fa-user-friends text-muted me-1"></i> {{ $room->capacity }}</td>
                            <td>
                                @if($room->status === 'available')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3">Available</span>
                                @elseif($room->status === 'maintenance')
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3">Maintenance</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3">{{ ucfirst($room->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($room->is_featured)
                                    <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i> Featured</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    {{-- <a href="{{ route('website.admin.rooms.show', $room->id) }}" class="btn btn-sm btn-outline-primary me-2">
                                        <i class="fas fa-eye"></i>
                                    </a> --}}
                                    <a href="{{ route('website.admin.rooms.edit', $room->id) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('website.admin.rooms.destroy', $room->id) }}" method="POST" 
                                          onsubmit="return confirm('Are you sure? This action cannot be undone.');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
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
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($rooms->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $rooms->links() }}
        </div>
        @endif
    </div>
</div>
@endsection