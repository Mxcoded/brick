@extends('website::layouts.admin')

@section('title', 'Edit Room')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-0">Edit Room</h1>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('website.admin.rooms.update', $room) }}" method="POST" enctype="multipart/form-data" id="update-room-form">
                @csrf
                @method('PUT')
                <!-- Basic Info Section -->
                <h4 class="mt-4">Basic Information</h4>
                <div class="mb-3">
                    <label for="name" class="form-label">Room Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $room->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description">{{ old('description', $room->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="price_per_night" class="form-label">Price per Night</label>
                    <input type="number" step="0.01" class="form-control @error('price_per_night') is-invalid @enderror" id="price_per_night" name="price_per_night" value="{{ old('price_per_night', $room->price_per_night) }}" required>
                    @error('price_per_night')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="capacity" class="form-label">Capacity</label>
                    <input type="number" class="form-control @error('capacity') is-invalid @enderror" id="capacity" name="capacity" value="{{ old('capacity', $room->capacity) }}" required>
                    @error('capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="size" class="form-label">Size (e.g., 30 sqm)</label>
                    <input type="text" class="form-control @error('size') is-invalid @enderror" id="size" name="size" value="{{ old('size', $room->size) }}" required>
                    @error('size')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="featured" class="form-label">Featured</label>
                    <select class="form-control @error('featured') is-invalid @enderror" id="featured" name="featured">
                        <option value="1" {{ old('featured', $room->featured) ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('featured', $room->featured) ? '' : 'selected' }}>No</option>
                    </select>
                    @error('featured')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Media Upload Section -->
                <h4 class="mt-4">Media Uploads</h4>
                <div class="mb-3">
                    <label for="primary_image" class="form-label">Primary Image (Optional - replaces current)</label>
                    <input type="file" class="form-control @error('primary_image') is-invalid @enderror" id="primary_image" name="primary_image" accept="image/*">
                    @error('primary_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if ($room->image)
                        <div class="mt-2">
                            <img src="{{ Storage::url($room->image) }}" alt="Current Primary Image" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                        </div>
                    @endif
                    <div id="primary_image_preview" class="mt-2"></div>
                </div>
                <div class="mb-3">
                    <label for="images" class="form-label">Additional Images (Optional - appends to existing)</label>
                    <input type="file" class="form-control @error('images') is-invalid @enderror" id="images" name="images[]" accept="image/*" multiple>
                    @error('images')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="images_preview" class="mt-2 d-flex flex-wrap"></div>
                </div>
                <!-- Current Images Section -->
                <h5 class="mt-4">Current Additional Images</h5>
                @if ($room->images->isEmpty())
                    <p class="text-muted">No additional images uploaded.</p>
                @else
                    <div class="row">
                        @foreach ($room->images as $image)
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <img src="{{ Storage::url($image->path) }}" class="card-img-top" alt="Room Image" style="height: 150px; object-fit: cover;">
                                    <div class="card-body text-center">
                                        <p>Manage images on <a href="{{ route('website.admin.rooms.show', $room) }}">show page</a></p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="mb-3">
                    <label for="video" class="form-label">Video (Optional - replaces current)</label>
                    <input type="file" class="form-control @error('video') is-invalid @enderror" id="video" name="video" accept="video/*">
                    @error('video')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if ($room->video)
                        <div class="mt-2">
                            <video src="{{ Storage::url($room->video) }}" controls class="img-fluid rounded shadow-sm" style="max-height: 200px;"></video>
                            <p class="mt-2">Manage video on <a href="{{ route('website.admin.rooms.show', $room) }}">show page</a></p>
                        </div>
                    @endif
                    <div id="video_preview" class="mt-2"></div>
                </div>

                <!-- Amenities Section -->
                <h4 class="mt-4">Amenities</h4>
                <div class="row">
                    @foreach (\Modules\Website\Models\Amenity::all() as $amenity)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="amenity_{{ $amenity->id }}" name="amenities[]" value="{{ $amenity->id }}" {{ in_array($amenity->id, old('amenities', $room->amenities->pluck('id')->toArray())) ? 'checked' : '' }}>
                                <label class="form-check-label" for="amenity_{{ $amenity->id }}">{{ $amenity->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('amenities')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror

                <button type="submit" class="btn btn-primary mt-4">Update Room</button>
            </form>
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