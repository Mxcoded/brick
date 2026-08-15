@push('scripts')
    <script>
        window.bookingFormConfig = {{ Js::from([
            'checkAvailabilityUrl' => route('website.room.checkAvailability', [], false),
            'checkEmailUrl' => route('website.checkEmail', [], false),
            'availableUnitsUrl' => route('website.api.available-units', [], false),
            'saveDraftUrl' => route('website.booking.draft', [], false),
            'cartAddonUrl' => route('website.cart.addon', [], false),
            'cartAddonRemoveUrl' => route('website.cart.addon-remove', ['addonId' => '__ID__'], false),
            'csrfToken' => csrf_token(),
        ]) }};
    </script>
@endpush
