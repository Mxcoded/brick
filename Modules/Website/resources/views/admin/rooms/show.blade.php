@extends('website::layouts.admin')

@section('title', 'View Room')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h1 class="h3 mb-0">View Room</h1>
        </div>
        <div class="card-body p-4">
            <h2 class="mb-4 fw-bold">{{ $room->name }}</h2>
            <dl class="row mb-4">
                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">{{ $room->description ?? 'N/A' }}</dd>
                <dt class="col-sm-3">Price/Night</dt>
                <dd class="col-sm-9">{{ number_format($room->price, 2) }}</dd>
                <dt class="col-sm-3">Capacity</dt>
                <dd class="col-sm-9">{{ $room->capacity }}</dd>
                <dt class="col-sm-3">Size</dt>
                <dd class="col-sm-9">{{ $room->size ?? 'N/A' }}</dd>
                <dt class="col-sm-3">Featured</dt>
                <dd class="col-sm-9">
                    @if ($room->featured)
                        <span class="badge bg-success">Yes</span>
                    @else
                        <span class="badge bg-secondary">No</span>
                    @endif
                </dd>
            </dl>
            <h4 class="mt-4 mb-3">Amenities</h4>
            @if ($room->amenities->isEmpty())
                <p class="text-muted">None</p>
            @else
                <div class="row">
                    @foreach ($room->amenities as $amenity)
                        <div class="col-md-4 mb-2">
                            <div class="d-flex align-items-center">
                                <i class="{{ $amenity->icon ?? 'fas fa-question' }} me-2 text-primary"></i>
                                <span>{{ $amenity->name }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Primary Image -->
            <h4 class="mt-4 mb-3">Primary Image</h4>
            @if ($room->image)
                <img src="{{ Storage::url($room->image) }}" alt="Primary Image" class="img-fluid rounded shadow-sm mb-3" style="max-height: 300px;">
            @else
                <p class="text-muted">No primary image uploaded.</p>
            @endif

            <!-- Additional Images -->
            <h4 class="mt-4 mb-3">Additional Images</h4>
            @if ($room->images->isEmpty())
                <p class="text-muted">No additional images uploaded.</p>
            @else
                <div class="row">
                    @foreach ($room->images as $image)
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <img src="{{ Storage::url($image->path) }}" class="card-img-top" alt="Room Image" style="height: 200px; object-fit: cover;">
                                <div class="card-body text-center">
                                    <form action="{{ route('website.admin.rooms.images.destroy', ['room' => $room->id, 'image' => $image->id]) }}" method="POST" class="delete-image-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete Image</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Video -->
            <h4 class="mt-4 mb-3">Video</h4>
            @if ($room->video)
                <div class="mb-3">
                    <video src="{{ Storage::url($room->video) }}" controls class="img-fluid rounded shadow-sm" style="max-height: 300px;"></video>
                    <form action="{{ route('website.admin.rooms.video.destroy', $room) }}" method="POST" class="delete-video-form mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete Video</button>
                    </form>
                </div>
            @else
                <p class="text-muted">No video uploaded.</p>
            @endif

            <div class="mt-4">
                <a href="{{ route('website.admin.rooms.edit', $room) }}" class="btn btn-warning me-2">Edit</a>
                <a href="{{ route('website.admin.rooms.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.delete-image-form, .delete-video-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const type = this.classList.contains('delete-image-form') ? 'image' : 'video';
                if (confirm(`Are you sure you want to delete this ${type}?`)) {
                    this.submit();
                }
            });
        });
    </script>
@endpush