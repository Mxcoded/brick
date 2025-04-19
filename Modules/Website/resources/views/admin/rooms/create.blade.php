@extends('website::layouts.admin')

@section('title', 'Create Room')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-0">Create Room</h1>
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
            <form action="{{ route('website.admin.rooms.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- Basic Info Section -->
                <h4 class="mt-4">Basic Information</h4>
                <div class="mb-3">
                    <label for="name" class="form-label">Room Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="price_per_night" class="form-label">Price per Night</label>
                    <input type="number" step="0.01" class="form-control @error('price_per_night') is-invalid @enderror" id="price_per_night" name="price_per_night" value="{{ old('price_per_night') }}" required>
                    @error('price_per_night')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="capacity" class="form-label">Capacity</label>
                    <input type="number" class="form-control @error('capacity') is-invalid @enderror" id="capacity" name="capacity" value="{{ old('capacity') }}" required>
                    @error('capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="size" class="form-label">Size (e.g., 30 sqm)</label>
                    <input type="text" class="form-control @error('size') is-invalid @enderror" id="size" name="size" value="{{ old('size') }}" required>
                    @error('size')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="featured" class="form-label">Featured</label>
                    <select class="form-control @error('featured') is-invalid @enderror" id="featured" name="featured">
                        <option value="1" {{ old('featured') == 1 ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('featured') == 0 ? 'selected' : '' }}>No</option>
                    </select>
                    @error('featured')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Media Upload Section -->
                <h4 class="mt-4">Media Uploads</h4>
                <div class="mb-3">
                    <label for="primary_image" class="form-label">Primary Image</label>
                    <input type="file" class="form-control @error('primary_image') is-invalid @enderror" id="primary_image" name="primary_image" accept="image/*" required>
                    @error('primary_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="primary_image_preview" class="mt-2"></div>
                </div>
                <div class="mb-3">
                    <label for="images" class="form-label">Additional Images</label>
                    <input type="file" class="form-control @error('images') is-invalid @enderror" id="images" name="images[]" accept="image/*" multiple>
                    @error('images')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="images_preview" class="mt-2 d-flex flex-wrap"></div>
                </div>
                <div class="mb-3">
                    <label for="video" class="form-label">Video (Optional)</label>
                    <input type="file" class="form-control @error('video') is-invalid @enderror" id="video" name="video" accept="video/*">
                    @error('video')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="video_preview" class="mt-2"></div>
                </div>

                <!-- Amenities Section -->
                <h4 class="mt-4">Amenities</h4>
                <div class="row">
                    @foreach (\Modules\Website\Models\Amenity::all() as $amenity)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="amenity_{{ $amenity->id }}" name="amenities[]" value="{{ $amenity->id }}" {{ in_array($amenity->id, old('amenities', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="amenity_{{ $amenity->id }}">{{ $amenity->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('amenities')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror

                <button type="submit" class="btn btn-primary mt-4">Create Room</button>
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

        // Video Preview (optional)
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