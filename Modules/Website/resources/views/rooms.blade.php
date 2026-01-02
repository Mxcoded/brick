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

            <!-- Rooms List -->
            @if ($rooms->isEmpty())
                <div class="text-center py-5">
                    <h3 class="text-muted">No rooms match your criteria.</h3>
                    <a href="{{ route('website.rooms.index') }}" class="btn btn-primary mt-3">View All Rooms</a>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($rooms as $room)
                        <div class="col-lg-6">
                            <div class="room-card card border-0 shadow-sm overflow-hidden h-100">
                                <div class="row g-0 h-100">
                                    <div class="col-md-6 position-relative">
                                        <img src="{{ Storage::url($room->image) }}"
                                            class="img-fluid h-100 w-100 object-fit-cover" alt="{{ $room->name }}"
                                            loading="lazy">
                                        <div class="price-badge position-absolute top-0 end-0 bg-primary text-white p-3">
                                            <span
                                                class="d-block fs-4 fw-bold">&#8358;{{ number_format($room->price_per_night) }}</span>
                                            <small class="d-block text-center">per night</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card-body p-4 d-flex flex-column h-100">
                                            <h2 class="h3 mb-3">{{ $room->name }}</h2>
                                            <p class="text-muted flex-grow-1">{{ Str::limit($room->description, 150) }}</p>

                                            @if (!empty($room->amenities))
                                                <div class="amenities mb-4">
                                                    <h5 class="h6 mb-3 text-primary">Key Amenities</h5>
                                                    <div class="room-features d-flex flex-wrap gap-2 mb-3">
                                                        @foreach ($room->amenities as $amenity)
                                                            <div class="col">
                                                                <div
                                                                    class="amenity-item d-flex align-items-center p-3 bg-light rounded">
                                                                    {{-- Use generic check icon since we only have string names --}}
                                                                    <i class="fas fa-check-circle text-primary me-3"></i>
                                                                    <span>{{ $amenity }}</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                                <a href="{{ route('website.rooms.show', $room->id) }}"
                                                    class="text-decoration-none text-primary">
                                                    View Details <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                                <a href="{{ route('website.booking', ['room_id' => $room->id]) }}"
                                                    class="btn btn-primary btn-sm">
                                                    Book Now
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
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
