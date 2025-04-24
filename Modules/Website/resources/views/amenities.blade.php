@extends('website::layouts.master')

@section('title', 'Amenities')

@section('content')
<section class="amenities-section py-5 py-lg-7">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h1 class="display-4 fw-bold mb-3">Our Amenities</h1>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">
                Discover the exceptional services and facilities that make your stay unforgettable.
            </p>
        </div>

        <div class="row g-4">
            <!-- Spa -->
            <div class="col-md-6 col-lg-4">
                <div class="amenity-card card border-0 shadow-sm h-100 overflow-hidden">
                    <img src="{{ asset('images/spa.jpg') }}" class="card-img-top" alt="Spa">
                    <div class="card-body">
                        <h3 class="h5 card-title">Luxury Spa</h3>
                        <p class="card-text text-muted">Rejuvenate with our range of treatments and therapies.</p>
                    </div>
                </div>
            </div>
            <!-- Fitness Center -->
            <div class="col-md-6 col-lg-4">
                <div class="amenity-card card border-0 shadow-sm h-100 overflow-hidden">
                    <img src="{{ asset('images/fitness.jpg') }}" class="card-img-top" alt="Fitness Center">
                    <div class="card-body">
                        <h3 class="h5 card-title">State-of-the-Art Fitness Center</h3>
                        <p class="card-text text-muted">Stay active with our modern equipment and personal trainers.</p>
                    </div>
                </div>
            </div>
            <!-- Pool -->
            <div class="col-md-6 col-lg-4">
                <div class="amenity-card card border-0 shadow-sm h-100 overflow-hidden">
                    <img src="{{ asset('images/pool.jpg') }}" class="card-img-top" alt="Pool">
                    <div class="card-body">
                        <h3 class="h5 card-title">Infinity Pool</h3>
                        <p class="card-text text-muted">Relax by our stunning infinity pool with panoramic views.</p>
                    </div>
                </div>
            </div>
            <!-- Add more amenities as needed -->
        </div>
    </div>
</section>
@endsection