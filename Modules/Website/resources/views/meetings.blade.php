@extends('website::layouts.master')

@section('title', $page->seo_title ?? 'Meetings & Events Space - Brickspoint ApartHotel')

@section('meta')
    @if($page->seo_description)
        <meta name="description" content="{{ $page->seo_description }}">
    @endif
@endsection

@section('content')

<style>
    /* ═══ HERO ═══ */
    .meetings-hero {
        position: relative;
        height: 65vh;
        min-height: 480px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #1a1a1a;
    }
    .meetings-hero img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.4;
    }
    .meetings-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.75) 100%);
    }
    .meetings-hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        color: #fff;
        max-width: 800px;
        padding: 0 1.5rem;
    }
    .meetings-hero-content .overline {
        font-size: 0.8rem;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: #C8A165;
        margin-bottom: 1.25rem;
        display: block;
        font-weight: 500;
    }
    .meetings-hero-content h1 {
        font-size: clamp(2.2rem, 5vw, 3.8rem);
        font-weight: 600;
        letter-spacing: 1.5px;
        margin-bottom: 1.25rem;
        color: #ffffff;
        text-shadow: 0 2px 30px rgba(0,0,0,0.5), 0 1px 4px rgba(0,0,0,0.3);
    }
    .meetings-hero-content p {
        font-size: 1.1rem;
        opacity: 0.9;
        font-weight: 400;
        line-height: 1.8;
        text-shadow: 0 1px 12px rgba(0,0,0,0.3);
    }
    .meetings-hero-content .btn-outline-light {
        border-color: rgba(255,255,255,0.5);
        border-width: 2px;
        letter-spacing: 1.5px;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.85rem 2.8rem;
        text-transform: uppercase;
        transition: all 0.3s ease;
        border-radius: 4px;
    }
    .meetings-hero-content .btn-outline-light:hover {
        background: #C8A165;
        border-color: #C8A165;
        color: #fff;
    }

    /* ═══ SECTIONS ═══ */
    .section-meetings {
        padding: 5rem 0;
        background: #fafaf8;
    }
    .section-meetings .container {
        max-width: 1140px;
    }
    .section-meetings-alt {
        padding: 5rem 0;
        background: #fff;
    }
    .section-meetings-alt .container {
        max-width: 1140px;
    }

    /* ═══ STATS ═══ */
    .stat-card {
        text-align: center;
        padding: 2.5rem 1rem;
        position: relative;
    }
    .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 40px;
        height: 3px;
        background: #C8A165;
        border-radius: 2px;
    }
    .stat-card .stat-number {
        font-size: 2.8rem;
        font-weight: 600;
        color: #C8A165;
        line-height: 1.2;
    }
    .stat-card .stat-number small {
        font-size: 1.2rem;
        font-weight: 400;
    }
    .stat-card .stat-label {
        font-size: 0.9rem;
        color: #4a4a4a;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-top: 0.75rem;
        font-weight: 500;
    }

    /* ═══ SECTION HEADINGS ═══ */
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

    /* ═══ CAPACITY TABLE ═══ */
    .capacity-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    }
    .capacity-table thead th {
        background: #1a1a1a;
        color: #fff;
        padding: 0.85rem 1rem;
        font-weight: 600;
        text-align: center;
        letter-spacing: 0.5px;
        font-size: 0.78rem;
        text-transform: uppercase;
    }
    .capacity-table thead th:first-child {
        text-align: left;
    }
    .capacity-table tbody td {
        padding: 0.8rem 1rem;
        text-align: center;
        border-bottom: 1px solid #ece9e2;
        color: #3a3a3a;
    }
    .capacity-table tbody td:first-child {
        text-align: left;
        font-weight: 600;
        color: #1a1a1a;
    }
    .capacity-table tbody tr:hover {
        background: rgba(200,161,101,0.06);
    }

    /* ═══ GALLERY ═══ */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.25rem;
    }
    .gallery-grid-item {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
        cursor: pointer;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: box-shadow 0.3s ease;
    }
    .gallery-grid-item:hover {
        box-shadow: 0 6px 24px rgba(0,0,0,0.15);
    }
    .gallery-grid-item img {
        width: 100%;
        height: 240px;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .gallery-grid-item:hover img {
        transform: scale(1.06);
    }

    /* ═══ EQUIPMENT LIST ═══ */
    .equipment-list {
        list-style: none;
        padding: 0;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 0.85rem;
    }
    .equipment-list li {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.9rem 1.1rem;
        background: #fff;
        border: 1px solid #e8e3db;
        border-radius: 6px;
        font-size: 0.95rem;
        color: #2c2c2c;
        font-weight: 450;
        transition: all 0.25s ease;
        box-shadow: 0 1px 4px rgba(0,0,0,0.03);
    }
    .equipment-list li:hover {
        border-color: #C8A165;
        box-shadow: 0 3px 12px rgba(200,161,101,0.12);
        transform: translateY(-1px);
    }
    .equipment-list li i {
        color: #C8A165;
        font-size: 1rem;
    }

    /* ═══ CATERING ═══ */
    .catering-text {
        color: #3a3a3a;
        line-height: 1.9;
        font-size: 1rem;
    }
    .catering-img {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: box-shadow 0.3s ease;
    }
    .catering-img:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.14);
    }
    .catering-img img {
        width: 100%;
        height: 240px;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .catering-img:hover img {
        transform: scale(1.04);
    }

    /* ═══ CTA ═══ */
    .cta-section {
        background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
        padding: 5rem 0;
        text-align: center;
        color: #fff;
    }
    .cta-section h2 {
        font-size: 2rem;
        font-weight: 600;
        letter-spacing: 1px;
        margin-bottom: 1rem;
        color: #ffffff;
        text-shadow: 0 2px 20px rgba(0,0,0,0.3);
    }
    .cta-section p {
        opacity: 0.75;
        font-size: 1rem;
        margin-bottom: 2.5rem;
        letter-spacing: 0.3px;
    }
    .cta-section .btn-gold {
        background: #C8A165;
        color: #fff;
        border: none;
        padding: 0.95rem 3.2rem;
        font-size: 0.85rem;
        font-weight: 600;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        border-radius: 4px;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }
    .cta-section .btn-gold:hover {
        background: #b08d55;
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(200,161,101,0.35);
        color: #fff;
    }

    .brochure-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #6a6a6a;
        font-size: 0.85rem;
        font-weight: 500;
        text-decoration: none;
        padding: 0.5rem 1.2rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        transition: all 0.25s ease;
    }
    .brochure-link:hover {
        border-color: #C8A165;
        color: #C8A165;
        background: rgba(200,161,101,0.04);
    }

    @media (max-width: 991px) {
        .section-meetings, .section-meetings-alt { padding: 3.5rem 0; }
        .gallery-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
    }
    @media (max-width: 576px) {
        .meetings-hero { min-height: 360px; height: 50vh; }
        .meetings-hero-content h1 { font-size: 1.8rem; }
        .section-heading h2 { font-size: 1.5rem; }
        .cta-section h2 { font-size: 1.4rem; }
    }
