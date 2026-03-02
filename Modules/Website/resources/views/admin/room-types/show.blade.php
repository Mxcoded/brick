@extends('layouts.master')

@section('title', $roomType->name)

@section('page-content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('website.admin.room-types.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fas fa-arrow-left me-1"></i> Back to Room Types
                </a>
                <h1 class="h3 text-gray-800 mb-0">{{ $roomType->name }}</h1>
            </div>
            <a href="{{ route('website.admin.room-types.edit', $roomType->id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Edit Room Type
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

        <div class="row">
            {{-- Room Type Details --}}
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm">
                    @if($roomType->image_url)
                        <img src="{{ $roomType->image_url }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                    @endif
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title mb-0">{{ $roomType->name }}</h5>
                            @if($roomType->is_featured)
                                <span class="badge bg-warning text-dark"><i class="fas fa-star"></i> Featured</span>
                            @endif
                        </div>

                        <p class="text-muted small mb-3">{{ Str::limit($roomType->description, 150) }}</p>

                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="fw-bold text-success">₦{{ number_format($roomType->price) }}</div>
                                <small class="text-muted">per night</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold">{{ $roomType->capacity }}</div>
                                <small class="text-muted">guests</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold">{{ $roomType->units->count() }}</div>
                                <small class="text-muted">units</small>
                            </div>
                        </div>

                        @if($roomType->amenities->count() > 0)
                            <div class="border-top pt-3">
                                <h6 class="small text-muted mb-2">Amenities</h6>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($roomType->amenities as $amenity)
                                        <span class="badge bg-light text-dark border">
                                            <i class="{{ $amenity->icon ?? 'fas fa-check' }} me-1"></i>
                                            {{ $amenity->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Room Units --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-door-open me-2 text-primary"></i> Room Units</h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                            <i class="fas fa-plus me-1"></i> Add Unit
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Room Number</th>
                                        <th>Floor</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roomType->units as $unit)
                                        <tr>
                                            <td class="ps-4 fw-bold">{{ $unit->room_number }}</td>
                                            <td>{{ $unit->floor ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $unit->status_color }}-subtle text-{{ $unit->status_color }} border">
                                                    {{ ucfirst($unit->status) }}
                                                </span>
                                            </td>
                                            <td class="small text-muted">{{ Str::limit($unit->notes, 30) ?? '-' }}</td>
                                            <td class="text-end pe-4">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" data-bs-target="#editUnitModal{{ $unit->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('website.admin.room-units.destroy', $unit->id) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Delete this unit?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        {{-- Edit Unit Modal --}}
                                        <div class="modal fade" id="editUnitModal{{ $unit->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('website.admin.room-units.update', $unit->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Unit: {{ $unit->room_number }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Room Number</label>
                                                                <input type="text" name="room_number" class="form-control" 
                                                                       value="{{ $unit->room_number }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Floor</label>
                                                                <input type="text" name="floor" class="form-control" 
                                                                       value="{{ $unit->floor }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Status</label>
                                                                <select name="status" class="form-select" required>
                                                                    <option value="available" {{ $unit->status == 'available' ? 'selected' : '' }}>Available</option>
                                                                    <option value="occupied" {{ $unit->status == 'occupied' ? 'selected' : '' }}>Occupied</option>
                                                                    <option value="maintenance" {{ $unit->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                                                    <option value="blocked" {{ $unit->status == 'blocked' ? 'selected' : '' }}>Blocked</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Notes</label>
                                                                <textarea name="notes" class="form-control" rows="2">{{ $unit->notes }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fas fa-door-open fa-2x mb-2 d-block text-light"></i>
                                                No units added yet. Click "Add Unit" to create one.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Unit Modal --}}
    <div class="modal fade" id="addUnitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('website.admin.room-types.units.store', $roomType->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Unit to {{ $roomType->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Room Number <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" class="form-control" placeholder="e.g., 101, Suite A" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Floor</label>
                            <input type="text" name="floor" class="form-control" placeholder="e.g., 1st Floor, Ground">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Internal notes about this unit..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Unit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
