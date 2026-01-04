@extends('website::layouts.master')

@section('title', $room->name . ' - Room Details')

@section('content')
    <section class="room-details-section py-5 py-lg-7">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('website.home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('website.rooms.index') }}">Rooms & Suites</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $room->name }}</li>
                </ol>
            </nav>

            <div class="row mb-5">
                <div class="col-12">
                    <h1 class="display-4 fw-bold mb-3">{{ $room->name }}</h1>
                    <p class="lead text-muted">{{ $room->description }}</p>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-lg-8">
                    @if ($room->video_url)
                        <div class="mb-4 ratio ratio-16x9 rounded shadow-lg overflow-hidden">
                            {{-- Check if it is a YouTube URL or local --}}
                            @if(Str::contains($room->video_url, 'youtube') || Str::contains($room->video_url, 'youtu.be'))
                                <iframe src="{{ str_replace('watch?v=', 'embed/', $room->video_url) }}" allowfullscreen></iframe>
                            @else
                                <video controls>
                                    <source src="{{ $room->video_url }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            @endif
                        </div>
                    @endif
                    
                    {{-- Main Image Fallback --}}
                    @if($room->image_url)
                         <img src="{{ $room->image_url }}" class="img-fluid rounded shadow-sm w-100" alt="{{ $room->name }}">
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="card shadow border-0 sticky-top" style="top: 100px; z-index: 10;">
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <span class="h2 fw-bold text-primary">₦{{ number_format($room->price, 2) }}</span>
                                <span class="text-muted">/ night</span>
                            </div>

                            <form id="checkAvailabilityForm" action="{{ route('website.room.checkAvailability') }}" method="POST">
                                @csrf
                                <input type="hidden" name="room_id" value="{{ $room->id }}">
                                
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-uppercase text-muted">Check In</label>
                                        <input type="date" name="check_in_date" id="check_in_date" class="form-control" required min="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-uppercase text-muted">Check Out</label>
                                        <input type="date" name="check_out_date" id="check_out_date" class="form-control" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    </div>
                                </div>

                                <button type="submit" id="checkBtn" class="btn btn-primary w-100 py-3 fw-bold">
                                    <span class="btn-text">Check Availability</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                                
                                <div id="availabilityResult" class="mt-3 text-center small fw-bold p-2 rounded d-none"></div>
                            </form>
                        </div>
                        <div class="card-footer bg-light p-3 text-center">
                            <small class="text-muted"><i class="fas fa-lock me-1"></i> Best Price Guaranteed</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap gap-3 mb-5 text-muted">
                        <div class="d-flex align-items-center bg-light px-3 py-2 rounded">
                            <i class="fas fa-user-friends me-2"></i> {{ $room->capacity }} Guests
                        </div>
                        <div class="d-flex align-items-center bg-light px-3 py-2 rounded">
                            <i class="fas fa-ruler-combined me-2"></i> {{ $room->size ?? 'N/A' }}
                        </div>
                        <div class="d-flex align-items-center bg-light px-3 py-2 rounded">
                            <i class="fas fa-bed me-2"></i> {{ $room->bed_type ?? 'King Bed' }}
                        </div>
                    </div>

                    <h3 class="h4 fw-bold mb-3">Description</h3>
                    <div class="mb-5">
                        {!! nl2br(e($room->description)) !!}
                    </div>

                    <h3 class="h4 fw-bold mb-4">Room Amenities</h3>
                    <div class="row row-cols-1 row-cols-md-2 g-3 mb-5">
                        @forelse(collect($room->amenities ?? []) as $amenity)
                            <div class="col">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle text-primary me-2"></i>
                                    <span>{{ is_string($amenity) ? $amenity : ($amenity['name'] ?? 'Amenity') }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No specific amenities listed.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            
            @if(isset($relatedRooms) && $relatedRooms->isNotEmpty())
            <hr class="my-5">
            <h3 class="fw-bold mb-4">You May Also Like</h3>
            <div class="row g-4">
                @foreach($relatedRooms as $related)
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            @if($related->image_url)
                                <img src="{{ $related->image_url }}" class="card-img-top" alt="{{ $related->name }}" style="height: 200px; object-fit: cover;">
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">{{ $related->name }}</h5>
                                <p class="card-text text-primary fw-bold">₦{{ number_format($related->price, 2) }}</p>
                                <a href="{{ route('website.rooms.show', $related->slug ?? $related->id) }}" class="btn btn-outline-primary btn-sm stretched-link">View Details</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('checkAvailabilityForm');
        
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('checkBtn');
            const btnText = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.spinner-border');
            const resultDiv = document.getElementById('availabilityResult');

            // 1. UI Reset
            btn.disabled = true;
            btnText.textContent = 'Checking...';
            spinner.classList.remove('d-none');
            
            // Hide result properly using classList
            resultDiv.classList.add('d-none');
            resultDiv.className = 'mt-3 text-center small fw-bold p-2 rounded d-none'; 

            // 2. Prepare Data
            const formData = new FormData(form);
            const jsonData = Object.fromEntries(formData.entries());

            // 3. Send Request
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
                // Check content type to prevent JSON parse error on 500 HTML response
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error("Server returned an invalid response.");
                }

                const data = await response.json();
                
                // Handle 422 Validation Errors
                if (response.status === 422) {
                    const firstError = data.errors ? Object.values(data.errors).flat()[0] : 'Invalid dates selected.';
                    throw new Error(firstError);
                }

                if (!response.ok) {
                    throw new Error(data.message || 'Something went wrong.');
                }

                return data;
            })
            .then(data => {
                // Show result div
                resultDiv.classList.remove('d-none');

                if (data.available) {
                    // SUCCESS: Room is free
                    resultDiv.classList.remove('bg-danger', 'text-danger', 'bg-warning', 'text-warning');
                    resultDiv.classList.add('bg-success', 'bg-opacity-10', 'text-success');
                    resultDiv.innerHTML = `<i class=\"fas fa-check-circle me-1\"></i> ${data.message} Redirecting...`;
                    
                    // Redirect to booking page
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1000);
                } else {
                    // FAIL: Room is occupied (Show smart suggestion)
                    resultDiv.classList.remove('bg-success', 'text-success', 'bg-warning', 'text-warning');
                    resultDiv.classList.add('bg-danger', 'bg-opacity-10', 'text-danger');
                    resultDiv.innerHTML = `<i class=\"fas fa-times-circle me-1\"></i> ${data.message}`;

                    // If suggestion exists, append a "Use Dates" button
                    if (data.suggestion) {
                        const suggestBtn = document.createElement('button');
                        suggestBtn.type = 'button';
                        suggestBtn.className = 'btn btn-sm btn-outline-dark mt-2 d-block mx-auto';
                        suggestBtn.innerHTML = 'Use Available Dates';
                        
                        // Click handler for suggestion
                        suggestBtn.onclick = function() {
                            document.getElementById('check_in_date').value = data.suggestion.check_in;
                            document.getElementById('check_out_date').value = data.suggestion.check_out;
                            // Trigger submit again automatically
                            form.requestSubmit(); 
                        };
                        resultDiv.appendChild(suggestBtn);
                    }
                    
                    // Reset button state
                    btn.disabled = false;
                    btnText.textContent = 'Check Availability';
                    spinner.classList.add('d-none');
                }
            })
            .catch(error => {
                // ERROR HANDLER
                console.error("Availability Check Error:", error);
                
                resultDiv.classList.remove('d-none');
                resultDiv.classList.remove('bg-success', 'text-success', 'bg-danger', 'text-danger');
                resultDiv.classList.add('bg-warning', 'bg-opacity-10', 'text-dark');
                resultDiv.innerHTML = `<i class=\"fas fa-exclamation-triangle me-1\"></i> ${error.message}`;
                
                btn.disabled = false;
                btnText.textContent = 'Check Availability';
                spinner.classList.add('d-none');
            });
        });
    });
</script>
@endpush