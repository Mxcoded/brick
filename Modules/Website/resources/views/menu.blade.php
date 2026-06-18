<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $dining->name }} — Menu — {{ config('app.name') }}</title>
    @vite(['resources/sass/app.scss'])
    <style>
        body { background: #f5f7fb; margin: 0; font-family: system-ui, -apple-system, sans-serif; }
        .menu-header { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 1.5rem; text-align: center; color: #fff; }
        .menu-header h2 { color: #C8A165; font-weight: 700; letter-spacing: 2px; margin: 0; font-size: 1.5rem; }
        .menu-header small { color: rgba(255,255,255,0.5); letter-spacing: 3px; text-transform: uppercase; font-size: 0.7rem; }
        .menu-header .venue-name { color: #fff; font-size: 1rem; margin-top: 0.25rem; }
        .menu-iframe { width: 100%; height: calc(100vh - 90px); border: none; }
    </style>
</head>
<body>
    <div class="menu-header">
        <h2>BRICKSPOINT</h2>
        <small>On-site Restaurant</small>
        <div class="venue-name"><i class="fas fa-utensils me-1" style="color: #C8A165;"></i> {{ $dining->name }}</div>
    </div>

    @if($dining->menu_pdf)
        <iframe src="{{ $dining->menu_pdf }}#view=FitH" class="menu-iframe" title="{{ $dining->name }} Menu"></iframe>
    @else
        <div class="d-flex align-items-center justify-content-center" style="height: calc(100vh - 90px);">
            <div class="text-center text-muted">
                <i class="fas fa-book-open fa-3x mb-3 opacity-25"></i>
                <h5>No Menu Available</h5>
                <p class="small">The menu for {{ $dining->name }} is not uploaded yet.</p>
                <a href="{{ route('website.dining') }}" class="btn btn-outline-dark btn-sm">Back to Dining</a>
            </div>
        </div>
    @endif
</body>
</html>
