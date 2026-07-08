@extends('website::layouts.master')

@section('title', 'On-site Restaurant - Brickspoint ApartHotel')

@section('content')

<style>
    /* ═══ HERO ═══ */
    .dining-hero {
        position: relative;
        height: 60vh;
        min-height: 420px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #1a1a1a;
    }
    .dining-hero img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.4;
    }
    .dining-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.75) 100%);
    }
    .dining-hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        color: #fff;
        max-width: 720px;
        padding: 0 1.5rem;
    }
    .dining-hero-content .overline {
        font-size: 0.8rem;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: #C8A165;
        margin-bottom: 1.25rem;
        display: block;
        font-weight: 500;
    }
    .dining-hero-content h1 {
        font-size: clamp(2.2rem, 5vw, 3.8rem);
        font-weight: 600;
        letter-spacing: 1.5px;
        margin-bottom: 1rem;
        color: #ffffff;
        text-shadow: 0 2px 30px rgba(0,0,0,0.5), 0 1px 4px rgba(0,0,0,0.3);
    }
    .dining-hero-content p {
        font-size: 1.1rem;
        opacity: 0.9;
        font-weight: 400;
        line-height: 1.8;
        text-shadow: 0 1px 12px rgba(0,0,0,0.3);
    }

    /* ═══ SECTION ═══ */
    .section-dining {
        padding: 5rem 0;
        background: #fafaf8;
    }
    .section-dining .container {
        max-width: 1140px;
    }

    /* ═══ CARDS ═══ */
    .dining-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        transition: all 0.35s ease;
    }
    .dining-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        transform: translateY(-3px);
    }
    .dining-card-img {
        height: 100%;
        min-height: 300px;
        overflow: hidden;
    }
    .dining-card-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .dining-card:hover .dining-card-img img {
        transform: scale(1.05);
    }
    .dining-card-body {
        padding: 2.5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .dining-card-body .cuisine-badge {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #C8A165;
        margin-bottom: 0.5rem;
    }
    .dining-card-body h3 {
        font-size: 1.6rem;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 0.75rem;
    }
    .dining-card-body .dining-desc {
        color: #5a5a5a;
        line-height: 1.8;
        font-size: 0.95rem;
        margin-bottom: 1.25rem;
    }
    .dining-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #ece9e2;
        margin-bottom: 1.5rem;
    }
    .dining-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.88rem;
        color: #5a5a5a;
    }
    .dining-meta-item i {
        color: #C8A165;
        width: 16px;
        text-align: center;
    }
    .btn-menu {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.75rem 2rem;
        background: #C8A165;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .btn-menu:hover {
        background: #b08d55;
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(200,161,101,0.3);
        color: #fff;
    }
    .btn-menu-outline {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.75rem 2rem;
        background: transparent;
        color: #C8A165;
        border: 1.5px solid #C8A165;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .btn-menu-outline:hover {
        background: #C8A165;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(200,161,101,0.2);
    }

    /* ═══ MENU MODAL ═══ */
    .menu-modal .modal-content {
        border: none;
        border-radius: 12px;
        overflow: hidden;
    }
    .menu-modal .modal-header {
        background: #1a1a1a;
        color: #fff;
        border-bottom: 2px solid #C8A165;
        padding: 1.25rem 1.5rem;
    }
    .menu-modal .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }
    .menu-modal .modal-body {
        padding: 0;
        background: #f8f8f8;
    }
    .menu-modal .modal-footer {
        border-top: 1px solid #ece9e2;
        padding: 0.75rem 1.5rem;
    }
    .menu-iframe {
        width: 100%;
        height: 75vh;
        border: none;
    }
    .menu-external-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #C8A165;
        font-weight: 600;
        text-decoration: none;
    }
    .menu-external-link:hover {
        color: #b08d55;
    }

    @media (max-width: 991px) {
        .dining-card-img { min-height: 220px; }
        .dining-card-body { padding: 1.5rem; }
        .section-dining { padding: 3rem 0; }
    }
    @media (max-width: 576px) {
        .dining-hero { min-height: 300px; height: 45vh; }
        .dining-hero-content h1 { font-size: 1.8rem; }
        .dining-card-body h3 { font-size: 1.3rem; }
        .dining-meta { gap: 1rem; }
    }
</style>

{{-- ═══ HERO ═══ --}}
<section class="dining-hero">
    <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1400&q=80" alt="On-site Restaurant">
    <div class="dining-hero-overlay"></div>
    <div class="dining-hero-content">
        <span class="overline">Brickspoint ApartHotel</span>
        <h1>On-site Restaurant</h1>
        <p>Experience exceptional dining without leaving the comfort of the hotel. From à la carte breakfasts to exquisite evening dinners.</p>
    </div>
</section>

{{-- ═══ DINING OPTIONS ═══ --}}
<section class="section-dining">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        @forelse($diningOptions as $dining)
        <div class="dining-card mb-4">
            <div class="row g-0">
                <div class="col-md-5">
                    <div class="dining-card-img">
                        @if($dining->image_url)
                            <img src="{{ $dining->image_url }}" alt="{{ $dining->name }}">
                        @else
                            <div class="bg-light h-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-utensils fa-3x text-muted opacity-25"></i>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="dining-card-body">
                        @if($dining->cuisine_type)
                            <span class="cuisine-badge">{{ $dining->cuisine_type }}</span>
                        @endif
                        <h3>{{ $dining->name }}</h3>
                        <p class="dining-desc">{{ $dining->description }}</p>

                        <div class="dining-meta">
                            @if($dining->opening_hours)
                                <span class="dining-meta-item">
                                    <i class="far fa-clock"></i> {{ $dining->opening_hours }}
                                </span>
                            @endif
                            @if($dining->dress_code)
                                <span class="dining-meta-item">
                                    <i class="fas fa-tshirt"></i> {{ $dining->dress_code }}
                                </span>
                            @endif
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            @if($dining->menu_pdf)
                                <button class="btn-menu" data-bs-toggle="modal" data-bs-target="#menuModal{{ $dining->id }}">
                                    <i class="fas fa-book-open"></i> View Menu
                                </button>
                            @endif
                            @if($dining->menu_link)
                                <a href="{{ $dining->menu_link }}" target="_blank" class="btn-menu-outline">
                                    <i class="fas fa-external-link-alt"></i> Online Menu
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ MENU MODAL ═══ --}}
        @if($dining->menu_pdf)
        <div class="modal fade menu-modal" id="menuModal{{ $dining->id }}" tabindex="-1" aria-labelledby="menuModalLabel{{ $dining->id }}" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="menuModalLabel{{ $dining->id }}">
                            <i class="fas fa-book-open me-2" style="color: #C8A165;"></i>{{ $dining->name }} — Menu
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <iframe src="{{ $dining->menu_pdf }}#view=FitH" class="menu-iframe" title="Menu PDF"></iframe>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i> Scroll or use zoom controls to browse the menu.
                        </small>
                        <a href="{{ $dining->menu_pdf }}" target="_blank" class="menu-external-link">
                            <i class="fas fa-external-link-alt"></i> Open in new tab
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach

        @if($diningOptions->isEmpty())
        <div class="text-center py-5">
            <div class="text-muted opacity-25 mb-3">
                <i class="fas fa-utensils fa-4x"></i>
            </div>
            <h3 class="fw-light">Coming Soon</h3>
            <p class="text-muted">We are currently updating our dining options.</p>
        </div>
        @endif
    </div>
</section>
@endsection
