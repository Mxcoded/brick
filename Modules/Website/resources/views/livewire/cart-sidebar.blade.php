<div class="cart-sidebar sticky-top" style="top: 100px;" {{ $attributes }}>
    <div class="card border-0 shadow">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center cart-header">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-shopping-cart me-2 text-primary"></i>
                <span class="badge rounded-pill ms-1 cart-badge">{{ $cart['total_rooms'] }}</span>
            </h5>
            <span class="small text-uppercase tracking-wide">Selected Rooms</span>
        </div>

        <div class="card-body p-0">
            <div id="cartItems" class="p-3" wire:loading.class="opacity-50">
                @forelse ($cart['items'] as $item)
                    <div class="cart-item d-flex justify-content-between align-items-start py-2 border-bottom"
                         wire:key="cart-item-{{ $item['room_type_id'] }}">
                        <div class="flex-grow-1">
                            <div class="fw-bold small">{{ $item['room_type_name'] }}</div>
                            <div class="text-muted small">
                                <i class="fas fa-moon me-1"></i>{{ $item['quantity'] }} room &times; {{ $item['nights'] }} nights
                            </div>
                            <div class="text-success small fw-bold">
                                ₦{{ number_format($item['subtotal'], 2) }}
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0"
                                wire:click="remove({{ $item['room_type_id'] }})"
                                wire:loading.attr="disabled"
                                aria-label="Remove {{ $item['room_type_name'] }} from cart">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>
                @empty
                    <div class="text-center py-5 px-2">
                        <div class="cart-empty-icon mx-auto mb-3">
                            <i class="fas fa-bed"></i>
                        </div>
                        <h6 class="fw-bold mb-1">No rooms selected yet</h6>
                        <p class="small text-muted mb-0">Pick a room from the list to build your stay</p>
                    </div>
                @endforelse
            </div>

            <div class="cart-summary bg-light p-3 border-top">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Total Rooms</span>
                    <span class="fw-bold">{{ $cart['total_rooms'] }}</span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Total Guests Capacity</span>
                    <span class="fw-bold">{{ $cart['total_guests'] }}</span>
                </div>
                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">Nights</span>
                    <span class="fw-bold">{{ $cart['nights'] ?: '-' }}</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="h6 mb-0">Total</span>
                    <span class="h5 fw-bold mb-0 cart-total">{{ $cart['formatted_total'] }}</span>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white py-3">
            <a href="{{ route('website.booking', [], false) }}" wire:loading.attr="aria-busy"
               class="btn btn-primary btn-lg w-100 mb-2 {{ empty($cart['items']) ? 'disabled' : '' }}">
                <i class="fas fa-arrow-right me-2"></i> Continue to Checkout
            </a>
            <div class="text-center small text-muted mb-2">
                <i class="fas fa-lock me-1"></i> No payment required yet
            </div>
            <button type="button" wire:click="clear"
                    wire:confirm="Are you sure you want to clear your cart?"
                    wire:loading.attr="disabled"
                    class="btn btn-outline-danger btn-sm w-100"
                    {{ empty($cart['items']) ? 'disabled' : '' }}>
                <i class="fas fa-trash me-1"></i> Clear All
            </button>
        </div>
    </div>
</div>
