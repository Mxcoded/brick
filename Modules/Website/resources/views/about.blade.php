@extends('website::layouts.master')

@section('title', 'About Us - Our Luxury Hotel')

@section('content')
    <!-- Hero Section -->
    <section class="hero-section position-relative overflow-hidden" style="background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('{{ asset('images/about-hero.jpg') }}') center/cover no-repeat; height: 50vh;">
        <div class="container h-100 d-flex align-items-center justify-content-center">
            <div class="text-center text-white">
                <h1 class="display-4 fw-bold mb-3 animate__animated animate__fadeInDown">About Our Hotel</h1>
                <p class="lead mb-0 animate__animated animate__fadeInUp animate__delay-1s">Discover the story behind our commitment to luxury and hospitality</p>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="py-5 py-lg-7">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="ratio ratio-16x9 rounded overflow-hidden shadow-lg">
                        <img src="{{ asset('images/hotel-hero-2.png') }}" alt="Hotel History" class="img-fluid w-100 h-100 object-fit-cover">
                    </div>
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <h2 class="display-5 fw-bold mb-4">Our Story</h2>
                    <p class="lead mb-4">Established in the heart of Abuja, our hotel was born from a vision to redefine luxury hospitality in Nigeria.</p>
                    <p class="text-muted">With a commitment to excellence, we blend modern elegance with warm, personalized service. Our journey began with a single goal: to create unforgettable experiences for every guest, whether they’re here for business or leisure.</p>
                    <a href="{{ route('website.contact') }}" class="btn btn-primary btn-lg mt-3">Contact Us</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Values Section -->
    <section class="py-5 py-lg-7 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Our Mission & Values</h2>
                <p class="text-muted mx-auto" style="max-width: 700px;">We are dedicated to delivering exceptional experiences guided by our core values.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="value-card bg-white p-4 h-100 rounded shadow-sm text-center">
                        <div class="icon mb-3 text-primary">
                            <i class="fas fa-heart fa-3x"></i>
                        </div>
                        <h3 class="h5 mb-3">Hospitality</h3>
                        <p class="text-muted">Every guest is treated like family, with warmth and care at the heart of everything we do.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card bg-white p-4 h-100 rounded shadow-sm text-center">
                        <div class="icon mb-3 text-primary">
                            <i class="fas fa-star fa-3x"></i>
                        </div>
                        <h3 class="h5 mb-3">Excellence</h3>
                        <p class="text-muted">We strive for perfection in every detail, from service to amenities.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card bg-white p-4 h-100 rounded shadow-sm text-center">
                        <div class="icon mb-3 text-primary">
                            <i class="fas fa-globe fa-3x"></i>
                        </div>
                        <h3 class="h5 mb-3">Sustainability</h3>
                        <p class="text-muted">Committed to eco-friendly practices to preserve our environment for future generations.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-5 py-lg-7">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Meet Our Team</h2>
                <p class="text-muted mx-auto" style="max-width: 700px;">Our dedicated staff is here to ensure your stay is extraordinary.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="team-card bg-white p-4 h-100 rounded shadow-sm text-center">
                        <img src="{{ asset('images/team-member1.jpg') }}" class="rounded-circle mb-3" width="150" height="150" alt="Team Member">
                        <h3 class="h5 mb-2">{{$settings['gm_name']}}</h3>
                        <p class="text-muted mb-2">General Manager</p>
                        <p class="text-muted">With over 15 years in hospitality, Jane ensures every guest feels at home.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="team-card bg-white p-4 h-100 rounded shadow-sm text-center">
                        <img src="{{ asset('images/team-member2.jpg') }}" class="rounded-circle mb-3" width="150" height="150" alt="Team Member">
                        <h3 class="h5 mb-2">{{$settings['chef_name']}}</h3>
                        <p class="text-muted mb-2">Head Chef</p>
                        <p class="text-muted">John crafts culinary masterpieces inspired by local and international flavors.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="team-card bg-white p-4 h-100 rounded shadow-sm text-center">
                        <img src="{{ asset('images/team-member3.jpg') }}" class="rounded-circle mb-3" width="150" height="150" alt="Team Member">
                        <h3 class="h5 mb-2">{{$settings['spa_director']}}</h3>
                        <p class="text-muted mb-2">Spa Director</p>
                        <p class="text-muted">Emily creates serene experiences with her expertise in wellness therapies.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-5 py-lg-7 btn-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Experience Our Hospitality</h2>
            <p class="lead mb-5 mx-auto" style="max-width: 700px;">Book your stay today and become part of our story.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('website.booking') }}" class="btn btn-light btn-lg px-5">Book Now</a>
                <a href="{{ route('website.contact') }}" class="btn btn-outline-light btn-lg px-5">Get in Touch</a>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .value-card, .team-card {
            transition: all 0.3s ease;
        }
        .value-card:hover, .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .icon {
            transition: transform 0.3s ease;
        }
        .value-card:hover .icon {
            transform: scale(1.2);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll for navigation
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