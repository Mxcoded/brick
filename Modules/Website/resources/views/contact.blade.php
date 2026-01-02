@extends('website::layouts.master')

@section('title', 'Contact Us - Our Luxury Hotel')

@section('content')
    <!-- Hero Section -->
    <section class="hero-section position-relative overflow-hidden"
        style="background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('{{ asset('images/contact-hero.jpg') }}') center/cover no-repeat; height: 50vh;">
        <div class="container h-100 d-flex align-items-center justify-content-center">
            <div class="text-center text-white">
                <h1 class="display-4 fw-bold mb-3 animate__animated animate__fadeInDown">Get in Touch</h1>
                <p class="lead mb-0 animate__animated animate__fadeInUp animate__delay-1s">We’re here to assist you with any
                    questions or inquiries</p>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="py-5 py-lg-7">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h2 class="display-5 fw-bold mb-4">Send Us a Message</h2>
                    <p class="text-muted mb-4">Whether you have a question about your stay or need assistance, our team is
                        ready to help.</p>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('website.contact.submit') }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback">
                                Please enter your name.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            <div class="invalid-feedback">
                                Please enter your message.
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg px-5">Send Message</button>
                    </form>
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <h2 class="display-5 fw-bold mb-4">Contact Information</h2>
                    <div class="contact-info mb-4">
                        <div class="d-flex mb-3">
                            <div class="me-3 text-primary">
                                <i class="fas fa-map-marker-alt fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="h5 mb-1">Address</h4>
                                <p class="text-muted mb-0">
                                    {{ $settings['address'] ?? '24 Jose Marti Crescent Asokoro, Abuja, Nigeria' }}</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="me-3 text-primary">
                                <i class="fas fa-phone-alt fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="h5 mb-1">Phone</h4>
                                <p class="text-muted mb-0">{{ $settings['phone'] ?? '+234 809 999 9627' }}</p>
                                <p class="text-muted mb-0"></p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="me-3 text-primary">
                                <i class="fas fa-envelope fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="h5 mb-1">Email</h4>
                                <p class="text-muted mb-0">{{ $settings['email'] ?? 'rsv@brickspoint.com' }}</p>

                            </div>
                        </div>
                    </div>
                    <div class="map-container rounded overflow-hidden shadow-lg">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.245471836331!2d7.515760620783751!3d9.041358819331956!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x104e0be6749dba69%3A0x8be5a894805903b9!2s24%20Jose%20Marti%20St%2C%20Asokoro%2C%20Crescent%20900110%2C%20Federal%20Capital%20Territory!5e0!3m2!1sen!2sng!4v1743448389956!5m2!1sen!2sng"
                            width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-5 py-lg-7 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Plan Your Stay Today</h2>
            <p class="lead mb-5 mx-auto" style="max-width: 700px;">Reach out to us and let us make your visit unforgettable.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('website.booking.form') }}" class="btn btn-light btn-lg px-5">Book Now</a>
                <a href="{{ route('website.rooms.index') }}" class="btn btn-outline-light btn-lg px-5">Explore Rooms</a>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        .contact-info .d-flex {
            transition: all 0.3s ease;
        }

        .contact-info .d-flex:hover {
            transform: translateX(10px);
        }

        .map-container {
            transition: all 0.3s ease;
        }

        .map-container:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });

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