</style>

{{-- ═══ HERO ═══ --}}
<section class="meetings-hero">
    @if($page->hero_image)
        <img src="{{ $page->hero_image }}" alt="{{ $page->hero_title ?? 'Meetings & Events' }}">
    @else
        <img src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?w=1400&q=80" alt="Meeting space">
    @endif
    <div class="meetings-hero-overlay"></div>
    <div class="meetings-hero-content">
        @if($page->hero_subtitle)
            <span class="overline">{{ $page->hero_subtitle }}</span>
        @endif
        <h1>{{ $page->hero_title ?? 'Meetings & Events Space' }}</h1>
        @if($page->hero_description)
            <p>{{ $page->hero_description }}</p>
        @endif
        <a href="{{ route('website.meeting-enquiry') }}" class="btn btn-outline-light mt-3">
            Request A Quote <i class="fas fa-arrow-right ms-2"></i>
        </a>
    </div>
</section>

{{-- ═══ STATS ═══ --}}
@php $stats = $page->stats ?? []; @endphp
@if(!empty($stats['meeting_rooms']) || !empty($stats['total_sqm']) || !empty($stats['total_capacity']))
<section class="section-meetings">
    <div class="container">
        <div class="row justify-content-center">
            @if(!empty($stats['meeting_rooms']))
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number">{{ $stats['meeting_rooms'] }}</div>
                    <div class="stat-label">Meeting Rooms</div>
                </div>
            </div>
            @endif
            @if(!empty($stats['total_sqm']))
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number">{{ number_format($stats['total_sqm']) }}<small> m²</small></div>
                    <div class="stat-label">Total Meeting Space</div>
                </div>
            </div>
            @endif
            @if(!empty($stats['total_capacity']))
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number">{{ number_format($stats['total_capacity']) }}</div>
                    <div class="stat-label">Total Capacity</div>
                </div>
            </div>
            @endif
        </div>
        @if($page->brochure_pdf)
        <div class="text-center mt-4">
            <a href="{{ $page->brochure_pdf }}" target="_blank" class="brochure-link">
                <i class="fas fa-file-pdf"></i> Download Brochure PDF
            </a>
        </div>
        @endif
    </div>
