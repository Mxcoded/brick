@extends('website::layouts.master')

@section('title', 'Offers & Deals - Brickspoint ApartHotel')

@section('content')

<style>
    .offers-hero {
        position: relative;
        height: 55vh;
        min-height: 380px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #1a1a1a;
    }
    .offers-hero img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.4;
    }
    .offers-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.75) 100%);
    }
    .offers-hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        color: #fff;
        max-width: 780px;
        padding: 0 1.5rem;
    }
    .offers-hero-content .overline {
        font-size: 0.8rem;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: #C8A165;
        margin-bottom: 1.25rem;
        display: block;
        font-weight: 500;
    }
    .offers-hero-content h1 {
        font-size: clamp(2.2rem, 5vw, 3.8rem);
        font-weight: 600;
        letter-spacing: 1.5px;
        margin-bottom: 1.25rem;
        color: #ffffff;
        text-shadow: 0 2px 30px rgba(0,0,0,0.5), 0 1px 4px rgba(0,0,0,0.3);
    }
    .offers-hero-content p {
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
    .section-intro .container { max-width: 1140px; }
    .intro-text {
        color: #3a3a3a;
        line-height: 1.9;
        font-size: 1rem;
        max-width: 800px;
        margin: 0 auto;
    }
    .section-offers {
        padding: 5rem 0;
        background: #fff;
    }
    .section-offers .container { max-width: 1140px; }
    .offer-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        transition: all 0.35s ease;
        height: 100%;
        background: #fff;
        cursor: pointer;
    }
    .offer-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        transform: translateY(-4px);
    }
    .offer-card-img {
        height: 200px;
        overflow: hidden;
        position: relative;
    }
    .offer-card-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .offer-card:hover .offer-card-img img {
        transform: scale(1.06);
    }
    .offer-card-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: #C8A165;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 0.3rem 0.8rem;
        border-radius: 4px;
    }
    .offer-card-body {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .offer-card-body .offer-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(200,161,101,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.75rem;
        flex-shrink: 0;
    }
    .offer-card-body .offer-icon i {
        font-size: 1.1rem;
        color: #C8A165;
    }
    .offer-card-body h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 0.5rem;
    }
    .offer-card-body .offer-dates {
        font-size: 0.78rem;
        color: #C8A165;
        font-weight: 500;
        margin-bottom: 0.6rem;
    }
    .offer-card-body p {
        font-size: 0.88rem;
        color: #5a5a5a;
        line-height: 1.7;
        flex: 1;
        margin-bottom: 1rem;
    }
    .offer-card-body .offer-link {
        font-size: 0.78rem;
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
    .offer-card-body .offer-link:hover {
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
    .om-close { color: #C8A165; opacity: 0.8; }
    .om-close:hover { opacity: 1; }
    .om-header {
        border-bottom: 2px solid #C8A165;
        padding-bottom: 0.75rem;
        margin-bottom: 1.25rem;
    }
    .om-img {
        width: 100%;
        max-height: 320px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e8ddd0;
    }
    .om-content {
        color: #3a3a3a;
        line-height: 1.9;
    }
    .om-content h2, .om-content h3 { color: #C8A165; margin-top: 1.5rem; margin-bottom: 0.75rem; }
    .om-content ul, .om-content ol { padding-left: 1.5rem; margin-bottom: 1rem; }
    .om-content img { max-width: 100%; border-radius: 8px; margin: 1.5rem 0; }
    .om-feature-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.65rem 1rem;
        background: #fafaf8;
        border-radius: 6px;
        border-left: 3px solid #C8A165;
    }
    .om-feature-item i { color: #C8A165; font-size: 0.9rem; width: 18px; text-align: center; }
    .om-feature-item span { color: #3a3a3a; font-size: 0.9rem; }
    .om-terms {
        background: #f8f4ee;
        border-radius: 8px;
        padding: 1rem 1.25rem;
        font-size: 0.85rem;
        color: #6a5a48;
        line-height: 1.7;
    }
    .om-terms strong { color: #3d3229; }
    .om-back {
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
    .om-back:hover { color: #b08d55; }
    .offer-empty {
        text-align: center;
        padding: 5rem 0;
    }
    .offer-empty i {
        font-size: 3rem;
        color: #ddd5c8;
        margin-bottom: 1rem;
    }
    .offer-empty p {
        color: #8a7a6a;
        font-size: 1.05rem;
    }

    @media (max-width: 991px) {
        .section-intro { padding: 3rem 0; }
        .section-offers { padding: 3rem 0; }
    }
    @media (max-width: 576px) {
        .offers-hero { min-height: 280px; height: 40vh; }
        .offers-hero-content h1 { font-size: 1.8rem; }
        .offer-card-img { height: 160px; }
    }
</style>

{{-- ═══ HERO ═══ --}}
<section class="offers-hero">
    @if($page->hero_image)
        <img src="{{ $page->hero_image }}" alt="{{ $page->hero_title ?? 'Offers' }}">
    @else
        <img src="https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=1400&q=80" alt="Offers">
    @endif
    <div class="offers-hero-overlay"></div>
    <div class="offers-hero-content">
        @if($page->hero_subtitle)
            <span class="overline">{{ $page->hero_subtitle }}</span>
        @endif
        <h1>{{ $page->hero_title ?? 'Exclusive Offers' }}</h1>
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

{{-- ═══ OFFERS GRID ═══ --}}
@php $activeOffers = $page->offers->where('is_active', true); @endphp

@if($activeOffers->count())
<section class="section-offers">
    <div class="container">
        <div class="row g-4">
            @foreach($activeOffers as $offer)
            <div class="col-md-6 col-lg-4">
                <div class="offer-card" data-bs-toggle="modal" data-bs-target="#offerModal{{ $offer->id }}">
                    @if($offer->image)
                    <div class="offer-card-img">
                        <img src="{{ $offer->image }}" alt="{{ $offer->title }}" loading="lazy">
                        <span class="offer-card-badge">Offer</span>
                    </div>
                    @endif
                    <div class="offer-card-body">
                        @if($offer->icon)
                        <div class="offer-icon">
                            <i class="{{ $offer->icon }}"></i>
                        </div>
                        @endif
                        <h3>{{ $offer->title }}</h3>
                        @if($offer->valid_from)
                        <div class="offer-dates">
                            <i class="far fa-calendar-alt me-1"></i>
                            Valid {{ $offer->valid_from->format('M j, Y') }}
                            @if($offer->valid_to) - {{ $offer->valid_to->format('M j, Y') }} @endif
                        </div>
                        @endif
                        @if($offer->short_description)
                        <p>{{ Str::limit($offer->short_description, 120) }}</p>
                        @endif
                        <span class="offer-link">
                            View Details <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@else
<div class="offer-empty">
    <i class="fas fa-tag"></i>
    <p>No offers available at the moment. Check back soon!</p>
</div>
@endif

{{-- ═══ MODALS ═══ --}}
@if($activeOffers->count())
    @foreach($activeOffers as $offer)
    <div class="modal fade" id="offerModal{{ $offer->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-2 om-header w-100">
                        @if($offer->icon)
                            <i class="{{ $offer->icon }}" style="color: #C8A165; font-size: 1.3rem;"></i>
                        @endif
                        <h5 class="modal-title fw-bold mb-0" style="color: #1a1a1a;">{{ $offer->title }}</h5>
                    </div>
                    <button type="button" class="btn-close om-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    @if($offer->image)
                    <img src="{{ $offer->image }}?t={{ time() }}" alt="{{ $offer->title }}" class="om-img mb-3">
                    @endif

                    @if($offer->valid_from)
                    <div class="d-flex align-items-center gap-2 mb-3" style="color: #C8A165;">
                        <i class="far fa-calendar-alt"></i>
                        <span class="fw-semibold">
                            Valid {{ $offer->valid_from->format('M j, Y') }}
                            @if($offer->valid_to) - {{ $offer->valid_to->format('M j, Y') }} @endif
                        </span>
                    </div>
                    @endif

                    @if($offer->short_description)
                    <p class="text-muted mb-3" style="line-height: 1.8;">{{ $offer->short_description }}</p>
                    @endif

                    @if($offer->content)
                    <div class="om-content mb-3">{!! $offer->content !!}</div>
                    @endif

                    @if($offer->features)
                    <div class="mt-3">
                        <h6 class="fw-bold mb-3" style="color: #C8A165; letter-spacing: 1px; text-transform: uppercase; font-size: 0.8rem;">
                            <i class="fas fa-list me-1"></i> What's Included
                        </h6>
                        <div class="d-flex flex-column gap-2">
                            @foreach($offer->features as $feature)
                            <div class="om-feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>{{ $feature }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($offer->terms_conditions)
                    <div class="mt-3">
                        <h6 class="fw-bold mb-2" style="color: #C8A165; letter-spacing: 1px; text-transform: uppercase; font-size: 0.8rem;">
                            <i class="fas fa-file-alt me-1"></i> Terms & Conditions
                        </h6>
                        <div class="om-terms">{!! nl2br(e($offer->terms_conditions)) !!}</div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="om-back" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left"></i> Back to Offers
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif

@endsection
