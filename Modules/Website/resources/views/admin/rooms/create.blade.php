@extends('layouts.master')

@section('title', 'Add New Room')

@section('page-content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Add New Room Type</h4>

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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Room Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                        placeholder="e.g. Deluxe Suite" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Price per Night (₦) <span class="text-danger">*</span></label>
                                    <input type="number" name="price" class="form-control" value="{{ old('price') }}"
                                        required min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Capacity (Guests)</label>
                                    <input type="number" name="capacity" class="form-control"
                                        value="{{ old('capacity', 2) }}" min="1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Room Size</label>
                                    <input type="text" name="size" class="form-control" value="{{ old('size') }}"
                                        placeholder="e.g. 45 sqm">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Bed Type</label>
                                    <select name="bed_type" class="form-control">
                                        <option value="King Size">King Size</option>
                                        <option value="Queen Size">Queen Size</option>
                                        <option value="Twin Beds">Twin Beds</option>
                                        <option value="Double Bed">Double Bed</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="5" required>{{ old('description') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="available">Available (Online)</option>
                                        <option value="maintenance">Maintenance (Hidden)</option>
                                        <option value="booked">Fully Booked</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>YouTube Video URL (Optional)</label>
                                    <input type="url" name="video_url" class="form-control"
                                        value="{{ old('video_url') }}" placeholder="https://youtube.com/watch?v=...">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="d-block mb-2 font-weight-bold">Amenities</label>
                            <div class="row">
                                @forelse($amenities as $amenity)
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="amenities[]"
                                                    value="{{ $amenity->id }}">
                                                {{ $amenity->name }}
                                                @if ($amenity->icon)
                                                    <i class="{{ $amenity->icon }} ml-1"></i>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-muted">
                                        No amenities found. <a href="{{ route('website.admin.amenities.create') }}">Create
                                            some here</a>.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Primary Image <span class="text-danger">*</span></label>
                                    <input type="file" name="image" class="form-control" accept="image/*" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Gallery Images (Optional)</label>
                                    <input type="file" name="gallery_images[]" class="form-control" accept="image/*"
                                        multiple>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="is_featured" value="1">
                                Feature this room on Homepage
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Create Room</button>
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
