<div class="card-summary shadow-sm">
    <div class="card-header">
        <h5><i class="fas fa-receipt me-2"></i>Booking Summary</h5>
    </div>

    @php
        $hasRoom = ! is_null($roomType);
        $nights = ($hasRoom && $checkIn && $checkOut)
            ? max(1, \Carbon\Carbon::parse($checkIn)->diffInDays(\Carbon\Carbon::parse($checkOut)))
            : 1;
        $pricePerNight = $rate['price_per_night'] ?? 0;
        $baseTotal = $rate['base_total'] ?? ($pricePerNight * $nights);
        $guestFee = $rate['guest_fee_total'] ?? 0;
        $guestFeeBreakdown = $rate['guest_fee_breakdown'] ?? '';
        $total = $rate['total'] ?? $baseTotal;

        $guestsParts = [];
        if ($adults > 0) {
            $guestsParts[] = $adults.' '.Str::plural('Adult', $adults);
        }
        if ($children > 0) {
            $guestsParts[] = $children.' '.Str::plural('Child', $children);
        }
        $guestsLabel = implode(', ', $guestsParts) ?: '1 Adult';
    @endphp

    <img src="{{ $roomType->image_url ?? asset('images/default-room.jpg') }}"
        class="card-img-top {{ $hasRoom ? '' : 'd-none' }}"
        style="height: 180px; object-fit: cover;" alt="{{ $roomType->name ?? 'Selected room' }}">

    <div class="card-body">
        <div class="text-center mb-3">
            <h5 class="fw-bold mb-1" style="color: var(--brand-dark);">
                {{ $roomType->name ?? 'Select a Room Type' }}
            </h5>
            <div class="small text-muted">
                <i class="fas fa-user-friends me-1"></i> Max
                {{ $roomType->capacity ?? '-' }} Guests
            </div>
        </div>

        <hr class="my-3">

        <div class="d-flex justify-content-between small mb-2">
            <span class="text-muted">Check-in</span>
            <span class="fw-bold">{{ $checkIn ? \Carbon\Carbon::parse($checkIn)->format('M d, Y') : '...' }}</span>
        </div>
        <div class="d-flex justify-content-between small mb-2">
            <span class="text-muted">Check-out</span>
            <span class="fw-bold">{{ $checkOut ? \Carbon\Carbon::parse($checkOut)->format('M d, Y') : '...' }}</span>
        </div>
        <div class="d-flex justify-content-between small mb-2">
            <span class="text-muted">Nights</span>
            <span class="fw-bold">{{ $hasRoom ? $nights : '—' }}</span>
        </div>
        <div class="d-flex justify-content-between small mb-2">
            <span class="text-muted">Rate</span>
            <span>₦{{ number_format($pricePerNight, 2) }}
                <span class="text-muted">/ night</span></span>
        </div>
        <div class="d-flex justify-content-between small mb-2">
            <span class="text-muted">Guests</span>
            <span class="fw-bold">{{ $guestsLabel }}</span>
        </div>
        <div class="d-flex justify-content-between small mb-2">
            <span class="text-muted">Room Cost ({{ $hasRoom ? $nights : 1 }}
                {{ Str::plural('night', $hasRoom ? $nights : 1) }})</span>
            <span class="fw-bold">₦{{ number_format($baseTotal, 2) }}</span>
        </div>

        @if ($guestFee > 0)
            <div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Extra Guest Fee</span>
                    <span class="fw-bold">₦{{ number_format($guestFee, 2) }}</span>
                </div>
                <div class="text-muted small mb-2"
                    style="font-size: 0.72rem; padding-left: 0.5rem; border-left: 2px solid var(--brand-gold-light);">
                    {!! $guestFeeBreakdown !!}
                </div>
            </div>
        @endif

        <hr class="my-3">

        <div class="summary-total-row">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold small">Total</span>
                <span class="amount">₦{{ number_format($total, 2) }}</span>
            </div>
        </div>

        <p class="text-muted small mt-3 mb-0">
            <i class="fas fa-info-circle me-1"></i> Specific room assigned at check-in
        </p>
    </div>
</div>
