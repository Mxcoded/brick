@extends('website::layouts.master')

@section('title', $event->title.' - Register Interest - Brickspoint ApartHotel')

@section('content')
<style>
    .lead-hero {
        position: relative;
        height: 45vh;
        min-height: 320px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #1a1a1a;
    }
    .lead-hero .hero-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.4;
    }
    .lead-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.75) 100%);
    }
    .lead-hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        color: #fff;
        max-width: 750px;
        padding: 0 1.5rem;
    }
    .lead-hero-content .hero-subtitle {
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 3px;
        opacity: 0.8;
        margin-bottom: 0.75rem;
    }
    .lead-hero-content h1 {
        color: #fff;
        font-size: clamp(2rem, 4vw, 3.2rem);
        font-weight: 600;
        letter-spacing: 1.5px;
        margin-bottom: 1rem;
        text-shadow: 0 2px 30px rgba(0,0,0,0.5);
    }
    .lead-hero-content p,
    .lead-hero-content span {
        color: #fff;
    }
    .lead-hero-content .hero-description {
        font-size: 1.05rem;
        opacity: 0.9;
        line-height: 1.8;
        text-shadow: 0 1px 12px rgba(0,0,0,0.3);
    }
    .hero-meta span {
        color: #fff;
        opacity: 0.85;
        font-size: 0.95rem;
    }
    .hero-meta i {
        color: #C8A165;
    }
    .lead-section {
        padding: 4rem 0;
        background: #fafaf8;
    }
    .lead-form-card {
        max-width: 640px;
        margin: 0 auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 30px rgba(0,0,0,0.06);
        padding: 2.5rem;
    }
    .lead-form-card h2 {
        color: #C8A165;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .form-control:focus {
        border-color: #C8A165;
        box-shadow: 0 0 0 0.2rem rgba(200,161,101,0.2);
    }
    .btn-gold {
        background-color: #C8A165;
        border-color: #C8A165;
        color: #fff;
        padding: 0.6rem 2rem;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        font-size: 0.85rem;
    }
    .btn-gold:hover {
        background-color: #b08d55;
        border-color: #b08d55;
        color: #fff;
    }
</style>

<section class="lead-hero">
    @if ($event->hero_image_url)
        <img class="hero-bg" src="{{ $event->hero_image_url }}" alt="{{ $event->title }}">
    @else
        <img class="hero-bg" src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1400&q=80" alt="Events">
    @endif
    <div class="lead-hero-overlay"></div>
    <div class="lead-hero-content">
        @if ($event->hero_subtitle)
            <div class="hero-subtitle">{{ $event->hero_subtitle }}</div>
        @endif
        <h1>{{ $event->title }}</h1>
        @if ($event->description)
            <p class="hero-description">{{ $event->description }}</p>
        @else
            <p class="hero-description">Register your interest for this event. Fill in your details below.</p>
        @endif
        @if ($event->event_date || $event->location)
            <div class="hero-meta mt-3 d-flex justify-content-center gap-4 flex-wrap">
                @if ($event->event_date)
                    <span><i class="fas fa-calendar-alt me-2"></i>{{ $event->event_date->format('F d, Y') }}</span>
                @endif
                @if ($event->location)
                    <span><i class="fas fa-map-marker-alt me-2"></i>{{ $event->location }}</span>
                @endif
            </div>
        @endif
    </div>
</section>

<section class="lead-section">
    <div class="container">
        <div class="lead-form-card">
            <h2>{{ $event->getFormHeadingOrDefault() }}</h2>
            @if ($event->form_subtext)
                <p class="text-muted mb-4">{{ $event->form_subtext }}</p>
            @else
                <p class="text-muted mb-4">Fill in your details and our team will reach out to you.</p>
            @endif

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm" style="border-left: 4px solid #C8A165;">
                    <i class="fas fa-check-circle me-2" style="color: #C8A165;"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm" style="border-left: 4px solid #dc3545;">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('website.event-lead.store', $event->slug) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-uppercase text-secondary">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control form-control-lg" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-uppercase text-secondary">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control form-control-lg" value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-uppercase text-secondary">Phone <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control form-control-lg" value="{{ old('phone') }}" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold small text-uppercase text-secondary">Company <span class="text-muted small text-lowercase">(optional)</span></label>
                    <input type="text" name="company" class="form-control form-control-lg" value="{{ old('company') }}">
                </div>
                <button type="submit" class="btn btn-gold w-100">
                    <i class="fas fa-paper-plane me-1"></i> Submit
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
