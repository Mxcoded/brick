@extends('website::layouts.master')

@section('title', 'Rooms & Suites')

@section('content')
    <section class="rooms-section py-5 py-lg-7 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h1 class="display-4 fw-bold mb-3">Our Rooms & Suites</h1>
                <p class="lead text-muted mx-auto" style="max-width: 700px;">
                    Experience unparalleled comfort in our meticulously designed accommodations,
                    each offering a perfect blend of luxury and functionality.
                </p>
            </div>

            <!-- Filters -->
            <div class="filters mb-5 bg-white p-4 rounded shadow-sm">
                <form action="{{ route('website.rooms.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="city" class="form-label">Location</label>
                        <select class="form-select" id="city" name="city">
                            <option value="">All Locations</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city }}" {{ ($selectedCity ?? request('city')) == $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="min_price" class="form-label">Min Price</label>
                        <input type="number" class="form-control" id="min_price" name="min_price" min="0"
                            step="100" value="{{ request('min_price') }}" placeholder="N0">
                    </div>
                    <div class="col-md-3">
                        <label for="max_price" class="form-label">Max Price</label>
                        <input type="number" class="form-control" id="max_price" name="max_price" min="0"
                            step="100" value="{{ request('max_price') }}" placeholder="N5000">
                    </div>
                    <div class="col-md-3">
                        <label for="guests" class="form-label">Guests</label>
                        <select class="form-select" id="guests" name="guests">
                            <option value="">Any</option>
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ request('guests') == $i ? 'selected' : '' }}>
                                    {{ $i }} Guest{{ $i > 1 ? 's' : '' }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sort" class="form-label">Sort By</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="">Default</option>
                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to
                                High</option>
                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High
                                to Low</option>
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="{{ route('website.rooms.index') }}" class="btn btn-outline-secondary ms-2">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Room Types List -->
            @if ($roomTypes->isEmpty())
                <div class="text-center py-5">
                    <h3 class="text-muted">No rooms match your criteria.</h3>
                    <a href="{{ route('website.rooms.index') }}" class="btn btn-primary mt-3">View All Rooms</a>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($roomTypes as $roomType)
                        @php
                            // Calculate real-time availability using unified service
                            $availabilityService = app(\App\Services\RoomAvailabilityService::class);
                            $today = \Carbon\Carbon::today();
                            $tomorrow = $today->copy()->addDay();
                            
                            $checkInDate = !empty($checkIn) ? $checkIn : $today->format('Y-m-d');
                            $checkOutDate = !empty($checkOut) ? $checkOut : $tomorrow->format('Y-m-d');
                            
                            $availability = $availabilityService->checkRoomTypeAvailability(
                                $roomType->id,
                                $checkInDate,
                                $checkOutDate
                            );
                            
                            $availableUnits = $availability['available_count'] ?? 0;
                            $isAvailable = $availability['available'];
                            $availabilityReason = $availability['reason'] ?? null;
                            $availabilityMessage = $availability['message'] ?? null;
                            $totalUnits = $roomType->units_count;
                        @endphp
                        <div class="col-lg-6">
                            <div class="room-card card border-0 shadow-sm overflow-hidden h-100">
                                <div class="row g-0 h-100">
                                    <div class="col-md-6 position-relative">
                                        <img src="{{ $roomType->image_url ?? 'https://via.placeholder.com/400x300' }}"
                                            class="img-fluid h-100 w-100 object-fit-cover" alt="{{ $roomType->name }}"
                                            loading="lazy">
                                        <div class="price-badge position-absolute top-0 end-0 btn-primary text-white p-3">
                                            <span
                                                class="d-block fs-4 fw-bold">&#8358;{{ number_format($roomType->price) }}</span>
                                            <small class="d-block text-center">per night</small>
                                        </div>
                                        {{-- Availability Badge --}}
                                        <div class="position-absolute bottom-0 start-0 m-2">
                                            @if ($isAvailable)
                                                <span class="badge bg-success py-2 px-3">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    {{ $availableUnits }}/{{ $totalUnits }} Available
                                                </span>
                                            @elseif ($availabilityReason === 'stop_sell')
                                                <span class="badge bg-secondary py-2 px-3">
                                                    <i class="fas fa-ban me-1"></i> Not for Sale
                                                </span>
                                            @elseif ($availabilityReason === 'closed_to_arrival')
                                                <span class="badge bg-warning py-2 px-3 text-dark">
                                                    <i class="fas fa-sign-in-alt me-1"></i> No Check-in
                                                </span>
                                            @elseif ($availabilityReason === 'min_stay')
                                                <span class="badge bg-warning py-2 px-3 text-dark">
                                                    <i class="fas fa-clock me-1"></i> Min Stay Required
                                                </span>
                                            @else
                                                <span class="badge bg-danger py-2 px-3">
                                                    <i class="fas fa-times-circle me-1"></i> Fully Booked
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card-body p-4 d-flex flex-column h-100">
                                            <div class="d-flex align-items-start justify-content-between mb-1">
                                                <h2 class="h3 mb-0">{{ $roomType->name }}</h2>
                                                @php $__prop = $allProperties->firstWhere('id', $roomType->property_id); @endphp
                                                @if ($__prop)
                                                <span class="badge bg-soft-neutral text-dark border ms-2" style="font-size: 0.7rem; white-space: nowrap;">
                                                    <i class="fas fa-location-dot me-1 text-primary"></i>{{ $__prop->name }}
                                                </span>
                                                @endif
                                            </div>
                                            <div class="d-flex gap-2 mb-2 text-muted small">
                                                <span><i class="fas fa-user-friends me-1"></i> {{ $roomType->capacity }} Guests</span>
                                                @if($roomType->bed_type)
                                                    <span><i class="fas fa-bed me-1"></i> {{ $roomType->bed_type }}</span>
                                                @endif
                                            </div>
                                            <p class="text-muted flex-grow-1">{{ Str::limit($roomType->description, 120) }}</p>

                                            <div class="room-features d-flex flex-wrap gap-2 mb-3">
                                                @foreach ($roomType->amenities->take(3) as $amenity)
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="{{ $amenity->icon ?? 'fas fa-check-circle' }} text-primary me-1"></i>
                                                        {{ $amenity->name }}
                                                    </span>
                                                @endforeach

                                                @if ($roomType->amenities->count() > 3)
                                                    <span class="badge bg-light text-muted border">
                                                        +{{ $roomType->amenities->count() - 3 }} more
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                                <a href="{{ route('website.rooms.show', $roomType->slug ?? $roomType->id) }}"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i> View Details
                                                </a>
                                                @if ($isAvailable)
                                                    <a href="{{ route('website.rooms.show', $roomType->slug ?? $roomType->id) }}"
                                                        class="btn btn-primary btn-sm">
                                                        <i class="fas fa-arrow-right me-1"></i> Select Room
                                                    </a>
                                                @else
                                                    <span class="btn btn-secondary btn-sm disabled" 
                                                          title="{{ $availabilityMessage }}">
                                                        <i class="fas fa-ban me-1"></i> Unavailable
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination Links -->
                <div class="d-flex justify-content-center mt-5">
                    {{ $roomTypes->links() }}
                </div>
            @endif

            <!-- CTA -->
            <div class="text-center mt-5">
                <a href="{{ route('website.amenities') }}" class="btn btn-outline-primary btn-lg px-5">
                    <i class="fas fa-spa me-2"></i> View All Amenities
                </a>
            </div>
        </div>
    </section>

    @push('styles')
        <style>
            .rooms-section {
                background-color: #f8f9fa;
            }

            .room-card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                border-radius: 10px;
                overflow: hidden;
            }

            .room-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
            }

            .price-badge {
                border-bottom-left-radius: 10px;
            }

            .object-fit-cover {
                object-fit: cover;
            }

            .amenities .badge {
                font-weight: normal;
                padding: 0.35em 0.65em;
            }

            .filters {
                border-radius: 10px;
            }

            /* Gold-themed Pagination */
            .pagination {
                gap: 0.25rem;
            }
            .pagination .page-link {
                color: #b8860b;
                border-color: #d4af37;
                border-radius: 5px;
                padding: 0.5rem 0.85rem;
                transition: all 0.3s ease;
            }
            .pagination .page-link:hover {
                background-color: #d4af37;
                border-color: #d4af37;
                color: #fff;
            }
            .pagination .page-item.active .page-link {
                background-color: #b8860b;
                border-color: #b8860b;
                color: #fff;
            }
            .pagination .page-item.disabled .page-link {
                color: #ccc;
                border-color: #e9e9e9;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Smooth scroll for internal links
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();
                        document.querySelector(this.getAttribute('href')).scrollIntoView({
                            behavior: 'smooth'
                        });
                    });
                });
            });
        </script>
    @endpush
@endsection
