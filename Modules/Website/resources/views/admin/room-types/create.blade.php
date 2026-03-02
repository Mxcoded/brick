@extends('layouts.master')

@section('title', 'Create Room Type')

@section('page-content')
    <div class="container-fluid py-4">
        <div class="mb-4">
            <a href="{{ route('website.admin.room-types.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Room Types
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0"><i class="fas fa-bed me-2 text-primary"></i> Create New Room Type</h5>
            </div>

            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('website.admin.room-types.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        {{-- Basic Information --}}
                        <div class="col-md-8">
                            <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-1"></i> Basic Information</h6>
                            
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label fw-bold">Room Type Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" 
                                           placeholder="e.g., Deluxe Suite, Standard Room" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Display Order</label>
                                    <input type="number" name="display_order" class="form-control" value="{{ old('display_order', 0) }}" min="0">
                                    <small class="text-muted">Lower numbers appear first</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Price per Night <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₦</span>
                                        <input type="number" name="price" class="form-control" value="{{ old('price') }}" 
                                               min="0" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Capacity <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="capacity" class="form-control" value="{{ old('capacity', 2) }}" 
                                               min="1" required>
                                        <span class="input-group-text">guests</span>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Room Size</label>
                                    <input type="text" name="size" class="form-control" value="{{ old('size') }}" 
                                           placeholder="e.g., 45 sqm">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Bed Type</label>
                                    <select name="bed_type" class="form-select">
                                        <option value="">Select bed type</option>
                                        <option value="King Size" {{ old('bed_type') == 'King Size' ? 'selected' : '' }}>King Size</option>
                                        <option value="Queen Size" {{ old('bed_type') == 'Queen Size' ? 'selected' : '' }}>Queen Size</option>
                                        <option value="Double Bed" {{ old('bed_type') == 'Double Bed' ? 'selected' : '' }}>Double Bed</option>
                                        <option value="Twin Beds" {{ old('bed_type') == 'Twin Beds' ? 'selected' : '' }}>Twin Beds</option>
                                        <option value="Single Bed" {{ old('bed_type') == 'Single Bed' ? 'selected' : '' }}>Single Bed</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Video URL</label>
                                    <input type="url" name="video_url" class="form-control" value="{{ old('video_url') }}" 
                                           placeholder="YouTube or Vimeo link">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="4" required 
                                          placeholder="Describe this room type...">{{ old('description') }}</textarea>
                            </div>

                            {{-- Amenities --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">Amenities</label>
                                <div class="row">
                                    @foreach ($amenities as $amenity)
                                        <div class="col-md-4 col-6">
                                            <div class="form-check">
                                                <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" 
                                                       class="form-check-input" id="amenity{{ $amenity->id }}"
                                                       {{ in_array($amenity->id, old('amenities', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="amenity{{ $amenity->id }}">
                                                    <i class="{{ $amenity->icon ?? 'fas fa-check' }} text-muted me-1"></i>
                                                    {{ $amenity->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Sidebar --}}
                        <div class="col-md-4">
                            {{-- Status Options --}}
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">Status</h6>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_active" value="1" class="form-check-input" 
                                               id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active (visible on website)</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="is_featured" value="1" class="form-check-input" 
                                               id="is_featured" {{ old('is_featured') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_featured">Featured (show on homepage)</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Main Image --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">Main Image <span class="text-danger">*</span></label>
                                <input type="file" name="image" class="form-control" accept="image/*" required>
                                <small class="text-muted">Max 20MB. Will be compressed automatically.</small>
                            </div>

                            {{-- Gallery --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">Gallery Images</label>
                                <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                                <small class="text-muted">Select multiple images for the gallery.</small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Initial Room Units --}}
                    <h6 class="text-muted mb-3"><i class="fas fa-door-open me-1"></i> Room Units (Optional)</h6>
                    <p class="small text-muted mb-3">Add the physical rooms of this type. You can add more units later.</p>

                    <div id="units-container">
                        <div class="row unit-row mb-2">
                            <div class="col-md-5">
                                <input type="text" name="units[0][room_number]" class="form-control" placeholder="Room Number (e.g., 101)">
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="units[0][floor]" class="form-control" placeholder="Floor (e.g., 1st Floor)">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger btn-remove-unit w-100" disabled>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="add-unit-btn">
                        <i class="fas fa-plus me-1"></i> Add Another Unit
                    </button>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('website.admin.room-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Create Room Type
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let unitIndex = 1;
    const container = document.getElementById('units-container');
    const addBtn = document.getElementById('add-unit-btn');

    addBtn.addEventListener('click', function() {
        const row = document.createElement('div');
        row.className = 'row unit-row mb-2';
        row.innerHTML = `
            <div class="col-md-5">
                <input type="text" name="units[${unitIndex}][room_number]" class="form-control" placeholder="Room Number (e.g., 10${unitIndex + 1})">
            </div>
            <div class="col-md-5">
                <input type="text" name="units[${unitIndex}][floor]" class="form-control" placeholder="Floor (e.g., 1st Floor)">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-remove-unit w-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        unitIndex++;
        updateRemoveButtons();
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-unit')) {
            e.target.closest('.unit-row').remove();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        const rows = container.querySelectorAll('.unit-row');
        rows.forEach((row, index) => {
            const btn = row.querySelector('.btn-remove-unit');
            btn.disabled = rows.length === 1;
        });
    }
});
</script>
@endpush
