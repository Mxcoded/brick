@extends('website::layouts.master')

@section('title', 'Facilities - Brickspoint ApartHotel')

@section('content')

<style>
    .facilities-hero {
        position: relative;
        height: 60vh;
        min-height: 420px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #1a1a1a;
    }
    .facilities-hero img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.4;
    }
    .facilities-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.75) 100%);
    }
    .facilities-hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        color: #fff;
        max-width: 780px;
        padding: 0 1.5rem;
    }
    .facilities-hero-content .overline {
        font-size: 0.8rem;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: #C8A165;
        margin-bottom: 1.25rem;
        display: block;
        font-weight: 500;
    }
    .facilities-hero-content h1 {
        font-size: clamp(2.2rem, 5vw, 3.8rem);
        font-weight: 600;
        letter-spacing: 1.5px;
        margin-bottom: 1.25rem;
        color: #ffffff;
        text-shadow: 0 2px 30px rgba(0,0,0,0.5), 0 1px 4px rgba(0,0,0,0.3);
    }
    .facilities-hero-content p {
        font-size: 1.1rem;
        opacity: 0.9;
        font-weight: 400;
        line-height: 1.8;
        text-shadow: 0 1px 12px rgba(0,0,0,0.3);
    }
    .section-intro {
        padding: 4rem 0;
        background: #fafaf8;
    }
    .section-intro .container {
        max-width: 1140px;
    }
    .intro-text {
        color: #3a3a3a;
        line-height: 1.9;
        font-size: 1rem;
        max-width: 800px;
        margin: 0 auto;
    }
    .section-facilities {
        padding: 5rem 0;
        background: #fff;
    }
    .section-facilities .container {
        max-width: 1140px;
    }
    .facility-card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        transition: all 0.35s ease;
        height: 100%;
        background: #fff;
        cursor: pointer;
    }
    .facility-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        transform: translateY(-4px);
    }
    .facility-card-img {
        height: 220px;
        overflow: hidden;
    }
    .facility-card-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .facility-card:hover .facility-card-img img {
        transform: scale(1.06);
    }
    .facility-card-body {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .facility-card-body .facility-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: rgba(200,161,101,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        flex-shrink: 0;
    }
    .facility-card-body .facility-icon i {
        font-size: 1.2rem;
        color: #C8A165;
    }
    .facility-card-body h3 {
        font-size: 1.15rem;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 0.6rem;
    }
    .facility-card-body p {
        font-size: 0.9rem;
        color: #5a5a5a;
        line-height: 1.7;
        flex: 1;
        margin-bottom: 1rem;
    }
    .facility-card-body .facility-link {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #C8A165;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: gap 0.3s ease;
    }
    .facility-card-body .facility-link:hover {
        gap: 0.8rem;
        color: #b08d55;
    }
    .section-heading {
        text-align: center;
        margin-bottom: 3rem;
    }
    .section-heading .overline {
        font-size: 0.75rem;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: #C8A165;
        display: block;
        margin-bottom: 0.75rem;
        font-weight: 600;
    }
    .section-heading h2 {
        font-size: 2rem;
        font-weight: 500;
        color: #C8A165;
        letter-spacing: 1px;
    }
    .fm-close {
        color: #C8A165;
        opacity: 0.8;
    }
    .fm-close:hover { opacity: 1; }
    .fm-header {
        border-bottom: 2px solid #C8A165;
        padding-bottom: 0.75rem;
        margin-bottom: 1.25rem;
    }
    .fm-img {
        width: 100%;
        max-height: 340px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e8ddd0;
    }
    .fm-content {
        color: #3a3a3a;
        line-height: 1.9;
    }
    .fm-content h2, .fm-content h3 {
        color: #C8A165;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
    .fm-content ul, .fm-content ol { padding-left: 1.5rem; margin-bottom: 1rem; }
    .fm-content img { max-width: 100%; border-radius: 8px; margin: 1.5rem 0; }
    .fm-feature-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.65rem 1rem;
        background: #fafaf8;
        border-radius: 6px;
        border-left: 3px solid #C8A165;
    }
    .fm-feature-item i { color: #C8A165; font-size: 0.9rem; width: 18px; text-align: center; }
    .fm-feature-item span { color: #3a3a3a; font-size: 0.9rem; }
    .fm-back {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: #C8A165;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.8rem;
        text-decoration: none;
        padding: 0.5rem 0;
    }
    .fm-back:hover { color: #b08d55; }

    @media (max-width: 991px) {
        .section-intro { padding: 3rem 0; }
        .section-facilities { padding: 3rem 0; }
    }
    @media (max-width: 576px) {
        .facilities-hero { min-height: 300px; height: 45vh; }
        .facilities-hero-content h1 { font-size: 1.8rem; }
        .facility-card-img { height: 180px; }
    }
</style>

{{-- ═══ HERO ═══ --}}
<section class="facilities-hero">
    @if($page->hero_image)
        <img src="{{ $page->hero_image }}" alt="{{ $page->hero_title ?? 'Facilities' }}">
    @else
        <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1400&q=80" alt="Facilities">
    @endif
    <div class="facilities-hero-overlay"></div>
    <div class="facilities-hero-content">
        @if($page->hero_subtitle)
            <span class="overline">{{ $page->hero_subtitle }}</span>
        @endif
        <h1>{{ $page->hero_title ?? 'Our Facilities' }}</h1>
    </div>
</section>

{{-- ═══ INTRO ═══ --}}
@if($page->intro_heading || $page->intro_description)
<section class="section-intro">
    <div class="container text-center">
        @if($page->intro_heading)
            <div class="section-heading mb-4">
                <span class="overline">{{ $page->intro_heading }}</span>
            </div>
        @endif
        @if($page->intro_description)
            <p class="intro-text">{{ $page->intro_description }}</p>
        @endif
    </div>
</section>
@endif

{{-- ═══ FACILITY ITEMS GRID ═══ --}}
@if($page->items->count())
<section class="section-facilities">
    <div class="container">
        <div class="row g-4">
            @foreach($page->items as $item)
            <div class="col-md-6 col-lg-4">
                <div class="facility-card" data-bs-toggle="modal" data-bs-target="#facilityModal{{ $item->id }}">
                    @if($item->image)
                    <div class="facility-card-img">
                        <img src="{{ $item->image }}" alt="{{ $item->title }}" loading="lazy">
                    </div>
                    @endif
                    <div class="facility-card-body">
                        @if($item->icon)
                        <div class="facility-icon">
                            <i class="{{ $item->icon }}"></i>
                        </div>
                        @endif
                        <h3>{{ $item->title }}</h3>
                        @if($item->description)
                        <p>{{ Str::limit($item->description, 120) }}</p>
                        @endif
                        <span class="facility-link">
                            View Details <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══ MODALS ═══ --}}
@if($page->items->count())
    @foreach($page->items as $item)
    <div class="modal fade" id="facilityModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-2 fm-header w-100">
                        @if($item->icon)
                            <i class="{{ $item->icon }}" style="color: #C8A165; font-size: 1.3rem;"></i>
                        @endif
                        <h5 class="modal-title fw-bold mb-0" style="color: #1a1a1a;">{{ $item->title }}</h5>
                    </div>
                    <button type="button" class="btn-close fm-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    @if($item->image)
                    <img src="{{ $item->image }}?t={{ time() }}" alt="{{ $item->title }}" class="fm-img mb-3">
                    @endif

                    @if($item->description)
                    <p class="text-muted mb-3" style="line-height: 1.8;">{{ $item->description }}</p>
                    @endif

                    @if($item->content)
                    <div class="fm-content mb-3">{!! $item->content !!}</div>
                    @endif

                    @if($item->features)
                    <div class="mt-3">
                        <h6 class="fw-bold mb-3" style="color: #C8A165; letter-spacing: 1px; text-transform: uppercase; font-size: 0.8rem;">
                            <i class="fas fa-list me-1"></i> Features
                        </h6>
                        <div class="d-flex flex-column gap-2">
                            @foreach($item->features as $feature)
                            <div class="fm-feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>{{ $feature }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="fm-back" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left"></i> Back to Facilities
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif

@endsection
