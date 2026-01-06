@extends('layouts.master')

@section('title', 'Edit Room')

@section('page-content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Edit Room: {{ $room->name }}</h4>

                    <form action="{{ route('website.admin.rooms.update', $room->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Room Name</label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $room->name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Price per Night (₦)</label>
                                    <input type="number" name="price" class="form-control"
                                        value="{{ old('price', $room->price) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Capacity</label>
                                    <input type="number" name="capacity" class="form-control"
                                        value="{{ old('capacity', $room->capacity) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Size</label>
                                    <input type="text" name="size" class="form-control"
                                        value="{{ old('size', $room->size) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Bed Type</label>
                                    <select name="bed_type" class="form-control">
                                        @foreach (['King Size', 'Queen Size', 'Twin Beds', 'Double Bed'] as $type)
                                            <option value="{{ $type }}"
                                                {{ $room->bed_type == $type ? 'selected' : '' }}>{{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="5" required>{{ old('description', $room->description) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
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
                                <div class="form-group">
                                    <label>Video URL</label>
                                    <input type="url" name="video_url" class="form-control"
                                        value="{{ old('video_url', $room->video_url) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="d-block mb-2">Amenities</label>
                            <div class="row">
                                @php
                                    $amenitiesList = [
                                        'Free Wi-Fi',
                                        'Breakfast Included',
                                        'Air Conditioning',
                                        'Smart TV',
                                        'Mini Bar',
                                        'Ocean View',
                                        'Room Service',
                                        'Gym Access',
                                        'Swimming Pool',
                                    ];
                                    $currentAmenities = $room->amenities ?? [];
                                @endphp
                                @foreach ($amenitiesList as $amenity)
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="amenities[]"
                                                    value="{{ $amenity }}"
                                                    {{ in_array($amenity, $currentAmenities) ? 'checked' : '' }}>
                                                {{ $amenity }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label>Primary Image</label>
                                <input type="file" name="image" class="form-control mb-2">
                                @if ($room->image_url)
                                    <img src="{{ $room->image_url }}" alt="Primary" class="img-thumbnail"
                                        style="height: 100px;">
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label>Add Gallery Images</label>
                                <input type="file" name="gallery_images[]" class="form-control" multiple>
                            </div>
                        </div>

                        @if ($room->images && $room->images->count() > 0)
                            <label>Current Gallery</label>
                            <div class="d-flex flex-wrap gap-2 mb-4">
                                @foreach ($room->images as $img)
                                    <div class="position-relative">
                                        <img src="{{ $img->image_url }}" class="rounded"
                                            style="width: 100px; height: 100px; object-fit: cover;">
                                        <a href="{{ route('website.admin.rooms.image.delete', $img->id) }}"
                                            onclick="return confirm('Delete this image?')"
                                            class="btn btn-xs btn-danger position-absolute top-0 start-100 translate-middle rounded-circle p-1"
                                            style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                            &times;
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="form-check mb-4">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="is_featured" value="1"
                                    {{ $room->is_featured ? 'checked' : '' }}>
                                Feature this room on Homepage
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Update Room</button>
                        <a href="{{ route('website.admin.rooms.index') }}" class="btn btn-light">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Primary Image Preview
        document.getElementById('primary_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('img-fluid', 'rounded', 'shadow-sm');
                    img.style.maxHeight = '200px';
                    document.getElementById('primary_image_preview').innerHTML = '';
                    document.getElementById('primary_image_preview').appendChild(img);
                }
                reader.readAsDataURL(file);
            }
        });

        // Additional Images Preview
        document.getElementById('images').addEventListener('change', function(e) {
            const files = e.target.files;
            const preview = document.getElementById('images_preview');
            preview.innerHTML = '';
            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.classList.add('me-2', 'mb-2');
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('img-fluid', 'rounded', 'shadow-sm');
                    img.style.maxHeight = '100px';
                    div.appendChild(img);
                    preview.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        });

        // Video Preview
        document.getElementById('video').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const video = document.createElement('video');
                video.src = URL.createObjectURL(file);
                video.controls = true;
                video.classList.add('img-fluid', 'rounded', 'shadow-sm');
                video.style.maxHeight = '200px';
                document.getElementById('video_preview').innerHTML = '';
                document.getElementById('video_preview').appendChild(video);
            }
        });
    </script>
@endpush
