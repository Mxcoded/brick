@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banquet.index') }}">Banquet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('banquet.lead-events.index') }}">Lead Events</a></li>
    <li class="breadcrumb-item active">{{ $event->title }}</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 banquet-theme">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-charcoal">
                <i class="fas fa-edit me-2 text-gold"></i>{{ $event->title }}
            </h1>
            <p class="text-muted mb-0">
                Slug: <code>{{ $event->slug }}</code>
                &middot; {{ $event->leads()->count() }} lead(s)
                &middot; <a href="{{ route('website.event-lead', $event->slug) }}" target="_blank" class="text-decoration-none">Preview <i class="fas fa-external-link-alt small"></i></a>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('banquet.lead-events.qrcode', $event->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-qrcode me-1"></i> QR Code
            </a>
            <a href="{{ route('banquet.lead-events.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <form action="{{ route('banquet.lead-events.update', $event->id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        {{-- === SECTION 1: EVENT DETAILS === --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <i class="fas fa-info-circle me-2 text-gold"></i>
                <span class="fw-bold">Event Details</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Event Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $event->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Event Date</label>
                        <input type="date" name="event_date" class="form-control @error('event_date') is-invalid @enderror"
                               value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}">
                        @error('event_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Organizer / Company</label>
                        <input type="text" name="organizer" class="form-control @error('organizer') is-invalid @enderror"
                               value="{{ old('organizer', $event->organizer) }}">
                        @error('organizer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Location</label>
                        <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                               value="{{ old('location', $event->location) }}">
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="2">{{ old('description', $event->description) }}</textarea>
                        <small class="text-muted">Shown in the hero section of the public page.</small>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" id="isActive"
                                   {{ $event->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Active (form accessible to attendees)</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- === SECTION 2: HERO SECTION === --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <i class="fas fa-image me-2 text-gold"></i>
                <span class="fw-bold">Hero Section</span>
                <span class="text-muted small ms-2">— The top banner of the public page</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hero Subtitle</label>
                        <input type="text" name="hero_subtitle" class="form-control @error('hero_subtitle') is-invalid @enderror"
                               value="{{ old('hero_subtitle', $event->hero_subtitle) }}"
                               placeholder="e.g. Brickspoint ApartHotel">
                        <small class="text-muted">Small text above the title.</small>
                        @error('hero_subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hero Background Image</label>
                        <input type="file" name="hero_image" class="form-control @error('hero_image') is-invalid @enderror" accept="image/*">
                        @error('hero_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if ($event->hero_image_url)
                            <div class="mt-2 d-flex align-items-center gap-2">
                                <img src="{{ $event->hero_image_url }}" alt="Current hero" style="height: 50px; border-radius: 4px; object-fit: cover;">
                                <small class="text-muted">Current image</small>
                                <div class="form-check form-switch ms-auto">
                                    <input type="checkbox" name="remove_hero_image" class="form-check-input" value="1" id="removeHero">
                                    <label class="form-check-label small" for="removeHero">Remove</label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- === SECTION 3: FORM CONTENT === --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <i class="fas fa-pencil-alt me-2 text-gold"></i>
                <span class="fw-bold">Form Content</span>
                <span class="text-muted small ms-2">— Text displayed above and after the form</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Form Heading</label>
                        <input type="text" name="form_heading" class="form-control @error('form_heading') is-invalid @enderror"
                               value="{{ old('form_heading', $event->form_heading) }}"
                               placeholder="Register Now">
                        <small class="text-muted">Leave empty for default: "Register Now"</small>
                        @error('form_heading')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Thank You Message</label>
                        <input type="text" name="thank_you_message" class="form-control @error('thank_you_message') is-invalid @enderror"
                               value="{{ old('thank_you_message', $event->thank_you_message) }}"
                               placeholder="Thank you for your interest...">
                        <small class="text-muted">Shown after form submission. Default provided if empty.</small>
                        @error('thank_you_message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Form Subtext</label>
                        <textarea name="form_subtext" class="form-control @error('form_subtext') is-invalid @enderror"
                                  rows="2" placeholder="Fill in your details and our team will reach out to you.">{{ old('form_subtext', $event->form_subtext) }}</textarea>
                        <small class="text-muted">Supporting text below the form heading.</small>
                        @error('form_subtext')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-gold px-5">
                <i class="fas fa-save me-1"></i> Save All Changes
            </button>
        </div>
    </form>
</div>

<style>
.banquet-theme .text-gold { color: #C8A165 !important; }
.btn-gold { background-color: #C8A165; border-color: #C8A165; color: #fff; }
.btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #fff; }
</style>
@endsection
