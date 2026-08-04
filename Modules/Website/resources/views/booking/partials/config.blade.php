@push('scripts')
    <script>
        window.bookingFormConfig = {{ Js::from([
            'checkAvailabilityUrl' => route('website.room.checkAvailability', [], false),
            'updateGuestsUrl' => route('website.cart.update-guests', [], false),
            'checkEmailUrl' => route('website.checkEmail', [], false),
            'availableUnitsUrl' => route('website.api.available-units', [], false),
            'csrfToken' => csrf_token(),
        ]) }};
    </script>
    @vite(['resources/js/booking-form.js'])
@endpush
