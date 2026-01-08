@extends('layouts.master')

@section('title', 'Room Details')

@section('page-content')
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm">
            <img src="{{ $room->image_url }}" class="card-img-top" alt="{{ $room->name }}" style="height: 250px; object-fit: cover;">
            <div class="card-body">
                <h3 class="card-title text-primary">{{ $room->name }}</h3>
                <h5 class="fw-bold text-success mb-3">₦{{ number_format($room->price, 2) }} <span class="text-muted small">/ night</span></h5>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Status:</span>
                    @if ($room->status === 'available')
                        <span class="badge bg-success">Available</span>
                    @elseif($room->status === 'maintenance')
                        <span class="badge bg-danger">Maintenance</span>
                    @else
                        <span class="badge bg-secondary">Booked</span>
                    @endif
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Capacity:</span>
                    <span class="fw-bold">{{ $room->capacity }} Guests</span>
                </div>

                 <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Bed Type:</span>
                    <span class="fw-bold">{{ $room->bed_type ?? 'N/A' }}</span>
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <a href="{{ route('website.admin.rooms.edit', $room->id) }}" class="btn btn-primary">Edit Room</a>
                    <a href="{{ route('website.admin.rooms.index') }}" class="btn btn-outline-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title fw-bold">Description</h5>
                <p class="text-muted">{{ $room->description }}</p>
                
                @if($room->video_url)
                    <div class="mt-3">
                        <a href="{{ $room->video_url }}" target="_blank" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-play me-1"></i> Watch Video Tour
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3">Amenities</h5>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($room->amenities as $amenity)
                        <span class="badge bg-light text-dark border p-2">
                            @if($amenity->icon) <i class="{{ $amenity->icon }} me-1"></i> @endif
                            {{ $amenity->name }}
                        </span>
                    @empty
                        <span class="text-muted small">No amenities listed.</span>
                    @endforelse
                </div>
            </div>
        </div>

        @if($room->images->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3">Photo Gallery</h5>
                <div class="row g-2">
                    @foreach($room->images as $img)
                        <div class="col-md-3 col-6">
                            <a href="{{ $img->image_url }}" target="_blank">
                                <img src="{{ $img->image_url }}" class="img-fluid rounded" style="height: 120px; width: 100%; object-fit: cover;">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection