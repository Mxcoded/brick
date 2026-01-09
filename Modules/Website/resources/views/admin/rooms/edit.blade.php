@extends('layouts.master')

@section('title', 'Edit Room')

@section('page-content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Edit Room: <span class="text-primary">{{ $room->name }}</span></h4>
                        <a href="{{ route('website.admin.rooms.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>

                    {{-- ✅ NICE UI: SUCCESS MESSAGE --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-2 fs-4 text-success"></i>
                                <div>
                                    <strong>Success!</strong> {{ session('success') }}
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- ⚠️ NICE UI: ERROR MESSAGES --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-exclamation-circle me-2 fs-4 text-danger"></i>
                                <strong>Please fix the following errors:</strong>
                            </div>
                            <ul class="mb-0 mt-2 ps-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

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
                                    <input type="number" name="price" class="form-control" step="0.01"
                                        value="{{ old('price', $room->price) }}" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Capacity (Guests) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="capacity" class="form-control"
                                        value="{{ old('capacity', $room->capacity) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Room Size (e.g. 30 sqm)</label>
                                    <input type="text" name="size" class="form-control"
                                        value="{{ old('size', $room->size) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Bed Type</label>
                                    <input type="text" name="bed_type" class="form-control"
                                        value="{{ old('bed_type', $room->bed_type) }}" placeholder="e.g. King Size">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                    <textarea name="description" class="form-control" rows="4" required>{{ old('description', $room->description) }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="available" {{ $room->status == 'available' ? 'selected' : '' }}>
                                            Available</option>
                                        <option value="booked" {{ $room->status == 'booked' ? 'selected' : '' }}>Booked
                                        </option>
                                        <option value="maintenance" {{ $room->status == 'maintenance' ? 'selected' : '' }}>
                                            Maintenance</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Video URL (Optional)</label>
                                    <input type="url" name="video_url" id="video_url" class="form-control"
                                        value="{{ old('video_url', $room->video_url) }}" placeholder="https://youtube.com/...">
                                    <div id="video_preview_link"></div>
                                </div>
                            </div>

                            <div class="col-12 mb-4">
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
                                                        @if($amenity->icon) <i class="{{ $amenity->icon }} ms-1 text-muted"></i> @endif
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label fw-bold">Main Room Image</label>
                                <div class="d-flex align-items-start gap-3 flex-wrap">
                                    @if ($room->image_url)
                                        <div class="text-center">
                                            <img src="{{ $room->image_url }}" alt="Main Image" class="img-thumbnail shadow-sm"
                                                style="width: 150px; height: 100px; object-fit: cover;">
                                            <small class="d-block text-muted mt-1">Current Main Image</small>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                        <small class="text-muted">Upload to replace the current main image.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label fw-bold">Room Gallery</label>
                                <div class="card bg-light border-0 p-3">
                                    @if ($room->images->count() > 0)
                                        <div class="row mb-3">
                                            <div class="col-12"><small class="text-muted mb-2 d-block fw-bold">Current Gallery Images</small></div>
                                            @foreach ($room->images as $img)
                                                <div class="col-6 col-md-3 col-lg-2 position-relative mb-2">
                                                    <img src="{{ $img->image_url }}" class="img-thumbnail w-100 shadow-sm"
                                                        style="height: 100px; object-fit: cover;">

                                                    <button type="button"
                                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle p-0 d-flex align-items-center justify-content-center shadow"
                                                        style="width: 24px; height: 24px;"
                                                        title="Delete this image"
                                                        onclick="if(confirm('Are you sure you want to delete this image?')) { document.getElementById('delete-img-{{ $img->id }}').submit(); }">
                                                        <i class="fas fa-times" style="font-size: 12px;"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="form-group">
                                        <label>Add More Gallery Images</label>
                                        <input type="file" name="gallery_images[]" id="gallery_images"
                                            class="form-control" multiple accept="image/*">
                                        <div class="mt-2 row" id="gallery_preview_container"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form> @foreach ($room->images as $img)
                        <form id="delete-img-{{ $img->id }}"
                            action="{{ route('website.admin.rooms.image.delete', $img->id) }}" method="POST"
                            style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Gallery Images Preview
        document.getElementById('gallery_images').addEventListener('change', function(e) {
            const files = e.target.files;
            const preview = document.getElementById('gallery_preview_container');
            preview.innerHTML = ''; // Clear previous selections

            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-thumbnail me-2 mb-2 shadow-sm';
                    img.style.width = '80px';
                    img.style.height = '80px';
                    img.style.objectFit = 'cover';
                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        });

        // Video URL Live Check
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