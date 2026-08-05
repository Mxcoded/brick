@push('scripts')
    <script>
        window.bookingFormConfig = {{ Js::from([
            'checkAvailabilityUrl' => route('website.room.checkAvailability', [], false),
            'checkEmailUrl' => route('website.checkEmail', [], false),
            'availableUnitsUrl' => route('website.api.available-units', [], false),
            'saveDraftUrl' => route('website.booking.draft', [], false),
            'csrfToken' => csrf_token(),
        ]) }};
    </script>
    @vite(['resources/js/booking-form.js'])
@endpush