</section>
@endif

{{-- ═══ SEATING CAPACITY CHART ═══ --}}
@if($page->rooms->count())
<section class="section-meetings-alt">
    <div class="container">
        <div class="section-heading">
            <span class="overline">Seating Capacity</span>
            <h2>Seating Capacity Chart</h2>
        </div>
        <div class="table-responsive">
            <table class="capacity-table">
                <thead>
                    <tr>
                        <th>Meeting Room</th>
                        <th>Size (m²)</th>
                        <th>Boardroom</th>
                        <th>Classroom</th>
                        <th>Theatre</th>
                        <th>Cocktail</th>
                        <th>Banquet</th>
                        <th>Cabaret</th>
                        <th>U-shape</th>
                        <th>Double U</th>
                        <th>Triple U</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($page->rooms as $room)
                    <tr>
                        <td>{{ $room->name }}</td>
                        <td>{{ $room->size_sqm ? number_format($room->size_sqm, 0) : '-' }}</td>
                        <td>{{ $room->boardroom ?? '-' }}</td>
                        <td>{{ $room->classroom ?? '-' }}</td>
                        <td>{{ $room->theatre ?? '-' }}</td>
                        <td>{{ $room->cocktail ?? '-' }}</td>
                        <td>{{ $room->banquet ?? '-' }}</td>
                        <td>{{ $room->cabaret ?? '-' }}</td>
                        <td>{{ $room->ushape ?? '-' }}</td>
                        <td>{{ $room->double_u ?? '-' }}</td>
                        <td>{{ $room->triple_u ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endif

{{-- ═══ GALLERY ═══ --}}
@if($page->gallery->count())
<section class="section-meetings">
    <div class="container">
        <div class="section-heading">
            <span class="overline">Gallery</span>
            <h2>Our Spaces</h2>
        </div>
        <div class="gallery-grid">
            @foreach($page->gallery as $item)
            <div class="gallery-grid-item">
                <img src="{{ $item->image }}" alt="{{ $item->alt_text ?? 'Meeting space' }}" loading="lazy">
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══ EQUIPMENT ═══ --}}
@if($page->equipment_items && count($page->equipment_items))
<section class="section-meetings-alt">
    <div class="container">
        <div class="section-heading">
            <span class="overline">Facilities</span>
            <h2>{{ $page->equipment_heading ?? 'Equipment & Services' }}</h2>
        </div>
        <ul class="equipment-list">
            @foreach($page->equipment_items as $item)
            <li><i class="fas fa-check-circle"></i> {{ $item }}</li>
            @endforeach
        </ul>
    </div>
</section>
@endif

{{-- ═══ CATERING ═══ --}}
@if($page->catering_heading || $page->catering_description)
<section class="section-meetings">
    <div class="container">
        <div class="section-heading">
            <span class="overline">Catering</span>
            <h2>{{ $page->catering_heading ?? 'Event Catering' }}</h2>
        </div>
        @if($page->catering_description)
            <div class="row justify-content-center mb-4">
                <div class="col-lg-8 text-center">
                    <p class="catering-text">{{ $page->catering_description }}</p>
                </div>
            </div>
        @endif
        @if($page->catering_image_1 || $page->catering_image_2 || $page->catering_image_3)
        <div class="row g-4">
            @if($page->catering_image_1)
            <div class="col-md-4">
                <div class="catering-img"><img src="{{ $page->catering_image_1 }}" alt="Catering"></div>
            </div>
            @endif
            @if($page->catering_image_2)
            <div class="col-md-4">
                <div class="catering-img"><img src="{{ $page->catering_image_2 }}" alt="Catering"></div>
            </div>
            @endif
            @if($page->catering_image_3)
            <div class="col-md-4">
                <div class="catering-img"><img src="{{ $page->catering_image_3 }}" alt="Catering"></div>
            </div>
            @endif
        </div>
        @endif
    </div>
</section>
@endif

{{-- ═══ CTA ═══ --}}
<section class="cta-section">
    <div class="container">
        <h2>Start planning your event with us</h2>
        <p>{{ $page->contact_email ?? 'reservations@brickspoint.com' }} &nbsp;|&nbsp; {{ $page->contact_phone ?? '+234 809 999 9627' }}</p>
        <a href="{{ route('website.meeting-enquiry') }}" class="btn-gold">
            <i class="fas fa-paper-plane me-2"></i>Request A Quote
        </a>
    </div>
</section>

@endsection
