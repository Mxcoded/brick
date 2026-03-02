@extends('layouts.master')

@section('title', 'Edit ' . $roomType->name)

@section('page-content')
    <div class="container-fluid py-4">
        <div class="mb-4">
            <a href="{{ route('website.admin.room-types.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Room Types
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0"><i class="fas fa-edit me-2 text-primary"></i> Edit Room Type: {{ $roomType->name }}</h5>
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

                <form action="{{ route('website.admin.room-types.update', $roomType->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        {{-- Basic Information --}}
                        <div class="col-md-8">
                            <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-1"></i> Basic Information</h6>
                            
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label fw-bold">Room Type Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" 
                                           value="{{ old('name', $roomType->name) }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Display Order</label>
                                    <input type="number" name="display_order" class="form-control" 
                                           value="{{ old('display_order', $roomType->display_order) }}" min="0">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Price per Night <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₦</span>
                                        <input type="number" name="price" class="form-control" 
                                               value="{{ old('price', $roomType->price) }}" min="0" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Capacity <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="capacity" class="form-control" 
                                               value="{{ old('capacity', $roomType->capacity) }}" min="1" required>
                                        <span class="input-group-text">guests</span>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Room Size</label>
                                    <input type="text" name="size" class="form-control" 
                                           value="{{ old('size', $roomType->size) }}" placeholder="e.g., 45 sqm">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Bed Type</label>
                                    <select name="bed_type" class="form-select">
                                        <option value="">Select bed type</option>
                                        @foreach(['King Size', 'Queen Size', 'Double Bed', 'Twin Beds', 'Single Bed'] as $type)
                                            <option value="{{ $type }}" {{ old('bed_type', $roomType->bed_type) == $type ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Video URL</label>
                                    <input type="url" name="video_url" class="form-control" 
                                           value="{{ old('video_url', $roomType->video_url) }}" placeholder="YouTube or Vimeo link">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="4" required>{{ old('description', $roomType->description) }}</textarea>
                            </div>

                            {{-- Amenities --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">Amenities</label>
                                <div class="row">
                                    @php $selectedAmenities = old('amenities', $roomType->amenities->pluck('id')->toArray()); @endphp
                                    @foreach ($amenities as $amenity)
                                        <div class="col-md-4 col-6">
                                            <div class="form-check">
                                                <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" 
                                                       class="form-check-input" id="amenity{{ $amenity->id }}"
                                                       {{ in_array($amenity->id, $selectedAmenities) ? 'checked' : '' }}>
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
                                               id="is_active" {{ old('is_active', $roomType->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active (visible on website)</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="is_featured" value="1" class="form-check-input" 
                                               id="is_featured" {{ old('is_featured', $roomType->is_featured) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_featured">Featured (show on homepage)</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Current Main Image --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">Main Image</label>
                                @if($roomType->image_url)
                                    <div class="mb-2">
                                        <img src="{{ $roomType->image_url }}" class="img-thumbnail" style="max-height: 150px;">
                                    </div>
                                @endif
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <small class="text-muted">Leave empty to keep current image.</small>
                            </div>

                            {{-- Gallery --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">Gallery Images</label>
                                @if($roomType->images->count() > 0)
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        @foreach($roomType->images as $img)
                                            <div class="position-relative">
                                                <img src="{{ $img->image_url }}" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                                <form action="{{ route('website.admin.room-types.images.destroy', $img->id) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Delete this image?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0" 
                                                            style="width: 18px; height: 18px; font-size: 10px; line-height: 1;">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                                <small class="text-muted">Add more images to the gallery.</small>
                            </div>

                            {{-- Units Summary --}}
                            <div class="card bg-primary-subtle border-0">
                                <div class="card-body">
                                    <h6 class="mb-2">Room Units</h6>
                                    <p class="mb-2">
                                        <strong>{{ $roomType->units->count() }}</strong> unit(s) configured
                                    </p>
                                    <a href="{{ route('website.admin.room-types.show', $roomType->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-door-open me-1"></i> Manage Units
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('website.admin.room-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
