@extends('website::layouts.master')

@section('title', 'Book Your Stay')

@section('content')
    <div class="container py-5">
        <section class="booking-section py-5 min-vh-100">
            <div class="container-fluid px-lg-5">
                {{-- Session Messages --}}
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-info-circle me-2"></i> {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @php
                    $stayNights = max(1, \Carbon\Carbon::parse($checkIn)->diffInDays(\Carbon\Carbon::parse($checkOut)));
                @endphp

                {{-- Page Header --}}
                <div class="text-center mb-4">
                    <span class="badge rounded-pill px-3 py-2 mb-3 book-eyebrow">
                        <i class="fas fa-calendar-check me-1"></i> Reserve Your Stay
                    </span>
                    <h1 class="display-5 fw-bold mb-2">Book Your Stay</h1>
                    <p class="text-muted mx-auto mb-0" style="max-width: 520px;">
                        Select your dates and choose from our available rooms at Brickspoint Boutique Aparthotel.
                    </p>
                </div>

                {{-- Date & Guest Selection Bar --}}
                <div class="search-bar bg-white rounded-4 shadow-sm mb-4" id="searchBar">
                    <form id="searchForm" class="row g-3 align-items-end p-3 p-lg-4">
                        <div class="col-6 col-lg-3">
                            <label for="checkIn" class="form-label field-label">
                                <i class="fas fa-sign-in-alt me-1"></i> Check-in
                            </label>
                            <input type="date" id="checkIn" name="check_in" class="form-control form-control-lg"
                                value="{{ $checkIn }}" min="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6 col-lg-3">
                            <label for="checkOut" class="form-label field-label">
                                <i class="fas fa-sign-out-alt me-1"></i> Check-out
                            </label>
                            <input type="date" id="checkOut" name="check_out" class="form-control form-control-lg"
                                value="{{ $checkOut }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                        </div>
                        <div class="col-6 col-lg-2">
                            <label for="adults" class="form-label field-label">
                                <i class="fas fa-user-friends me-1"></i> Adults
                            </label>
                            <select id="adults" name="adults" class="form-select form-select-lg">
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ $adults == $i ? 'selected' : '' }}>
                                        {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6 col-lg-2">
                            <label for="children" class="form-label field-label">
                                <i class="fas fa-child me-1"></i> Children
                            </label>
                            <select id="children" name="children" class="form-select form-select-lg">
                                @for ($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ $children == $i ? 'selected' : '' }}>
                                        {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-12 col-lg-2">
                            <button type="submit" class="btn btn-primary btn-lg w-100 btn-search">
                                <i class="fas fa-search me-2"></i> Search
                            </button>
                            <div class="text-center small mt-2">
                                <span class="badge bg-light text-dark border px-3 py-2 nights-hint">
                                    <i class="fas fa-moon me-1"></i>
                                    <span id="nightsCount">{{ $stayNights }}</span>
                                    {{ Str::plural('Night', $stayNights) }}
                                </span>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="row g-4">
                    {{-- Room Types Grid --}}
                    <div class="col-lg-8">
                        {{-- Sort & Results Count --}}
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <div class="text-muted small fw-bold">
                                <span class="results-count-badge" id="resultsCount">{{ $roomTypes->count() }}</span>
                                <span id="resultsLabel">Room Types Available</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="position-relative">
                                    <input type="text" id="roomSearchInput"
                                        class="form-control form-control-sm room-search" placeholder="Search rooms..."
                                        style="padding-left: 2.25rem;">
                                    <i class="fas fa-search position-absolute"
                                        style="left: 0.85rem; top: 50%; transform: translateY(-50%); color: #999; font-size: 0.8rem;"></i>
                                </div>
                                <select id="sortBy" class="form-select form-select-sm" style="width: auto;">
                                    <option value="default">Sort by</option>
                                    <option value="price_asc">Price ↑</option>
                                    <option value="price_desc">Price ↓</option>
                                    <option value="name_asc">Name A-Z</option>
                                </select>
                            </div>
                        </div>

                        {{-- Room Cards Container --}}
                        <div id="roomCardsContainer">
                            @forelse ($roomTypes as $roomType)
                                @php
                                    $canBook = $roomType->is_available ?? $roomType->available_count > 0;
                                    $roomStayTotal = $roomType->display_price * $stayNights;
                                @endphp
                                <div class="room-card card border-0 shadow-sm mb-4" data-room-id="{{ $roomType->id }}"
                                    data-price="{{ $roomType->display_price }}" data-name="{{ $roomType->name }}"
                                    data-capacity="{{ $roomType->capacity }}">
                                    <div class="row g-0">
                                        {{-- Room Image (desktop) --}}
                                        <div class="col-md-4 d-none d-md-block">
                                            <div class="room-card-image-wrap h-100">
                                                <img src="{{ $roomType->image_url ?? asset('images/default-room.jpg') }}"
                                                    class="img-fluid h-100 w-100 object-fit-cover room-card-img"
                                                    alt="{{ $roomType->name }}">
                                                <div class="room-price-badge">
                                                    <span
                                                        class="room-price-amount">₦{{ number_format($roomType->display_price, 2) }}</span>
                                                    <span class="room-price-per">/ night</span>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Room Image (mobile, full width) --}}
                                        <div class="col-12 d-md-none position-relative">
                                            <div class="room-card-image-wrap">
                                                <img src="{{ $roomType->image_url ?? asset('images/default-room.jpg') }}"
                                                    class="img-fluid w-100 object-fit-cover room-card-img"
                                                    alt="{{ $roomType->name }}" style="height: 200px;">
                                                <div class="room-price-badge room-price-badge-mobile">
                                                    <span
                                                        class="room-price-amount">₦{{ number_format($roomType->display_price, 2) }}</span>
                                                    <span class="room-price-per">/ night</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Room Details --}}
                                        <div class="col-12 col-xl-8 col-lg-7">
                                            <div
                                                class="card-body p-3 p-sm-4 d-flex flex-column h-100 justify-content-between">

                                                {{-- Header: Room Name & Desktop Price --}}
                                                <div>
                                                    <div
                                                        class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                                        <div class="flex-grow-1">
                                                            <h4
                                                                class="card-title fw-bold mb-1 room-title fs-5 fs-sm-4 text-dark">
                                                                {{ $roomType->name }}
                                                            </h4>

                                                            {{-- Room Metadata Chips --}}
                                                            <div
                                                                class="d-flex flex-wrap align-items-center gap-2 gap-sm-3 text-muted small mb-2">
                                                                <span class="d-inline-flex align-items-center">
                                                                    <i class="fas fa-user-friends me-1 text-primary"></i>
                                                                    Max {{ $roomType->capacity }} Guests
                                                                </span>
                                                                @if ($roomType->bed_type)
                                                                    <span class="d-inline-flex align-items-center">
                                                                        <i class="fas fa-bed me-1 text-primary"></i>
                                                                        {{ $roomType->bed_type }}
                                                                    </span>
                                                                @endif
                                                                @if ($roomType->room_size)
                                                                    <span class="d-inline-flex align-items-center">
                                                                        <i
                                                                            class="fas fa-vector-square me-1 text-primary"></i>
                                                                        {{ $roomType->room_size }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        {{-- Desktop Side Price Display --}}
                                                        <div class="text-end d-none d-md-block ms-auto">
                                                            <div class="h4 text-success fw-bold mb-0">
                                                                ₦{{ number_format($roomType->display_price, 2) }}</div>
                                                            <small class="text-muted d-block fs-7">per night</small>
                                                        </div>
                                                    </div>

                                                    {{-- Availability Status Badge --}}
                                                    <div class="mb-3">
                                                        @if ($canBook)
                                                            <span
                                                                class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill fw-medium">
                                                                <i class="fas fa-check-circle me-1"></i>
                                                                {{ $roomType->available_count }}
                                                                {{ Str::plural('Room', $roomType->available_count) }}
                                                                Available
                                                            </span>
                                                        @elseif (($roomType->availability_reason ?? null) === 'stop_sell')
                                                            <span
                                                                class="badge bg-secondary-subtle text-secondary border border-secondary px-3 py-2 rounded-pill fw-medium">
                                                                <i class="fas fa-ban me-1"></i> Not Available for Sale
                                                            </span>
                                                        @elseif (($roomType->availability_reason ?? null) === 'closed_to_arrival')
                                                            <span
                                                                class="badge bg-warning-subtle text-warning border border-warning px-3 py-2 rounded-pill fw-medium">
                                                                <i class="fas fa-sign-in-alt me-1"></i> No Check-in on This
                                                                Date
                                                            </span>
                                                        @elseif (($roomType->availability_reason ?? null) === 'min_stay')
                                                            <span
                                                                class="badge bg-warning-subtle text-warning border border-warning px-3 py-2 rounded-pill fw-medium">
                                                                <i class="fas fa-clock me-1"></i>
                                                                {{ $roomType->availability_message ?? 'Minimum Stay Required' }}
                                                            </span>
                                                        @else
                                                            <span
                                                                class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 rounded-pill fw-medium">
                                                                <i class="fas fa-times-circle me-1"></i> Fully Booked
                                                            </span>
                                                        @endif
                                                    </div>

                                                    {{-- Amenities Chip Grid --}}
                                                    <div class="room-amenities mb-3">
                                                        <div class="d-flex flex-wrap gap-1 gap-sm-2">
                                                            @foreach ($roomType->amenities->take(6) as $amenity)
                                                                <span
                                                                    class="badge bg-light text-dark border fw-normal small amenity-chip py-1 px-2">
                                                                    <i
                                                                        class="{{ $amenity->icon ?? 'fas fa-check' }} text-primary me-1"></i>
                                                                    {{ $amenity->name }}
                                                                </span>
                                                            @endforeach
                                                            @if ($roomType->amenities->count() > 6)
                                                                <span
                                                                    class="badge bg-light text-muted border fw-normal small py-1 px-2">
                                                                    +{{ $roomType->amenities->count() - 6 }} more
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Stay Total Price Summary --}}
                                                    @if ($canBook)
                                                        <div class="stay-total mb-3">
                                                            <i class="fas fa-calculator me-1 text-primary"></i>
                                                            <span
                                                                class="fw-bold">₦{{ number_format($roomStayTotal, 2) }}</span>
                                                            total
                                                            <span class="text-muted fw-normal">for {{ $stayNights }}
                                                                {{ Str::plural('night', $stayNights) }}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Controls & CTA Section --}}
                                                <div class="pt-3 border-top mt-auto">
                                                    <div class="row g-2 align-items-center">
                                                        {{-- Room Quantity Dropdown --}}
                                                        <div class="col-12 col-sm-4 col-md-3">
                                                            <div
                                                                class="d-flex align-items-center justify-content-between justify-content-sm-start gap-2">
                                                                <label
                                                                    class="small fw-bold text-muted mb-0 flex-shrink-0">Rooms:</label>
                                                                <select
                                                                    class="form-select form-select-sm room-quantity-select w-100"
                                                                    data-room-id="{{ $roomType->id }}"
                                                                    style="min-height: 42px;"
                                                                    {{ !$canBook ? 'disabled' : '' }}>
                                                                    @for ($i = 1; $i <= min($roomType->available_count ?: 0, 10); $i++)
                                                                        <option value="{{ $i }}">
                                                                            {{ $i }}</option>
                                                                    @endfor
                                                                    @if (!$canBook)
                                                                        <option value="0">0</option>
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>

                                                        {{-- Booking CTA Button --}}
                                                        <div class="col-12 col-sm-8 col-md-9">
                                                            @if ($canBook)
                                                                <button type="button"
                                                                    class="btn btn-primary select-room-btn w-100 d-flex align-items-center justify-content-center gap-2"
                                                                    style="min-height: 48px;"
                                                                    data-room-id="{{ $roomType->id }}"
                                                                    data-room-name="{{ $roomType->name }}"
                                                                    data-room-price="{{ $roomType->display_price }}"
                                                                    data-room-capacity="{{ $roomType->capacity }}"
                                                                    data-base-occupancy="{{ $roomType->base_occupancy ?? 2 }}"
                                                                    data-extra-adult-fee="{{ $roomType->extra_adult_fee ?? 0 }}"
                                                                    data-extra-child-fee="{{ $roomType->extra_child_fee ?? 0 }}"
                                                                    data-room-image="{{ $roomType->image_url }}">
                                                                    <i class="fas fa-plus"></i>
                                                                    <span>Select Room</span>
                                                                </button>
                                                            @else
                                                                <button type="button"
                                                                    class="btn btn-secondary w-100 d-flex align-items-center justify-content-center gap-2"
                                                                    style="min-height: 48px;" disabled>
                                                                    <i class="fas fa-ban"></i>
                                                                    <span>Unavailable</span>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                                    <h4 class="text-muted">No rooms available</h4>
                                    <p class="text-muted">Please try different dates.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Cart Sidebar (Livewire) --}}
                    <div class="col-lg-4">
                        <livewire:website.cart-sidebar />

                        {{-- Booking Dates Display --}}
                        <div class="card border-0 shadow-sm mt-3 booking-dates-card">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between small">
                                    <div>
                                        <div class="text-muted text-uppercase small fw-bold">Check-in</div>
                                        <div class="fw-bold" id="displayCheckIn">
                                            {{ \Carbon\Carbon::parse($checkIn)->format('M d, Y') }}</div>
                                    </div>
                                    <div class="text-center align-self-center">
                                        <i class="fas fa-arrow-right text-primary"></i>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-muted text-uppercase small fw-bold">Check-out</div>
                                        <div class="fw-bold" id="displayCheckOut">
                                            {{ \Carbon\Carbon::parse($checkOut)->format('M d, Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    {{-- Mobile Sticky Checkout Bar --}}
    <div id="mobileCheckoutBar" class="mobile-checkout-bar d-lg-none">
        <div class="mobile-checkout-bar-inner">
                <div class="mobile-checkout-info">
                    <span class="mobile-checkout-total" id="mobileCartTotal">{{ $cart['formatted_total'] }}</span>
                    <span class="mobile-checkout-rooms"><span id="mobileCartRooms">{{ $cart['total_rooms'] }}</span>
                        room(s) · <span id="mobileCartNights">{{ $cart['nights'] }}</span> night(s)</span>
                </div>
                <button type="button" id="mobileContinueBtn"
                    class="btn btn-primary btn-lg w-100 w-md-auto mobile-continue-btn" style="min-height: 52px;"
                    data-booking-url="{{ route('website.booking', [], false) }}">
                    <i class="fas fa-arrow-right me-2"></i> Continue to Checkout
                </button>
            </div>
        </div>
    </div>
    <style>
        .booking-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
        }

        .book-eyebrow {
            background: var(--brand-cream);
            color: var(--brand-gold-dark);
            border: 1px solid var(--brand-gold-light);
            font-size: 0.8rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .search-bar {
            border-radius: 16px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06) !important;
        }

        .search-bar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--brand-gold), var(--brand-gold-light), var(--brand-gold));
        }

        .field-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 0.35rem;
        }

        .field-label i {
            color: var(--brand-gold);
        }

        .btn-search {
            height: 48px;
            border-radius: 10px;
        }

        .nights-hint {
            font-size: 0.78rem;
        }

        .results-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 28px;
            padding: 0 8px;
            background: var(--brand-gold);
            color: #fff;
            border-radius: 50rem;
            font-size: 0.8rem;
            margin-right: 4px;
        }

        .room-search {
            border-radius: 8px;
            border-color: #dee2e6;
        }

        .room-card {
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.2s ease;
            border: 2px solid transparent !important;
        }

        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.10) !important;
            border-color: var(--brand-gold-light) !important;
        }

        .room-card.selected {
            border-color: #198754 !important;
            box-shadow: 0 10px 30px rgba(25, 135, 84, 0.12) !important;
        }

        .room-card-image-wrap {
            position: relative;
            overflow: hidden;
            min-height: 100%;
            background: #f0f0f0;
        }

        .room-card-img {
            transition: transform 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .room-card-image-wrap:hover .room-card-img {
            transform: scale(1.06);
        }

        .room-price-badge {
            position: absolute;
            bottom: 12px;
            left: 12px;
            background: rgba(26, 26, 46, 0.88);
            backdrop-filter: blur(6px);
            color: #fff;
            padding: 6px 12px;
            border-radius: 8px;
            border-left: 3px solid var(--brand-gold);
            display: flex;
            align-items: baseline;
            gap: 4px;
            z-index: 2;
        }

        .room-price-amount {
            font-weight: 800;
            font-size: 1.05rem;
            color: var(--brand-gold-light);
        }

        .room-price-per {
            font-size: 0.72rem;
            color: rgba(255, 255, 255, 0.75);
        }

        .room-price-badge-mobile {
            bottom: 12px;
            left: 12px;
        }

        .room-title {
            color: var(--brand-dark);
        }

        .amenity-chip {
            border-radius: 50rem;
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        .stay-total {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--brand-gold-dark);
            background: var(--brand-cream);
            border: 1px dashed var(--brand-gold-light);
            border-radius: 8px;
            padding: 0.4rem 0.75rem;
            display: inline-block;
        }

        .booking-dates-card {
            border-radius: 16px;
        }

        .object-fit-cover {
            object-fit: cover;
        }

        .cart-sidebar .card {
            border-radius: 16px;
            overflow: hidden;
        }

        .cart-header {
            border-bottom: 3px solid var(--brand-gold);
        }

        .cart-badge {
            background: var(--brand-gold);
            color: #fff;
        }

        .cart-empty-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--brand-cream);
            border: 1px solid var(--brand-gold-light);
            color: var(--brand-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .cart-total {
            color: var(--brand-gold-dark);
        }

        .tracking-wide {
            letter-spacing: 0.5px;
        }

        .cart-item {
            transition: background-color 0.2s ease;
        }

        .cart-item:hover {
            background-color: #f8f9fa;
        }

        .select-room-btn.added {
            background-color: #198754 !important;
            border-color: #198754 !important;
        }

        .select-room-btn:active {
            transform: scale(0.98);
        }

        .mobile-checkout-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            background: rgba(26, 26, 46, 0.98);
            backdrop-filter: blur(12px);
            border-top: 1px solid rgba(200, 161, 101, 0.3);
            padding: 12px 16px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .mobile-checkout-bar.visible {
            transform: translateY(0);
        }

        .mobile-checkout-bar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 600px;
            margin: 0 auto;
            gap: 12px;
            flex-wrap: wrap;
        }

        .mobile-checkout-info {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-width: 0;
        }

        .mobile-checkout-total {
            color: #c8a165;
            font-weight: 700;
            font-size: 1.15rem;
        }

        .mobile-checkout-rooms {
            color: #999;
            font-size: 0.8rem;
        }

        .mobile-continue-btn {
            min-height: 52px;
            white-space: nowrap;
        }

        /* Responsive typography & element sizing */
        .fs-7 {
            font-size: 0.75rem;
        }

        .amenity-chip {
            font-size: 0.78rem;
            white-space: nowrap;
        }

        .stay-total {
            font-size: 0.85rem;
            background-color: var(--brand-cream, #fdfbf7);
            border: 1px dashed var(--brand-gold-light, #d4b07a);
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            display: inline-block;
        }

        @media (max-width: 575.98px) {
            .stay-total {
                display: block;
                width: 100%;
                text-align: center;
            }
        }

        @media (min-width: 992px) {
            .mobile-checkout-bar {
                display: none !important;
            }
        }

        @media (max-width: 575.98px) {
            .mobile-checkout-bar-inner {
                flex-direction: column;
                align-items: stretch;
            }

            .mobile-checkout-info {
                text-align: center;
            }

            .mobile-continue-btn {
                width: 100%;
            }

            .room-card .card-body {
                padding: 1rem !important;
            }

            .select-room-btn {
                font-size: 0.95rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const checkInInput = document.getElementById('checkIn');
            const checkOutInput = document.getElementById('checkOut');
            const searchForm = document.getElementById('searchForm');
            const mobileCheckoutBar = document.getElementById('mobileCheckoutBar');
            const mobileContinueBtn = document.getElementById('mobileContinueBtn');
            const globalMobileBar = document.querySelector('.mobile-sticky-bar');
            const mobileCartNights = document.getElementById('mobileCartNights');
            const nightsCount = document.getElementById('nightsCount');
            const resultsLabel = document.getElementById('resultsLabel');

            const formatDate = (dateStr) => {
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
            };

            function updateNightsHint() {
                if (!checkInInput.value || !checkOutInput.value) return;
                const nights = Math.max(1, Math.round((new Date(checkOutInput.value) - new Date(checkInInput
                    .value)) / 86400000));
                if (nightsCount) nightsCount.textContent = nights;
            }

            // Sync mobile checkout bar + room-card states from server cart
            function updateCartUI(cart) {
                const mobileTotal = document.getElementById('mobileCartTotal');
                const mobileRooms = document.getElementById('mobileCartRooms');

                if (cart.items.length > 0) {
                    mobileCheckoutBar.style.display = 'block';
                    // Slide up on next paint so the transition animates
                    requestAnimationFrame(() => mobileCheckoutBar.classList.add('visible'));
                    mobileTotal.textContent = cart.formatted_total;
                    mobileRooms.textContent = cart.total_rooms;
                    if (mobileCartNights) mobileCartNights.textContent = cart.nights;
                    if (globalMobileBar) globalMobileBar.style.display = 'none';
                } else {
                    mobileCheckoutBar.classList.remove('visible');
                    mobileCheckoutBar.style.display = 'none';
                    if (globalMobileBar) globalMobileBar.style.display = '';
                }

                updateSelectButtonStates(cart);
            }

            // Update button states based on cart
            function updateSelectButtonStates(cart) {
                document.querySelectorAll('.select-room-btn').forEach(btn => {
                    const roomId = btn.dataset.roomId;
                    const card = btn.closest('.room-card');
                    const inCart = cart.items.find(item => item.room_type_id == roomId);

                    if (inCart) {
                        btn.innerHTML = '<i class="fas fa-check me-1"></i> ' + inCart.quantity + ' Added';
                        btn.classList.add('added');
                        if (card) card.classList.add('selected');
                    } else {
                        btn.innerHTML = '<i class="fas fa-plus me-1"></i> Select Room';
                        btn.classList.remove('added');
                        if (card) card.classList.remove('selected');
                    }
                });
            }

            // Livewire cart integration
            function initLivewireCart() {
                if (typeof Livewire !== 'undefined') {
                    Livewire.on('cart-updated', ({
                        cart
                    }) => {
                        if (cart && cart.items) {
                            updateCartUI(cart);
                        }
                    });
                    Livewire.on('cart-error', ({
                        message
                    }) => alert(message || 'An error occurred. Please try again.'));
                    // Initial sync when cart already has items
                    updateCartUI(@json($cart));
                }
            }

            // Try immediate init if Livewire already loaded
            initLivewireCart();

            // Also listen for init event (in case Livewire loads after)
            document.addEventListener('livewire:init', initLivewireCart);

            // Select room button click → add to cart via Livewire
            document.querySelectorAll('.select-room-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const roomId = this.dataset.roomId;
                    const quantitySelect = document.querySelector(
                        `.room-quantity-select[data-room-id="${roomId}"]`);
                    const quantity = parseInt(quantitySelect.value) || 1;
                    const checkIn = checkInInput.value;
                    const checkOut = checkOutInput.value;

                    if (!checkIn || !checkOut) {
                        alert('Please select check-in and check-out dates first.');
                        return;
                    }

                    const adults = parseInt(document.getElementById('adults')?.value || 1);
                    const children = parseInt(document.getElementById('children')?.value || 0);

                    // Visual feedback on tap
                    this.classList.add('tapped');
                    setTimeout(() => this.classList.remove('tapped'), 150);

                    Livewire.dispatchTo('website.cart-sidebar', 'add', {
                        roomTypeId: roomId,
                        quantity: quantity,
                        checkIn: checkIn,
                        checkOut: checkOut,
                        adults: adults,
                        children: children,
                    });
                });
            });

            // Mobile Continue button - navigate to booking page
            if (mobileContinueBtn) {
                mobileContinueBtn.addEventListener('click', function() {
                    const url = this.dataset.bookingUrl;
                    if (url) {
                        this.disabled = true;
                        this.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Redirecting...';
                        window.location.href = url;
                    }
                });
            }

            // ── Room Search & Sort ──
            const roomSearchInput = document.getElementById('roomSearchInput');
            const sortBySelect = document.getElementById('sortBy');
            const roomCardsContainer = document.getElementById('roomCardsContainer');
            const resultsCount = document.getElementById('resultsCount');
            const originalOrder = Array.from(roomCardsContainer.querySelectorAll('.room-card'));

            function filterAndSortRooms() {
                const query = (roomSearchInput.value || '').toLowerCase().trim();
                const sortBy = sortBySelect.value;
                const cards = Array.from(roomCardsContainer.querySelectorAll('.room-card'));

                // Filter
                let visible = 0;
                cards.forEach(card => {
                    const name = (card.dataset.name || '').toLowerCase();
                    const capacity = card.dataset.capacity || '';
                    const matches = !query || name.includes(query) || capacity.includes(query);
                    card.style.display = matches ? '' : 'none';
                    if (matches) visible++;
                });

                // Sort (only visible)
                const sorted = cards.sort((a, b) => {
                    if (sortBy === 'price_asc') return parseFloat(a.dataset.price) - parseFloat(b.dataset
                        .price);
                    if (sortBy === 'price_desc') return parseFloat(b.dataset.price) - parseFloat(a.dataset
                        .price);
                    if (sortBy === 'name_asc') return (a.dataset.name || '').localeCompare(b.dataset.name ||
                        '');
                    // default: original order
                    return originalOrder.indexOf(a) - originalOrder.indexOf(b);
                });

                // Re-append in sorted order
                sorted.forEach(card => roomCardsContainer.appendChild(card));

                resultsCount.textContent = visible;
                if (resultsLabel) resultsLabel.textContent = visible === 1 ? 'Room Type Available' :
                    'Room Types Available';
            }

            let _searchTimer = null;
            if (roomSearchInput) {
                roomSearchInput.addEventListener('input', function() {
                    clearTimeout(_searchTimer);
                    _searchTimer = setTimeout(filterAndSortRooms, 250);
                });
            }
            if (sortBySelect) {
                sortBySelect.addEventListener('change', filterAndSortRooms);
            }

            // Search form submit - reload page with new dates
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const checkIn = checkInInput.value;
                const checkOut = checkOutInput.value;
                const adults = document.getElementById('adults').value;
                const children = document.getElementById('children').value;

                // Clear cart if dates change (cart service handles this, but also update URL)
                window.location.href =
                    `{{ route('website.book', [], false) }}?check_in=${checkIn}&check_out=${checkOut}&adults=${adults}&children=${children}`;
            });

            // Update check-out min date when check-in changes
            checkInInput.addEventListener('change', function() {
                const nextDay = new Date(this.value);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOutInput.min = nextDay.toISOString().split('T')[0];

                if (new Date(checkOutInput.value) <= new Date(this.value)) {
                    checkOutInput.value = nextDay.toISOString().split('T')[0];
                }

                // Update display
                document.getElementById('displayCheckIn').textContent = formatDate(this.value);
                updateNightsHint();
            });

            checkOutInput.addEventListener('change', function() {
                document.getElementById('displayCheckOut').textContent = formatDate(this.value);
                updateNightsHint();
            });

        });
    </script>
@endsection
