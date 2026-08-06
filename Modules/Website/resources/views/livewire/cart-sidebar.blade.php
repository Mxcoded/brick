<div class="cart-sidebar sticky-top" style="top: 100px;" {{ $attributes }}>
    <div class="card border-0 shadow">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-shopping-cart me-2"></i>
                <span class="badge bg-primary rounded-pill ms-1">{{ $cart['total_rooms'] }}</span>
            </h5>
            <span class="small">Selected Rooms</span>
        </div>

        <div class="card-body p-0">
            <div id="cartItems" class="p-3" wire:loading.class="opacity-50">
                @forelse ($cart['items'] as $item)
                    <div class="cart-item d-flex justify-content-between align-items-start py-2 border-bottom"
                         wire:key="cart-item-{{ $item['room_type_id'] }}">
                        <div class="flex-grow-1">
                            <div class="fw-bold small">{{ $item['room_type_name'] }}</div>
                            <div class="text-muted small">
                                {{ $item['quantity'] }} room &times; {{ $item['nights'] }} nights
                            </div>
                            <div class="text-success small fw-bold">
                                ₦{{ number_format($item['subtotal'], 2) }}
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0"
                                wire:click="remove({{ $item['room_type_id'] }})"
                                wire:loading.attr="disabled"
                                aria-label="Remove {{ $item['room_type_name'] }} from cart">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-bed fa-2x mb-2 d-block opacity-50"></i>
                        <h6 class="fw-bold">No rooms selected yet</h6>
                        <p class="small mb-0">Start selecting rooms to build your reservation</p>
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
                <div class="d-flex justify-content-between">
                    <span class="h6 mb-0">Total</span>
                    <span class="h5 text-success fw-bold mb-0">{{ $cart['formatted_total'] }}</span>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white py-3">
            <button type="button" wire:click="clear"
                    wire:confirm="Are you sure you want to clear your cart?"
                    wire:loading.attr="disabled"
                    class="btn btn-outline-danger btn-sm w-100 mb-2"
                    {{ empty($cart['items']) ? 'disabled' : '' }}>
                <i class="fas fa-trash me-1"></i> Clear All
            </button>
            <a href="{{ route('website.booking', [], false) }}" wire:loading.attr="aria-busy"
               class="btn btn-primary btn-lg w-100 {{ empty($cart['items']) ? 'disabled' : '' }}">
                <i class="fas fa-arrow-right me-2"></i> Continue
            </a>
        </div>
    </div>
</div>
