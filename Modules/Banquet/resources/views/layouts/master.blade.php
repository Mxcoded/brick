<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>@yield('title', 'Banquet') — {{ config('app.name', 'BRICKSPOINT ERP') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    @vite(['resources/sass/app.scss'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">



    <meta name="description" content="{{ $description ?? '' }}">
    <meta name="keywords" content="{{ $keywords ?? '' }}">
    <meta name="author" content="{{ $author ?? '' }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    {{-- Vite CSS --}}
    {{-- {{ module_vite('build-staff', 'resources/assets/sass/app.scss', storage_path('vite.hot')) }} --}}
    <style>
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }
    .badge {
        font-size: 0.9em;
        padding: 0.5em 0.75em;
    }
.card {
    border-radius: 0.75rem;
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.form-floating > label {
    color: #6c757d;
    padding: 0.5rem 1rem;
}

.form-control, .form-select {
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
}

.input-group .btn {
    border-radius: 0 0.5rem 0.5rem 0;
}

.card {
    border-radius: 0.75rem;
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.form-check-input {
    width: 1.2em;
    height: 1.2em;
    margin-top: 0.15em;
}

.invalid-feedback {
    font-size: 0.85rem;
}

.alert {
    border-radius: 0.5rem;
}
.summary-item.total-revenue p {
    font-size: 24px;
    color: #28a745;
}

</style>

</head>

<body>
  <div class="container mt-4">
   
    @yield('content')
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.phone-input').each(function () {
                var input = this;
                var hidden = $(input).data('hidden');
                var iti = window.intlTelInput(input, {
                    initialCountry: 'ng',
                    separateDialCode: true,
                    geoIpLookup: false,
                    utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js',
                });
                if (hidden) {
                    $(input).on('blur change', function () {
                        $('#' + hidden).val(iti.getNumber());
                    });
                }
                $(input).on('blur', function () {
                    if (iti.isValidNumber()) {
                        $(input).removeClass('is-invalid').addClass('is-valid');
                    } else if ($(input).val().trim() !== '') {
                        $(input).removeClass('is-valid').addClass('is-invalid');
                    } else {
                        $(input).removeClass('is-valid is-invalid');
                    }
                });
                if ($(input).val().trim() !== '') {
                    $(input).trigger('blur');
                }
            });
        });
    </script>
    @yield('scripts')
</body>
