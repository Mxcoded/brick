@extends('website::layouts.master')

@section('title', $room->name . ' - Room Details')

@section('content')
    <section class="room-details-section py-5 py-lg-7">
        <div class="container">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('website.home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('website.rooms.index') }}">Rooms & Suites</a></li>
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
                    @if ($room->video)
                        <div class="mb-4">
                            <video class="w-100 rounded shadow-lg" controls>
                                <source src="{{ Storage::url($room->video) }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    @endif
                    <div id="roomGallery" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @php
                                $images = $room->images ?? collect();
                                $mainImage = $room->image
                                    ? Storage::url($room->image)
                                    : asset('images/default-room.jpg');
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
                                    <img src="{{ Storage::url($image->path) }}" class="w-100 h-100 object-fit-cover"
                                        alt="{{ $image->caption ?? 'Thumbnail ' . ($index + 1) }}" loading="lazy">
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
                            <a href="{{ route('website.booking', ['room_id' => $room->id]) }}"
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
                    @if (empty($room->amenities))
                        <p class="text-muted">No amenities listed for this room yet.</p>
                    @else
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            @foreach ($room->amenities as $amenity)
                                <div class="col">
                                    <div class="amenity-item d-flex align-items-center p-3 bg-light rounded">
                                        {{-- Use generic check icon since we only have string names --}}
                                        <i class="fas fa-check-circle text-primary me-3"></i>
                                        <span>{{ $amenity }}</span>
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
                            <form id="checkAvailabilityForm" action="{{ route('website.room.checkAvailability') }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="room_id" value="{{ $room->id }}">

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small text-uppercase fw-bold">Check In</label>
                                        <input type="date" name="check_in_date" id="check_in_date"
                                            class="form-control" required min="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small text-uppercase fw-bold">Check Out</label>
                                        <input type="date" name="check_out_date" id="check_out_date"
                                            class="form-control" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    </div>
                                </div>

                                <button type="submit" id="checkBtn" class="btn btn-primary w-100 mt-3">
                                    <span class="btn-text">Check Availability</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"
                                        aria-hidden="true"></span>
                                </button>

                                <div id="availabilityResult" class="mt-3 text-center small fw-bold"></div>
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
                                            <a href="{{ route('website.rooms.show', $relatedRoom->id) }}"
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
        document.addEventListener('DOMContentLoaded', function() {
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
                        checkIn.addEventListener('change', function() {
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


                    document.getElementById('checkAvailabilityForm').addEventListener('submit', function(e) {
                            e.preventDefault();

                            const form = this;
                            const btn = document.getElementById('checkBtn');
                            const btnText = btn.querySelector('.btn-text');
                            const spinner = btn.querySelector('.spinner-border');
                            const resultDiv = document.getElementById('availabilityResult');

                            // UI: Loading State
                            btn.disabled = true;
                            btnText.textContent = 'Checking...';
                            spinner.classList.remove('d-none');
                            resultDiv.innerHTML = '';
                            resultDiv.className = 'mt-3 text-center small fw-bold';

                            // 1. Gather Data automatically from form inputs
                            const formData = new FormData(form);
                            const jsonData = Object.fromEntries(formData.entries());

                            // 2. Send Request
                            fetch(form.action, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify(jsonData)
                                })
                                .then(async response => {
                                    const data = await response.json();

                                    // Handle Validation Errors (422)
                                    if (response.status === 422) {
                                        let errorMsg = 'Please check your dates.';
                                        if (data.errors) {
                                            // Grab the first error message available
                                            errorMsg = Object.values(data.errors).flat()[0];
                                        }
                                        throw new Error(errorMsg);
                                    }

                                    // Handle Server Errors (500, etc)
                                    if (!response.ok) {
                                        throw new Error(data.message || 'Server error occurred.');
                                    }

                                    return data;
                                })
                                .then(data => {
                                        // Success Logic
                                        if (data.available) {
                                            resultDiv.innerHTML =
                                                '<span class="text-success"><i class="fas fa-check-circle"></i> Room is available! Redirecting...</span>';
                                            window.location.href = data.redirect_url;
                                        } else {
                                            // Show the smart message
                                            resultDiv.innerHTML =
                                                '<span class="text-danger"><i class="fas fa-calendar-times"></i> ' +
                                                data.message + '</span>';

                                            // Auto-fill suggestion if available
                                            if (data.suggestion) {
                                                // Optional: Add a button to apply the suggestion
                                                const applyBtn = document.createElement('button');
                                                applyBtn.className = 'btn btn-sm btn-outline-dark mt-2 d-block mx-auto';
                                                applyBtn.innerText = 'Update to Available Dates';
                                                applyBtn.onclick = function() {
                                                    document.getElementById('check_in_date').value = data.suggestion
                                                        .check_in;
                                                    document.getElementById('check_out_date').value = data
                                                        .suggestion.check_out;
                                                    document.getElementById('checkBtn')
                                                .click(); // Re-check immediately
                                                };
                                                resultDiv.appendChild(applyBtn);
                                            }

                                            btn.disabled = false;
                                            btnText.textContent = 'Check Availability';
                                            spinner.classList.add('d-none');
                                        });
                                });
                    });
    </script>
@endpush
