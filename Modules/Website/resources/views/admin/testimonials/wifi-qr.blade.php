@extends('layouts.master')

@section('title', 'Review QR Cards')

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fas fa-qrcode me-2" style="color: #C8A165;"></i>Review QR Cards
            </h1>
            <p class="text-muted mb-0">Stay, Dining and Event review codes. Print a card for each sales point and encourage guests to scan and leave feedback.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-gold px-4" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Cards
            </button>
            <a href="{{ route('website.admin.testimonials.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('website.admin.testimonials.wifi-qr') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold mb-1">Location / Branch</label>
                    <input type="text" name="branch" class="form-control" value="{{ $branch }}" list="known-branches"
                           placeholder="e.g. Asokoro" required>
                    <datalist id="known-branches">
                        @foreach ($branches as $known)
                            <option value="{{ $known }}"></option>
                        @endforeach
                    </datalist>
                    <div class="form-text">The branch is baked into the code and pre-fills the location on the review form.</div>
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-themed rounded-pill px-4">
                        <i class="fas fa-sync-alt me-1"></i> Generate
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 print-row">
        @php
            $labels = ['stay' => ['Stay', 'fa-bed'], 'restaurant' => ['Dining', 'fa-utensils'], 'event' => ['Event', 'fa-calendar-check']];
        @endphp
        @foreach ($urls as $type => $url)
            <div class="col-md-4 print-col">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-4 text-center">
                        <h5 class="fw-bold text-uppercase mb-1" style="color: var(--theme-heading);">
                            <i class="fas {{ $labels[$type][1] }} me-2" style="color: #C8A165;"></i>{{ $labels[$type][0] }} Review
                        </h5>
                        <div class="small text-muted mb-3">{{ $branch ? 'Scan to share your feedback · ' . $branch : 'Scan to share your feedback' }}</div>

                        <div class="mb-3 d-inline-block p-2 bg-white rounded shadow-sm">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data={{ urlencode($url) }}"
                                 alt="QR Code — {{ $labels[$type][0] }} review"
                                 class="img-fluid"
                                 style="max-width: 220px; image-rendering: pixelated;">
                        </div>

                        <p class="text-muted small mb-3 text-break">
                            <i class="fas fa-link me-1"></i>
                            <a href="{{ $url }}" target="_blank" class="text-decoration-none">{{ $url }}</a>
                        </p>

                        <div class="d-flex justify-content-center gap-2 no-print">
                            <a href="https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&data={{ urlencode($url) }}"
                               download="review-{{ $type }}.png"
                               class="btn btn-gold btn-sm px-3">
                                <i class="fas fa-download me-1"></i> Download
                            </a>
                            <button class="btn btn-outline-warning btn-sm px-3"
                                    onclick="navigator.clipboard.writeText('{{ $url }}').then(() => this.innerHTML='<i class=\'fas fa-check me-1\'></i> Copied!')">
                                <i class="fas fa-copy me-1"></i> Copy Link
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="alert alert-secondary mt-4 rounded-4 no-print d-flex gap-2 align-items-start mb-0">
        <i class="fas fa-info-circle mt-1"></i>
        <div class="small">
            Scanning a card opens the review form pre-selected for that type (Stay, Dining or Event) with the
            <strong>{{ $branch ?: 'branch' }}</strong> location pre-filled, so guests don't have to type it. Make a set of cards for each of your branches by changing the branch above.</div>
    </div>
</div>

<style>
.btn-gold { background-color: #C8A165; border-color: #C8A165; color: #fff; }
.btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #fff; }
@media print {
    .no-print { display: none !important; }
    .print-row { display: flex !important; }
    .print-col { flex: 0 0 33.3333%; max-width: 33.3333%; }
    body { background: #fff !important; }
}
</style>
@endsection