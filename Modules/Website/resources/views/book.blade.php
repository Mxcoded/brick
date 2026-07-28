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
            <div class="search-bar bg-white p-4 rounded-3 shadow-sm mb-4">
                <form id="searchForm" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">CHECK-IN</label>
                        <input type="date" id="checkIn" name="check_in" class="form-control form-control-lg" 
                               value="{{ $checkIn }}" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">CHECK-OUT</label>
                        <input type="date" id="checkOut" name="check_out" class="form-control form-control-lg" 
                               value="{{ $checkOut }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">ADULTS</label>
                        <select id="adults" name="adults" class="form-select form-select-lg">
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ $adults == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">CHILDREN</label>
                        <select id="children" name="children" class="form-select form-select-lg">
                            @for ($i = 0; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ $children == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
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
                                    <div class="col-md-4 position-relative">
                                        <img src="{{ $roomType->image_url ?? asset('images/default-room.jpg') }}" 
                                             class="img-fluid h-100 w-100 object-fit-cover" 
                                             alt="{{ $roomType->name }}" style="min-height: 250px;">
                                    </div>
                                    
                                    {{-- Room Details --}}
                                    <div class="col-md-8">
                                        <div class="card-body p-4">
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
                                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                                <div class="d-flex align-items-center gap-2">
                                                    <label class="small fw-bold text-muted mb-0">Number of Rooms:</label>
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
                                                class="btn btn-primary select-room-btn"
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
                                                    <button type="button" class="btn btn-secondary" disabled>
                                                        <i class="fas fa-ban me-1"></i> Unavailable
                                                    </button>
                                                @endif
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

                {{-- Floating Cart Sidebar --}}
                <div class="col-lg-4">
                    <div class="cart-sidebar sticky-top" style="top: 100px;">
                        <div class="card border-0 shadow">
                            {{-- Cart Header --}}
                            <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    <span id="cartBadge" class="badge bg-primary rounded-pill ms-1">{{ $cart['total_rooms'] }}</span>
                                </h5>
                                <span class="small">Selected Rooms</span>
                            </div>

                            {{-- Cart Body --}}
                            <div class="card-body p-0" id="cartBody">
                                {{-- Cart Items (Dynamic) --}}
                                <div id="cartItems" class="p-3">
                                    @if(empty($cart['items']))
                                        <div id="emptyCartMessage" class="text-center py-4 text-muted">
                                            <i class="fas fa-bed fa-2x mb-2 d-block opacity-50"></i>
                                            <h6 class="fw-bold">No rooms selected yet</h6>
                                            <p class="small mb-0">Start selecting rooms to build your reservation</p>
                                        </div>
                                    @else
                                        @foreach($cart['items'] as $item)
                                            <div class="cart-item d-flex justify-content-between align-items-start py-2 border-bottom" 
                                                 data-room-id="{{ $item['room_type_id'] }}">
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold small">{{ $item['room_type_name'] }}</div>
                                                    <div class="text-muted small">
                                                        {{ $item['quantity'] }} room × {{ $item['nights'] }} nights
                                                    </div>
                                                    <div class="text-success small fw-bold">
                                                        ₦{{ number_format($item['subtotal'], 2) }}
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-cart-item"
                                                        data-room-id="{{ $item['room_type_id'] }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                {{-- Cart Summary --}}
                                <div class="cart-summary bg-light p-3 border-top">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">Total Rooms</span>
                                        <span class="fw-bold" id="totalRooms">{{ $cart['total_rooms'] }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">Total Guests Capacity</span>
                                        <span class="fw-bold" id="totalGuests">{{ $cart['total_guests'] }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small mb-2">
                                        <span class="text-muted">Nights</span>
                                        <span class="fw-bold" id="cartNights">{{ $cart['nights'] ?: '-' }}</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="h6 mb-0">Total</span>
                                        <span class="h5 text-success fw-bold mb-0" id="cartTotal">{{ $cart['formatted_total'] }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Cart Actions --}}
                            <div class="card-footer bg-white py-3">
                                <button type="button" id="clearCartBtn" class="btn btn-outline-danger btn-sm w-100 mb-2"
                                        {{ empty($cart['items']) ? 'disabled' : '' }}>
                                    <i class="fas fa-trash me-1"></i> Clear All
                                </button>
                                <a href="{{ route('website.booking') }}" id="continueBtn" 
                                   class="btn btn-primary btn-lg w-100 {{ empty($cart['items']) ? 'disabled' : '' }}">
                                    <i class="fas fa-arrow-right me-2"></i> Continue
                                </a>
                            </div>
                        </div>

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
        </div>
    </section>

    {{-- Loading Overlay --}}
    <div id="loadingOverlay" class="d-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    {{-- Mobile Sticky Checkout Bar --}}
    <div id="mobileCheckoutBar" class="mobile-checkout-bar d-lg-none" style="display: none;">
        <div class="mobile-checkout-bar-inner">
            <div class="mobile-checkout-info">
                <span class="mobile-checkout-total" id="mobileCartTotal">{{ $cart['formatted_total'] }}</span>
                <span class="mobile-checkout-rooms"><span id="mobileCartRooms">{{ $cart['total_rooms'] }}</span> room(s)</span>
            </div>
            <a href="{{ route('website.booking') }}" id="mobileContinueBtn" class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-right me-2"></i> Continue
            </a>
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
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
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }
        #loadingOverlay.d-none {
            display: none !important;
        }
        .select-room-btn.added {
            background-color: #198754 !important;
            border-color: #198754 !important;
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
        }
        .mobile-checkout-bar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 600px;
            margin: 0 auto;
        }
        .mobile-checkout-info {
            display: flex;
            flex-direction: column;
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
        @media (min-width: 992px) {
            .mobile-checkout-bar { display: none !important; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = '{{ csrf_token() }}';
            const cartAddUrl = '{{ route("website.cart.add") }}';
            const cartRemoveUrl = '{{ url("/website/cart/remove") }}';
            const cartClearUrl = '{{ route("website.cart.clear") }}';
            const cartGetUrl = '{{ route("website.cart.get") }}';

            // Elements
            const checkInInput = document.getElementById('checkIn');
            const checkOutInput = document.getElementById('checkOut');
            const searchForm = document.getElementById('searchForm');
            const loadingOverlay = document.getElementById('loadingOverlay');

            // Helpers
            const formatMoney = (amount) => '₦' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            const formatDate = (dateStr) => {
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            };

            const showLoading = () => loadingOverlay.classList.remove('d-none');
            const hideLoading = () => loadingOverlay.classList.add('d-none');

            // Update Cart UI
            function updateCartUI(cart) {
                const cartItems = document.getElementById('cartItems');
                const cartBadge = document.getElementById('cartBadge');
                const totalRooms = document.getElementById('totalRooms');
                const totalGuests = document.getElementById('totalGuests');
                const cartNights = document.getElementById('cartNights');
                const cartTotal = document.getElementById('cartTotal');
                const clearBtn = document.getElementById('clearCartBtn');
                const continueBtn = document.getElementById('continueBtn');

                // Update badge
                cartBadge.textContent = cart.total_rooms;

                // Update mobile checkout bar
                const mobileBar = document.getElementById('mobileCheckoutBar');
                const mobileTotal = document.getElementById('mobileCartTotal');
                const mobileRooms = document.getElementById('mobileCartRooms');
                const globalBar = document.querySelector('.mobile-sticky-bar');

                if (cart.items.length > 0) {
                    mobileBar.style.display = 'block';
                    mobileTotal.textContent = cart.formatted_total;
                    mobileRooms.textContent = cart.total_rooms;
                    if (globalBar) globalBar.style.display = 'none';
                } else {
                    mobileBar.style.display = 'none';
                    if (globalBar) globalBar.style.display = '';
                }

                // Update summary
                totalRooms.textContent = cart.total_rooms;
                totalGuests.textContent = cart.total_guests;
                cartNights.textContent = cart.nights || '-';
                cartTotal.textContent = cart.formatted_total;

                // Update items
                if (cart.items.length === 0) {
                    cartItems.innerHTML = `
                        <div id="emptyCartMessage" class="text-center py-4 text-muted">
                            <i class="fas fa-bed fa-2x mb-2 d-block opacity-50"></i>
                            <h6 class="fw-bold">No rooms selected yet</h6>
                            <p class="small mb-0">Start selecting rooms to build your reservation</p>
                        </div>
                    `;
                    clearBtn.disabled = true;
                    continueBtn.classList.add('disabled');
                } else {
                    let html = '';
                    cart.items.forEach(item => {
                        html += `
                            <div class="cart-item d-flex justify-content-between align-items-start py-2 border-bottom" 
                                 data-room-id="${item.room_type_id}">
                                <div class="flex-grow-1">
                                    <div class="fw-bold small">${item.room_type_name}</div>
                                    <div class="text-muted small">
                                        ${item.quantity} room × ${item.nights} nights
                                    </div>
                                    <div class="text-success small fw-bold">
                                        ${formatMoney(item.subtotal)}
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-cart-item"
                                        data-room-id="${item.room_type_id}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                    });
                    cartItems.innerHTML = html;
                    clearBtn.disabled = false;
                    continueBtn.classList.remove('disabled');

                    // Re-attach remove listeners
                    attachRemoveListeners();
                }

                // Update select buttons state
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

            // Add to cart
            function addToCart(roomId, quantity) {
                const checkIn = checkInInput.value;
                const checkOut = checkOutInput.value;
                const adults = parseInt(document.getElementById('adults')?.value || 1);
                const children = parseInt(document.getElementById('children')?.value || 0);

                if (!checkIn || !checkOut) {
                    alert('Please select check-in and check-out dates first.');
                    return;
                }

                showLoading();

                fetch(cartAddUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        room_type_id: roomId,
                        quantity: quantity,
                        check_in: checkIn,
                        check_out: checkOut,
                        adults: adults,
                        children: children,
                    })
                })
                .then(r => r.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        updateCartUI(data.cart);
                    } else {
                        alert(data.message || 'Could not add room to cart.');
                    }
                })
                .catch(err => {
                    hideLoading();
                    console.error('Cart add error:', err);
                    alert('An error occurred. Please try again.');
                });
            }

            // Remove from cart
            function removeFromCart(roomId) {
                showLoading();

                fetch(`${cartRemoveUrl}/${roomId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                })
                .then(r => r.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        updateCartUI(data.cart);
                    }
                })
                .catch(err => {
                    hideLoading();
                    console.error('Cart remove error:', err);
                });
            }

            // Clear cart
            function clearCart() {
                if (!confirm('Are you sure you want to clear your cart?')) return;

                showLoading();

                fetch(cartClearUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                })
                .then(r => r.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        updateCartUI(data.cart);
                    }
                })
                .catch(err => {
                    hideLoading();
                    console.error('Cart clear error:', err);
                });
            }

            // Attach remove listeners
            function attachRemoveListeners() {
                document.querySelectorAll('.remove-cart-item').forEach(btn => {
                    btn.addEventListener('click', function() {
                        removeFromCart(this.dataset.roomId);
                    });
                });
            }

            // Select room button click
            document.querySelectorAll('.select-room-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const roomId = this.dataset.roomId;
                    const quantitySelect = document.querySelector(`.room-quantity-select[data-room-id="${roomId}"]`);
                    const quantity = parseInt(quantitySelect.value) || 1;
                    addToCart(roomId, quantity);
                });
            });

            // Clear cart button
            document.getElementById('clearCartBtn').addEventListener('click', clearCart);

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
                window.location.href = `{{ route('website.book') }}?check_in=${checkIn}&check_out=${checkOut}&adults=${adults}&children=${children}`;
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

            // Initial attach for remove buttons
            attachRemoveListeners();

            // Fetch current cart state on load
            fetch(cartGetUrl, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    updateCartUI(data.cart);
                }
            });

            // Hide global mobile bar on this page if cart already has items
            @if(!empty($cart['items']))
                const globalBar = document.querySelector('.mobile-sticky-bar');
                if (globalBar) globalBar.style.display = 'none';
            @endif
        });
    </script>
@endsection
