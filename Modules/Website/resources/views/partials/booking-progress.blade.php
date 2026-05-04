{{-- Booking Progress Indicator --}}
{{-- Usage: @include('website::partials.booking-progress', ['step' => 1|2|3|4]) --}}
@php
    $steps = [
        1 => ['icon' => 'fa-calendar-check', 'label' => 'Select Dates'],
        2 => ['icon' => 'fa-bed', 'label' => 'Choose Room'],
        3 => ['icon' => 'fa-user', 'label' => 'Guest Details'],
        4 => ['icon' => 'fa-check-circle', 'label' => 'Confirmation'],
    ];
    $currentStep = $step ?? 1;
@endphp

<div class="booking-progress-container mb-4">
    <div class="booking-progress d-flex justify-content-between align-items-center">
        @foreach($steps as $num => $stepData)
            <div class="progress-step {{ $num < $currentStep ? 'completed' : '' }} {{ $num == $currentStep ? 'active' : '' }} {{ $num > $currentStep ? 'pending' : '' }}">
                <div class="step-icon">
                    @if($num < $currentStep)
                        <i class="fas fa-check"></i>
                    @else
                        <i class="fas {{ $stepData['icon'] }}"></i>
                    @endif
                </div>
                <span class="step-label d-none d-md-block">{{ $stepData['label'] }}</span>
            </div>
            @if($num < 4)
                <div class="progress-line {{ $num < $currentStep ? 'completed' : '' }}"></div>
            @endif
        @endforeach
    </div>
</div>
