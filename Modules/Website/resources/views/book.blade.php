@extends('website::layouts.master')

@section('title', 'Book Your Stay')

@section('content')
    <div class="container py-5">
    <section class="booking-section py-5 bg-light min-vh-100">
        <div class="container-fluid px-lg-5">
            {{-- Session Messages --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-info-circle me-2"></i> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Page Header --}}
            <div class="text-center mb-4">
                <h1 class="display-5 fw-bold mb-2">Book Your Stay</h1>
                <p class="text-muted">Select your dates and choose from our available rooms</p>
            </div>

            {{-- Date & Guest Selection Bar --}}
            <div class="search-bar bg-white p-4 rounded-3 shadow-sm mb-4" id="searchBar">
                <form id="searchForm" class="row g-3 align-items-end">
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-bold small text-muted">CHECK-IN</label>
                        <input type="date" id="checkIn" name="check_in" class="form-control form-control-lg" 
                               value="{{ $checkIn }}" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-bold small text-muted">CHECK-OUT</label>
                        <input type="date" id="checkOut" name="check_out" class="form-control form-control-lg" 
                               value="{{ $checkOut }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold small text-muted">ADULTS</label>
                        <select id="adults" name="adults" class="form-select form-select-lg">
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ $adults == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold small text-muted">CHILDREN</label>
                        <select id="children" name="children" class="form-select form-select-lg">
                            @for ($i = 0; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ $children == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-md-none">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                    </div>
                    <div class="col-md-2 d-none d-md-block">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            <div class="row">
                {{-- Room Types Grid --}}
                <div class="col-lg-8">
                    {{-- Sort & Results Count --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div class="text-muted">
                            <span id="resultsCount">{{ $roomTypes->count() }}</span> Room Types Available
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="position-relative">
                                <input type="text" id="roomSearchInput" class="form-control form-control-sm"
                                    placeholder="Search rooms..." style="width: 180px; padding-left: 2rem;">
                                <i class="fas fa-search position-absolute" style="left: 0.75rem; top: 50%; transform: translateY(-50%); color: #999; font-size: 0.8rem;"></i>
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
                             <div class="room-card card border-0 shadow-sm mb-4" data-room-id="{{ $roomType->id }}"
                                  data-price="{{ $roomType->display_price }}" data-name="{{ $roomType->name }}" data-capacity="{{ $roomType->capacity }}">
                                <div class="row g-0">
                                    {{-- Room Image --}}
                                    <div class="col-md-4 position-relative d-none d-md-block">
                                        <img src="{{ $roomType->image_url ?? asset('images/default-room.jpg') }}" 
                                             class="img-fluid h-100 w-100 object-fit-cover" 
                                             alt="{{ $roomType->name }}" style="min-height: 250px;">
                                    </div>
                                    {{-- Mobile Image (full width) --}}
                                    <div class="col-12 d-md-none position-relative">
                                        <img src="{{ $roomType->image_url ?? asset('images/default-room.jpg') }}" 
                                             class="img-fluid w-100 object-fit-cover" 
                                             alt="{{ $roomType->name }}" style="height: 180px;">
                                    </div>
                                    
                                    {{-- Room Details --}}
                                    <div class="col-md-8 col-12">
                                        <div class="card-body p-4 p-md-4">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h4 class="card-title fw-bold mb-1">{{ $roomType->name }}</h4>
                                                    <div class="text-muted small mb-2">
                                                        <span class="me-3"><i class="fas fa-user-friends me-1"></i> Max {{ $roomType->capacity }} Guests</span>
                                                        @if($roomType->bed_type)
                                                            <span><i class="fas fa-bed me-1"></i> {{ $roomType->bed_type }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="h4 text-success fw-bold mb-0">₦{{ number_format($roomType->display_price, 2) }}</div>
                                                    <small class="text-muted">per night</small>
                                                </div>
                                            </div>

                                            {{-- Availability Badge --}}
                                            <div class="mb-3">
                                                @if ($roomType->is_available ?? ($roomType->available_count > 0))
                                                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        {{ $roomType->available_count }} {{ Str::plural('Room', $roomType->available_count) }} Available
                                                    </span>
                                                @elseif (($roomType->availability_reason ?? null) === 'stop_sell')
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary px-3 py-2">
                                                        <i class="fas fa-ban me-1"></i> Not Available for Sale
                                                    </span>
                                                @elseif (($roomType->availability_reason ?? null) === 'closed_to_arrival')
                                                    <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2">
                                                        <i class="fas fa-sign-in-alt me-1"></i> No Check-in on This Date
                                                    </span>
                                                @elseif (($roomType->availability_reason ?? null) === 'min_stay')
                                                    <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2">
                                                        <i class="fas fa-clock me-1"></i> {{ $roomType->availability_message ?? 'Minimum Stay Required' }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2">
                                                        <i class="fas fa-times-circle me-1"></i> Fully Booked
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Amenities --}}
                                            <div class="room-amenities mb-3">
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach ($roomType->amenities->take(6) as $amenity)
                                                        <span class="badge bg-light text-dark border small">
                                                            <i class="{{ $amenity->icon ?? 'fas fa-check' }} text-primary me-1"></i>
                                                            {{ $amenity->name }}
                                                        </span>
                                                    @endforeach
                                                    @if($roomType->amenities->count() > 6)
                                                        <span class="badge bg-light text-muted border small">
                                                            +{{ $roomType->amenities->count() - 6 }} more
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Room Selection Controls --}}
                                            @php $canBook = $roomType->is_available ?? ($roomType->available_count > 0); @endphp
                                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pt-3 border-top gap-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <label class="small fw-bold text-muted mb-0">Rooms:</label>
                                                    <select class="form-select form-select-sm room-quantity-select" 
                                                            data-room-id="{{ $roomType->id }}"
                                                            style="width: 80px;"
                                                            {{ !$canBook ? 'disabled' : '' }}>
                                                        @for ($i = 1; $i <= min($roomType->available_count ?: 0, 10); $i++)
                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                        @endfor
                                                        @if(!$canBook)
                                                            <option value="0">0</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                @if($canBook)
                                        <button type="button" 
                                                class="btn btn-primary select-room-btn w-100 w-md-auto"
                                                style="min-height: 48px;"
                                                data-room-id="{{ $roomType->id }}"
                                                data-room-name="{{ $roomType->name }}"
                                                data-room-price="{{ $roomType->display_price }}"
                                                data-room-capacity="{{ $roomType->capacity }}"
                                                data-base-occupancy="{{ $roomType->base_occupancy ?? 2 }}"
                                                data-extra-adult-fee="{{ $roomType->extra_adult_fee ?? 0 }}"
                                                data-extra-child-fee="{{ $roomType->extra_child_fee ?? 0 }}"
                                                data-room-image="{{ $roomType->image_url }}">
                                                <i class="fas fa-plus me-1"></i> Select Room
                                            </button>
                                            @else
                                                <button type="button" class="btn btn-secondary w-100 w-md-auto" style="min-height: 48px;" disabled>
                                                    <i class="fas fa-ban me-1"></i> Unavailable
                                                </button>
                                            @endif
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

                {{-- Floating Cart Sidebar (Livewire) --}}
                <div class="col-lg-4">
                    <livewire:website.cart-sidebar />

                    {{-- Booking Dates Display --}}
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between small">
                                <div>
                                    <div class="text-muted">Check-in</div>
                                    <div class="fw-bold" id="displayCheckIn">{{ \Carbon\Carbon::parse($checkIn)->format('M d, Y') }}</div>
                                </div>
                                <div class="text-center">
                                    <i class="fas fa-arrow-right text-muted"></i>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted">Check-out</div>
                                    <div class="fw-bold" id="displayCheckOut">{{ \Carbon\Carbon::parse($checkOut)->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Mobile Sticky Checkout Bar --}}
    <div id="mobileCheckoutBar" class="mobile-checkout-bar d-lg-none" style="display: none;">
        <div class="mobile-checkout-bar-inner">
            <div class="mobile-checkout-info">
                <span class="mobile-checkout-total" id="mobileCartTotal">{{ $cart['formatted_total'] }}</span>
                <span class="mobile-checkout-rooms"><span id="mobileCartRooms">{{ $cart['total_rooms'] }}</span> room(s) · <span id="mobileCartNights">{{ $cart['nights'] }}</span> night(s)</span>
            </div>
            <button type="button" id="mobileContinueBtn" class="btn btn-primary btn-lg w-100 w-md-auto" style="min-height: 48px;" data-booking-url="{{ route('website.booking', [], false) }}">
                <i class="fas fa-arrow-right me-2"></i> Continue to Checkout
            </button>
        </div>
    </div>
    </div>
    <style>
        .booking-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
        }
        .search-bar {
            border-radius: 15px;
        }
        .room-card {
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.2s ease;
        }
        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
        }
        .room-card:active {
            transform: scale(0.99);
        }
        .object-fit-cover {
            object-fit: cover;
        }
        .cart-sidebar .card {
            border-radius: 15px;
            overflow: hidden;
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
            background: rgba(26, 26, 26, 0.98);
            backdrop-filter: blur(12px);
            border-top: 1px solid rgba(200, 161, 101, 0.3);
            padding: 12px 16px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
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
            min-height: 48px;
            white-space: nowrap;
        }
        @media (min-width: 992px) {
            .mobile-checkout-bar { display: none !important; }
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

            const formatDate = (dateStr) => {
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            };

            // Sync mobile checkout bar + room-card states from server cart
            function updateCartUI(cart) {
                const mobileTotal = document.getElementById('mobileCartTotal');
                const mobileRooms = document.getElementById('mobileCartRooms');

                if (cart.items.length > 0) {
                    mobileCheckoutBar.classList.add('visible');
                    mobileTotal.textContent = cart.formatted_total;
                    mobileRooms.textContent = cart.total_rooms;
                    if (mobileCartNights) mobileCartNights.textContent = cart.nights;
                    if (globalMobileBar) globalMobileBar.style.display = 'none';
                } else {
                    mobileCheckoutBar.classList.remove('visible');
                    if (globalMobileBar) globalMobileBar.style.display = '';
                }

                updateSelectButtonStates(cart);
            }

            // Update button states based on cart
            function updateSelectButtonStates(cart) {
                document.querySelectorAll('.select-room-btn').forEach(btn => {
                    const roomId = btn.dataset.roomId;
                    const inCart = cart.items.find(item => item.room_type_id == roomId);
                    
                    if (inCart) {
                        btn.innerHTML = '<i class="fas fa-check me-1"></i> ' + inCart.quantity + ' Added';
                        btn.classList.add('added');
                    } else {
                        btn.innerHTML = '<i class="fas fa-plus me-1"></i> Select Room';
                        btn.classList.remove('added');
                    }
                });
            }

            // Livewire cart integration
            document.addEventListener('livewire:init', () => {
                Livewire.on('cart-updated', ({ cart }) => updateCartUI(cart));
                Livewire.on('cart-error', ({ message }) => alert(message || 'An error occurred. Please try again.'));

                // Initial sync when cart already has items
                updateCartUI(@json($cart));
            });

            // Select room button click → add to cart via Livewire
            document.querySelectorAll('.select-room-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const roomId = this.dataset.roomId;
                    const quantitySelect = document.querySelector(`.room-quantity-select[data-room-id="${roomId}"]`);
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
                        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Redirecting...';
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
                    if (sortBy === 'price_asc') return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                    if (sortBy === 'price_desc') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                    if (sortBy === 'name_asc') return (a.dataset.name || '').localeCompare(b.dataset.name || '');
                    // default: original order
                    return originalOrder.indexOf(a) - originalOrder.indexOf(b);
                });

                // Re-append in sorted order
                sorted.forEach(card => roomCardsContainer.appendChild(card));

                resultsCount.textContent = visible;
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
                window.location.href = `{{ route('website.book', [], false) }}?check_in=${checkIn}&check_out=${checkOut}&adults=${adults}&children=${children}`;
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
            });

            checkOutInput.addEventListener('change', function() {
                document.getElementById('displayCheckOut').textContent = formatDate(this.value);
            });

        });
    </script>
@endsection