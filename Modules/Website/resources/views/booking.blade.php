@extends('website::layouts.master')

@section('title', 'Complete Your Reservation')

@include('website::booking.partials.styles')

@section('content')
    @include('website::booking.partials.hero')

    <div class="container pb-5">

        @php
            $useCartFlow = isset($useCart) && $useCart && !empty($cart['items']);

            $reqRoomTypeId = old(
                'room_type_id',
                request('room_type_id', request('room_id', $selectedRoomType->id ?? '')),
            );
            $reqCheckIn = $useCartFlow
                ? $cart['check_in'] ?? ''
                : old('check_in_date', request('check_in_date', request('check_in')));
            $reqCheckOut = $useCartFlow
                ? $cart['check_out'] ?? ''
                : old('check_out_date', request('check_out_date', request('check_out')));
            $hasPhone = Auth::check() && $guest && !empty($guest->contact_number);
            $hasGender = Auth::check() && $guest && !empty($guest->gender);
            $hasAddress = Auth::check() && $guest && !empty($guest->home_address);
            $hasIdType = Auth::check() && $guest && !empty($guest->identification_type);
            $hasIdNumber = Auth::check() && $guest && !empty($guest->identification_number);
            $hasNationality = Auth::check() && $guest && !empty($guest->nationality);
            $hasDob = Auth::check() && $guest && !is_null($guest->birthday);

            $initialPrice = $selectedRoomType->display_price ?? 0;
            $initialCapacity = $selectedRoomType->capacity ?? 0;
            $cartMaxGuests = $useCartFlow
                ? collect($cart['items'] ?? [])->sum(
                    fn ($item) => (int) ($item['capacity'] ?? 0) * (int) ($item['quantity'] ?? 1)
                )
                : $initialCapacity;
            $initialBaseOccupancy = (int) ($selectedRoomType->base_occupancy ?? 2);
            if ($initialCapacity > 0) {
                $initialBaseOccupancy = min($initialBaseOccupancy, $initialCapacity);
            }
            $initialNights = 1;
            if ($reqCheckIn && $reqCheckOut) {
                $diff = \Carbon\Carbon::parse($reqCheckIn)->diffInDays(\Carbon\Carbon::parse($reqCheckOut));
                $initialNights = max(1, $diff);
            }

            $initialBaseTotal = $initialPrice * $initialNights;
            $initialGuestFee = 0;
            $initialGuestFeeBreakdown = '';
            $initialTotal = $initialBaseTotal;
            $hasSelectedRoom = (!empty($reqRoomTypeId) && $initialCapacity > 0) || $useCartFlow;

            if ($selectedRoomType && $reqCheckIn && $reqCheckOut) {
                $rateService = app(\Modules\Website\Services\WebsiteRateService::class);
                $rateResult = $rateService->calculateWithGuests(
                    $selectedRoomType,
                    $reqCheckIn,
                    $reqCheckOut,
                    (int) old('adults', 1),
                    (int) old('children', 0)
                );
                $initialPrice = $rateResult['price_per_night'];
                $initialBaseTotal = $rateResult['base_total'];
                $initialGuestFee = $rateResult['guest_fee_total'];
                $initialGuestFeeBreakdown = $rateResult['guest_fee_breakdown'];
                $initialTotal = $rateResult['total'];
            }
        @endphp

        <div class="row g-4">
            <div class="col-lg-8">
                @include('website::booking.partials.progress')
                @include('website::booking.partials.alerts')
                @include('website::booking.partials.booking-form')
            </div>

            <div class="col-lg-4">
                @include('website::booking.partials.summary')
            </div>
        </div>
    </div>
@endsection

@include('website::booking.partials.config')
