@php
    $bookingSteps = $useCartFlow
        ? [
            ['label' => 'Guest', 'icon' => 'user'],
            ['label' => 'ID', 'icon' => 'id-card'],
            ['label' => 'Options', 'icon' => 'users'],
            ['label' => 'Review', 'icon' => 'clipboard-check'],
            ['label' => 'Payment', 'icon' => 'credit-card'],
        ]
        : [
            ['label' => 'Dates & Room', 'icon' => 'calendar-alt'],
            ['label' => 'Guest', 'icon' => 'user'],
            ['label' => 'ID', 'icon' => 'id-card'],
            ['label' => 'Options', 'icon' => 'users'],
            ['label' => 'Review', 'icon' => 'clipboard-check'],
            ['label' => 'Payment', 'icon' => 'credit-card'],
        ];
@endphp

<div class="booking-progress-bar" aria-hidden="true">
    <div class="fill" id="bookingProgressFill"></div>
</div>
<div class="progress-caption" id="bookingProgressCaption"></div>

<div class="step-indicator" id="stepIndicator">
    @foreach ($bookingSteps as $i => $step)
        @if ($i > 0)
            <div class="step-connector"></div>
        @endif
        <div class="step-item {{ $i === 0 ? 'active' : '' }}" data-step="{{ $i + 1 }}">
            <div class="step-dot">{{ $i + 1 }}</div>
            <div class="step-label">{{ $step['label'] }}</div>
        </div>
    @endforeach
</div>

