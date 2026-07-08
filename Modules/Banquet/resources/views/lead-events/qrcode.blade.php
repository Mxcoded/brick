@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banquet.index') }}">Banquet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('banquet.lead-events.index') }}">Lead Events</a></li>
    <li class="breadcrumb-item active">QR Code — {{ $event->title }}</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 banquet-theme">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-charcoal">
                <i class="fas fa-qrcode me-2 text-gold"></i>{{ $event->title }}
            </h1>
            <p class="text-muted mb-0">Scan to open the event interest form</p>
        </div>
        <a href="{{ route('banquet.lead-events.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-5">
                    <div class="mb-4 d-inline-block p-3 bg-white rounded shadow-sm">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=350x350&data={{ urlencode($url) }}"
                             alt="QR Code for {{ $event->title }}"
                             class="img-fluid"
                             style="max-width: 350px; image-rendering: pixelated;">
                    </div>

                    <p class="text-muted small mb-3">
                        <i class="fas fa-link me-1"></i>
                        <a href="{{ $url }}" target="_blank" class="text-decoration-none">{{ $url }}</a>
                    </p>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&data={{ urlencode($url) }}"
                           download="qrcode-{{ $event->slug }}.png"
                           class="btn btn-gold px-4">
                            <i class="fas fa-download me-1"></i> Download PNG
                        </a>
                        <button class="btn btn-outline-secondary px-4" onclick="navigator.clipboard.writeText('{{ $url }}').then(() => { this.innerHTML='<i class=\'fas fa-check me-1\'></i> Copied!'; setTimeout(() => location.reload(), 1500); })">
                            <i class="fas fa-copy me-1"></i> Copy Link
                        </button>
                    </div>
                </div>

                <div class="card-footer bg-white border-top-0 text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Attendees scan this QR code to access the registration form.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.banquet-theme .text-gold { color: #C8A165 !important; }
.btn-gold { background-color: #C8A165; border-color: #C8A165; color: #fff; }
.btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #fff; }
</style>
@endsection
