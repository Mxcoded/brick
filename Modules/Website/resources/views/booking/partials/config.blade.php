@push('scripts')
    <script>
        window.bookingFormConfig = {{ Js::from([
            'checkAvailabilityUrl' => route('website.room.checkAvailability'),
            'updateGuestsUrl' => route('website.cart.update-guests'),
            'checkEmailUrl' => route('website.checkEmail'),
            'roomRateUrl' => route('website.api.room-rate'),
            'availableUnitsUrl' => route('website.api.available-units'),
            'csrfToken' => csrf_token(),
        ]) }};
    </script>
    @vite(['resources/js/booking-form.js'])
@endpush
