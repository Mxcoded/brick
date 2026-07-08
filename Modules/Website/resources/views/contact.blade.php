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
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('website.contact.send') }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        
                        {{-- Time-based validation token (encrypted timestamp) --}}
                        <input type="hidden" name="_form_token" value="{{ encrypt(time()) }}">
                        
                        {{-- Honeypot field 1 - hidden from users, bots will fill it --}}
                        <div style="position: absolute; left: -9999px;" aria-hidden="true">
                            <label for="website_url">Website</label>
                            <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off">
                        </div>
                        
                        {{-- Honeypot field 2 - Secondary trap field --}}
                        <div style="position: absolute; left: -9999px; top: -9999px;" aria-hidden="true">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" name="phone_number" id="phone_number" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Please enter your name.</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" minlength="10" required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Please enter your message (minimum 10 characters).</div>
                            @enderror
                            <small class="text-muted">Minimum 10 characters</small>
                        </div>
                        
                        {{-- reCAPTCHA v3 hidden token --}}
                        @if(config('services.recaptcha.site_key'))
                            <input type="hidden" name="g-recaptcha-response" id="recaptcha-token">
                        @endif
                        
                        <button type="submit" class="btn btn-primary btn-lg px-5" id="submit-btn">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
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
                                    {{ $settings['address'] ?? '24 Jose Marti Crescent Asokoro, Abuja, Nigeria' }}<br>
                                    {{ $settings['address_2'] ?? '11 Adzope Crescent, Wuse II, Abuja, Nigeria' }}
                                </p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="me-3 text-primary">
                                <i class="fas fa-phone-alt fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="h5 mb-1">Phone</h4>
                                <p class="text-muted mb-0">
                                    {{ $settings['phone'] ?? '+234 809 999 9627' }}<br>
                                    {{ $settings['phone_2'] ?? '+234 809 999 9620' }}
                                </p>
                                <p class="text-muted mb-0"></p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="me-3 text-primary">
                                <i class="fas fa-envelope fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="h5 mb-1">Email</h4>
                                <p class="text-muted mb-0">
                                    {{ $settings['email'] ?? 'rsv@brickspoint.com '}}<br>
                                    {{ $settings['email_2'] ?? 'rsv@brickspoint.ng' }}
                                </p>
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
    <section class="py-5 py-lg-7 btn-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Plan Your Stay Today</h2>
            <p class="lead mb-5 mx-auto" style="max-width: 700px;">Reach out to us and let us make your visit unforgettable.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('website.book') }}" class="btn btn-light btn-lg px-5">Book Now</a>
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
    {{-- Google reCAPTCHA v3 (only if configured) --}}
    @if(config('services.recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    @endif
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form.needs-validation');
            const submitBtn = document.getElementById('submit-btn');
            
            // reCAPTCHA v3 token generation
            @if(config('services.recaptcha.site_key'))
            function getRecaptchaToken() {
                return new Promise((resolve, reject) => {
                    grecaptcha.ready(function() {
                        grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'contact_form'})
                            .then(function(token) {
                                resolve(token);
                            })
                            .catch(function(error) {
                                console.error('reCAPTCHA error:', error);
                                resolve(null); // Allow form submission even if reCAPTCHA fails
                            });
                    });
                });
            }
            @endif
            
            // Form validation with reCAPTCHA
            form.addEventListener('submit', async function(event) {
                // First check form validity
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }
                
                // Get reCAPTCHA token if configured
                @if(config('services.recaptcha.site_key'))
                event.preventDefault();
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
                
                try {
                    const token = await getRecaptchaToken();
                    if (token) {
                        document.getElementById('recaptcha-token').value = token;
                    }
                    form.submit();
                } catch (error) {
                    console.error('Error getting reCAPTCHA token:', error);
                    form.submit(); // Submit anyway if reCAPTCHA fails
                }
                @else
                form.classList.add('was-validated');
                @endif
            });

            // Smooth scroll for navigation
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>
@endpush
