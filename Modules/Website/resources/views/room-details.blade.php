@extends('website::layouts.master')

@section('title', $room->name . ' - Room Details')

@section('content')
    <section class="room-details-section py-5 py-lg-7">
        <div class="container">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('website.home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('website.rooms') }}">Rooms & Suites</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $room->name }}</li>
                </ol>
            </nav>

            <!-- Room Header -->
            <div class="row mb-5">
                <div class="col-12">
                    <h1 class="display-4 fw-bold mb-3">{{ $room->name }}</h1>
                    <p class="lead text-muted">{{ $room->description }}</p>
                </div>
            </div>

            <!-- Image Gallery -->
            <div class="row mb-5">
                <div class="col-lg-8">
                    <div id="roomGallery" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @php
                                $images = $room->images ?? collect();
                                $mainImage = $room->image ? Storage::url($room->image) : asset('images/default-room.jpg');
                            @endphp

                            @if ($images->isNotEmpty())
                                @foreach ($images as $index => $image)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <a href="{{ Storage::url($image->path) }}" data-fancybox="gallery"
                                           data-caption="{{ $image->caption ?? $room->name }}">
                                            <img src="{{ Storage::url($image->path) }}"
                                                 class="d-block w-100 rounded shadow-lg"
                                                 alt="{{ $image->caption ?? $room->name }}"
                                                 style="max-height: 500px; object-fit: cover;" loading="lazy">
                                        </a>
                                        @if ($image->caption)
                                            <div class="carousel-caption d-none d-md-block">
                                                <p class="bg-dark bg-opacity-50 p-2 rounded">{{ $image->caption }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="carousel-item active">
                                    <a href="{{ $mainImage }}" data-fancybox="gallery"
                                       data-caption="{{ $room->name }}">
                                        <img src="{{ $mainImage }}" class="d-block w-100 rounded shadow-lg"
                                             alt="{{ $room->name }}" style="max-height: 500px; object-fit: cover;"
                                             loading="lazy">
                                    </a>
                                </div>
                            @endif
                        </div>
                        @if ($images->count() > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#roomGallery"
                                    data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#roomGallery"
                                    data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        @endif
                    </div>
                    <!-- Thumbnails -->
                    @if ($images->count() > 1)
                        <div class="thumbnail-gallery d-flex gap-3 overflow-auto mt-3">
                            @foreach ($images as $index => $image)
                                <div class="thumbnail rounded shadow-sm" data-bs-target="#roomGallery"
                                     data-bs-slide-to="{{ $index }}"
                                     style="width: 150px; height: 100px; cursor: pointer; flex-shrink: 0;">
                                    <img src="{{ Storage::url($image->path) }}"
                                         class="w-100 h-100 object-fit-cover"
                                         alt="{{ $image->caption ?? 'Thumbnail ' . ($index + 1) }}"
                                         loading="lazy">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-body p-4">
                            <h3 class="h4 fw-bold mb-3">Room Overview</h3>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-naira-sign text-primary me-2"></i>
                                    {{ number_format($room->price_per_night) }} / night</li>
                                <li class="mb-2"><i class="fas fa-ruler-combined text-primary me-2"></i>
                                    {{ $room->size ?? 'N/A' }} sq.ft</li>
                                <li class="mb-2"><i class="fas fa-user-friends text-primary me-2"></i> Up to
                                    {{ $room->capacity ?? 'N/A' }} Guests</li>
                            </ul>
                            <a href="{{ route('website.booking.form', ['room_id' => $room->id]) }}"
                               class="btn btn-primary w-100 mt-3">Book Now</a>
                            <a href="#availability-checker" class="btn btn-outline-primary w-100 mt-2">Check
                                Availability</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amenities -->
            <div class="row mb-5">
                <div class="col-12">
                    <h2 class="h3 fw-bold mb-4">Room Amenities</h2>
                    @if ($room->amenities->isEmpty())
                        <p class="text-muted">No amenities listed for this room yet.</p>
                    @else
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            @foreach ($room->amenities as $amenity)
                                <div class="col">
                                    <div class="amenity-item d-flex align-items-center p-3 bg-light rounded">
                                        <i class="{{ $amenity->icon ?? 'fas fa-check-circle' }} text-primary me-3"></i>
                                        <span>{{ $amenity->name }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Availability Checker -->
            <div class="row mb-5" id="availability-checker">
                <div class="col-12">
                    <h2 class="h3 fw-bold mb-4">Check Availability</h2>
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <form id="availabilityForm" class="row g-3">
                                <input type="hidden" name="room_id" value="{{ $room->id }}">
                                <div class="col-md-4">
                                    <label for="check_in" class="form-label">Check-In</label>
                                    <input type="date" class="form-control" id="check_in" name="check_in"
                                           min="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="check_out" class="form-label">Check-Out</label>
                                    <input type="date" class="form-control" id="check_out" name="check_out"
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" id="checkAvailabilityBtn" class="btn btn-primary w-100">Check
                                        Availability</button>
                                </div>
                            </form>
                            <div id="availabilityResult" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Rooms -->
            @if ($relatedRooms && $relatedRooms->isNotEmpty())
                <div class="row">
                    <div class="col-12">
                        <h2 class="h3 fw-bold mb-4">Explore Similar Rooms</h2>
                        <div class="row g-4">
                            @foreach ($relatedRooms as $relatedRoom)
                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <img src="{{ $relatedRoom->image ? Storage::url($relatedRoom->image) : asset('images/default-room.jpg') }}"
                                             class="card-img-top" alt="{{ $relatedRoom->name }}"
                                             style="height: 200px; object-fit: cover;" loading="lazy">
                                        <div class="card-body">
                                            <h4 class="h5">{{ $relatedRoom->name }}</h4>
                                            <p class="text-muted">{{ Str::limit($relatedRoom->description, 50) }}</p>
                                            <p class="fw-bold text-primary mb-0">
                                                {{ number_format($relatedRoom->price_per_night) }} / night</p>
                                        </div>
                                        <div class="card-footer bg-white border-0">
                                            <a href="{{ route('website.room.details', $relatedRoom->id) }}"
                                               class="btn btn-outline-primary w-100">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .room-details-section {
            background-color: #f8f9fa;
        }

        .carousel-item {
            transition: transform 0.6s ease;
        }

        .thumbnail {
            transition: opacity 0.3s ease;
        }

        .thumbnail:hover {
            opacity: 0.8;
        }

        .amenity-item {
            transition: all 0.3s ease;
        }

        .amenity-item:hover {
            background-color: #e9ecef;
            transform: translateY(-3px);
        }

        .thumbnail-gallery::-webkit-scrollbar {
            height: 5px;
        }

        .thumbnail-gallery::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .thumbnail-gallery::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .thumbnail-gallery::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Fancybox only if elements exist
            if (document.querySelector('[data-fancybox="gallery"]')) {
                Fancybox.bind('[data-fancybox="gallery"]', {
                    loop: true,
                    buttons: ["zoom", "share", "slideShow", "fullScreen", "download", "thumbs", "close"],
                    animationEffect: "zoom-in-out",
                    transitionEffect: "circular"
                });
            }

            // Dynamic check-out date adjustment
            const checkIn = document.getElementById('check_in');
            const checkOut = document.getElementById('check_out');
            if (checkIn && checkOut) {
                checkIn.addEventListener('change', function () {
                    if (this.value) {
                        const nextDay = new Date(this.value);
                        nextDay.setDate(nextDay.getDate() + 1);
                        const nextDayStr = nextDay.toISOString().split('T')[0];
                        checkOut.min = nextDayStr;
                        if (!checkOut.value || new Date(checkOut.value) <= new Date(nextDayStr)) {
                            checkOut.value = nextDayStr;
                        }
                    }
                });
            }

            // Availability checker
            const checkAvailabilityBtn = document.getElementById('checkAvailabilityBtn');
            const availabilityResult = document.getElementById('availabilityResult');
            const form = document.getElementById('availabilityForm');

            if (checkAvailabilityBtn && availabilityResult && form) {
                checkAvailabilityBtn.addEventListener('click', function () {
                    const formData = new FormData(form);
                    const checkInDate = formData.get('check_in');
                    const checkOutDate = formData.get('check_out');
                    const roomId = formData.get('room_id');

                    // Validate inputs
                    if (!roomId || !checkInDate || !checkOutDate) {
                        availabilityResult.innerHTML = `
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                Please ensure all fields (room ID, check-in, and check-out dates) are filled.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>`;
                        return;
                    }

                    // Loading state
                    checkAvailabilityBtn.disabled = true;
                    checkAvailabilityBtn.innerHTML = `
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Checking...`;

                    // Correctly construct the URL with the room ID
                    const url = "{{ route('website.room.checkAvailability', $room->id) }}" +
                                `?check_in=${encodeURIComponent(checkInDate)}&check_out=${encodeURIComponent(checkOutDate)}`;

                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(`Server responded with status ${response.status}: ${text}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (typeof data.available === 'boolean' && data.message) {
                            if (data.available) {
                                availabilityResult.innerHTML = `
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        ${data.message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        <div class="mt-3">
                                            <a href="{{ route('website.booking.form', ['room_id' => $room->id]) }}?check_in=${encodeURIComponent(checkInDate)}&check_out=${encodeURIComponent(checkOutDate)}"
                                               class="btn btn-success">
                                                Proceed to Book
                                            </a>
                                        </div>
                                    </div>`;
                            } else {
                                availabilityResult.innerHTML = `
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        ${data.message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>`;
                            }
                        } else {
                            throw new Error('Invalid response format from server');
                        }
                    })
                    .catch(error => {
                        availabilityResult.innerHTML = `
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Failed to check availability: ${error.message}. Please try again or contact support.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>`;
                        console.error('Availability Check Error:', error);
                    })
                    .finally(() => {
                        checkAvailabilityBtn.disabled = false;
                        checkAvailabilityBtn.innerHTML = 'Check Availability';
                    });
                });
            }
        });
    </script>
@endpush