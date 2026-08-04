<div class="card-summary shadow-sm">
    <div class="card-header">
        <h5><i class="fas fa-receipt me-2"></i>Booking Summary</h5>
    </div>

    <div class="card-body">
        <div class="d-flex justify-content-between mb-3 small">
            <span class="text-muted">Check-in</span>
            <span
                class="fw-bold">{{ \Carbon\Carbon::parse($cart['check_in'])->format('M d, Y') }}</span>
        </div>
        <div class="d-flex justify-content-between mb-3 small">
            <span class="text-muted">Check-out</span>
            <span
                class="fw-bold">{{ \Carbon\Carbon::parse($cart['check_out'])->format('M d, Y') }}</span>
        </div>

        <hr class="my-3">

        <div class="fw-bold small mb-3" style="color: var(--brand-gold);">SELECTED ROOMS</div>
        @foreach ($cart['items'] as $item)
            <div class="summary-room-item">
                <div class="d-flex align-items-start gap-3">
                    <div class="room-icon"><i class="fas fa-bed"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-bold small">{{ $item['room_type_name'] }}</div>
                        <div class="text-muted small">{{ $item['quantity'] }} room &times;
                            {{ $item['nights'] }} nights</div>
                    </div>
                    <div class="fw-bold" style="color: #16a34a; font-size: 0.9rem;">
                        ₦{{ number_format($item['subtotal'], 2) }}</div>
                </div>
            </div>
        @endforeach

        <div class="summary-total-row mt-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold small">Total</div>
                    <div class="text-muted" style="font-size: 0.75rem;">{{ $cart['total_rooms'] }}
                        room(s), {{ $cart['nights'] }} nights</div>
                </div>
                <span class="amount">{{ $cart['formatted_total'] }}</span>
            </div>
        </div>

        <p class="text-muted small mt-3 mb-0">
            <i class="fas fa-info-circle me-1"></i> Rooms assigned at check-in
        </p>
    </div>
</div>
