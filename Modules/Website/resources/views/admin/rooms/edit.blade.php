@extends('layouts.master')

@section('title', 'Edit Room')

@section('page-content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Edit Room: <span class="text-primary">{{ $room->name }}</span></h4>
                        <a href="{{ route('website.admin.rooms.index') }}" class="btn btn-outline-secondary btn-sm">Back to
                            List</a>
                    </div>

                    <form action="{{ route('website.admin.rooms.update', $room->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Room Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $room->name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Price per Night (₦) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="price" class="form-control"
                                        value="{{ old('price', $room->price) }}" required min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Capacity</label>
                                    <input type="number" name="capacity" class="form-control"
                                        value="{{ old('capacity', $room->capacity) }}" min="1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Size</label>
                                    <input type="text" name="size" class="form-control"
                                        value="{{ old('size', $room->size) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Bed Type</label>
                                    <select name="bed_type" class="form-select">
                                        @foreach (['King Size', 'Queen Size', 'Twin Beds', 'Double Bed'] as $type)
                                            <option value="{{ $type }}"
                                                {{ $room->bed_type == $type ? 'selected' : '' }}>{{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="5" required>{{ old('description', $room->description) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="available" {{ $room->status == 'available' ? 'selected' : '' }}>
                                            Available</option>
                                        <option value="maintenance" {{ $room->status == 'maintenance' ? 'selected' : '' }}>
                                            Maintenance</option>
                                        <option value="booked" {{ $room->status == 'booked' ? 'selected' : '' }}>Fully
                                            Booked</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Video URL (YouTube)</label>
                                    <input type="url" name="video_url" id="video_url" class="form-control"
                                        value="{{ old('video_url', $room->video_url) }}"
                                        placeholder="https://www.youtube.com/watch?v=...">
                                    <small class="text-muted" id="video_preview_link">
                                        @if ($room->video_url)
                                            <a href="{{ $room->video_url }}" target="_blank"
                                                class="text-primary mt-1 d-block">
                                                <i class="fas fa-play-circle me-1"></i> Test Current Video Link
                                            </a>
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label fw-bold d-block mb-2">Amenities</label>
                            <div class="card bg-light border-0 p-3">
                                <div class="row">
                                    @foreach ($amenities as $amenity)
                                        <div class="col-md-4 col-lg-3 mb-2">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="amenities[]"
                                                    value="{{ $amenity->id }}" id="am_{{ $amenity->id }}"
                                                    {{ $room->amenities->contains($amenity->id) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="am_{{ $amenity->id }}">
                                                    {{ $amenity->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Primary Image</label>
                                <input type="file" name="image" id="primary_image" class="form-control mb-2"
                                    accept="image/*">

                                <div id="primary_image_preview_container">
                                    @if ($room->image_url)
                                        <div class="position-relative d-inline-block">
                                            <span
                                                class="badge bg-secondary position-absolute top-0 start-0 m-1">Current</span>
                                            <img src="{{ $room->image_url }}" alt="Primary" class="img-thumbnail"
                                                style="height: 150px; width: auto;">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Add to Gallery</label>
                                <input type="file" name="gallery_images[]" id="gallery_images"
                                    class="form-control mb-2" multiple accept="image/*">
                                <div id="gallery_preview_container" class="d-flex flex-wrap gap-2">
                                </div>
                            </div>
                        </div>

                        @if ($room->images && $room->images->count() > 0)
                            <div class="mb-4">
                                <label class="form-label fw-bold">Current Gallery</label>
                                <div class="card bg-light border-0 p-3">
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach ($room->images as $img)
                                            <div class="position-relative d-inline-block bg-white p-1 rounded shadow-sm">
                                                <img src="{{ $img->image_url }}" class="rounded"
                                                    style="width: 100px; height: 100px; object-fit: cover;">

                                                <form action="{{ route('website.admin.rooms.image.delete', $img->id) }}"
                                                    method="POST"
                                                    class="position-absolute top-0 start-100 translate-middle"
                                                    onsubmit="return confirm('Delete this image permanently?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-danger btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center shadow"
                                                        style="width: 24px; height: 24px;" title="Delete Image">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="form-check mb-4">
                            <input type="checkbox" class="form-check-input" name="is_featured" id="is_featured"
                                value="1" {{ $room->is_featured ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                Feature this room on Homepage
                            </label>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('website.admin.rooms.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // 1. Primary Image Preview
        document.getElementById('primary_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const container = document.getElementById('primary_image_preview_container');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Clear current content (including the "Current" image from DB)
                    container.innerHTML = '';

                    const wrapper = document.createElement('div');
                    wrapper.className = 'position-relative d-inline-block';

                    const badge = document.createElement('span');
                    badge.className = 'badge bg-success position-absolute top-0 start-0 m-1';
                    badge.innerText = 'New Selection';

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-thumbnail';
                    img.style.height = '150px';

                    wrapper.appendChild(badge);
                    wrapper.appendChild(img);
                    container.appendChild(wrapper);
                }
                reader.readAsDataURL(file);
            }
        });

        // 2. Gallery Images Preview
        document.getElementById('gallery_images').addEventListener('change', function(e) {
            const files = e.target.files;
            const preview = document.getElementById('gallery_preview_container');
            preview.innerHTML = ''; // Clear previous selections

            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-thumbnail';
                    img.style.width = '80px';
                    img.style.height = '80px';
                    img.style.objectFit = 'cover';
                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        });

        // 3. Video URL Live Check (Optional UX Enhancement)
        document.getElementById('video_url').addEventListener('input', function(e) {
            const url = e.target.value;
            const container = document.getElementById('video_preview_link');
            if (url.includes('http')) {
                container.innerHTML =
                    `<a href="${url}" target="_blank" class="text-success mt-1 d-block"><i class="fas fa-external-link-alt me-1"></i> Valid Link Format</a>`;
            } else {
                container.innerHTML = '';
            }
        });
    </script>
@endpush
