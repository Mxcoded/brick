<div class="card-summary shadow-sm">
    <div class="card-header">
        <h5><i class="fas fa-receipt me-2"></i>Booking Summary</h5>
    </div>

    @if ($useCartFlow)
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
    @else
        <img id="summary-image"
            src="{{ $selectedRoomType->image_url ?? asset('images/default-room.jpg') }}"
            class="card-img-top {{ $selectedRoomType ? '' : 'd-none' }}"
            style="height: 180px; object-fit: cover;" alt="{{ $selectedRoomType->name ?? 'Selected room' }}">

        <div class="card-body">
            <div class="text-center mb-3">
                <h5 id="summary-name" class="fw-bold mb-1" style="color: var(--brand-dark);">
                    {{ $selectedRoomType->name ?? 'Select a Room Type' }}
                </h5>
                <div class="small text-muted">
                    <i class="fas fa-user-friends me-1"></i> Max <span
                        id="summary-capacity">{{ $selectedRoomType->capacity ?? '-' }}</span> Guests
                </div>
            </div>

            <hr class="my-3">

            <div class="d-flex justify-content-between small mb-2">
                <span class="text-muted">Check-in</span>
                <span class="fw-bold"
                    id="summary-checkin">{{ $reqCheckIn ? \Carbon\Carbon::parse($reqCheckIn)->format('M d, Y') : '...' }}</span>
            </div>
            <div class="d-flex justify-content-between small mb-2">
                <span class="text-muted">Check-out</span>
                <span class="fw-bold"
                    id="summary-checkout">{{ $reqCheckOut ? \Carbon\Carbon::parse($reqCheckOut)->format('M d, Y') : '...' }}</span>
            </div>
            <div class="d-flex justify-content-between small mb-2">
                <span class="text-muted">Nights</span>
                <span class="fw-bold" id="summary-nights">{{ $initialNights }}</span>
            </div>
            <div class="d-flex justify-content-between small mb-2">
                <span class="text-muted">Rate</span>
                <span id="summary-rate">₦{{ number_format($selectedRoomType->display_price ?? 0, 2) }}
                    <span class="text-muted">/ night</span></span>
            </div>
            <div class="d-flex justify-content-between small mb-2">
                <span class="text-muted">Guests</span>
                <span class="fw-bold" id="summary-guests">1 Adult</span>
            </div>
            <div class="d-flex justify-content-between small mb-2">
                <span class="text-muted">Room Cost
                    ({{ $initialNights }} {{ Str::plural('night', $initialNights) }})</span>
                <span class="fw-bold" id="summary-base-total">₦{{ number_format($initialBaseTotal, 2) }}</span>
            </div>
            <div class="{{ $initialGuestFee > 0 ? '' : 'd-none' }}" id="guest-fee-row">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Extra Guest Fee</span>
                    <span class="fw-bold" id="summary-guest-fee">₦{{ number_format($initialGuestFee, 2) }}</span>
                </div>
                <div class="text-muted small mb-2"
                    style="font-size: 0.72rem; padding-left: 0.5rem; border-left: 2px solid var(--brand-gold-light);"
                    id="guest-fee-breakdown">{!! $initialGuestFeeBreakdown !!}</div>
            </div>

            <hr class="my-3">

            <div class="summary-total-row">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold small">Total</span>
                    <span class="amount" id="summary-total">₦{{ number_format($initialTotal, 2) }}</span>
                </div>
            </div>

            <span id="summaryTotalLive" class="visually-hidden" aria-live="polite"></span>

            <p class="text-muted small mt-3 mb-0">
                <i class="fas fa-info-circle me-1"></i> Specific room assigned at check-in
            </p>
        </div>
    @endif
</div>
