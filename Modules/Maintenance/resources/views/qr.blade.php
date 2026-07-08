@extends('layouts.base')

@section('styles')
<style>
    body { background: #f8f9fa; }
    .qr-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .qr-card { background: #fff; border-radius: 20px; box-shadow: 0 8px 40px rgba(0,0,0,0.1); padding: 3rem; text-align: center; max-width: 500px; width: 100%; }
    .brand-mark { font-family: 'Proxima Nova', Arial, sans-serif; font-weight: 800; font-size: 1.8rem; color: #C8A165; letter-spacing: -0.5px; }
    .qr-title { font-weight: 700; margin: 1rem 0 0.5rem; }
    .qr-sub { color: #888; margin-bottom: 1.5rem; }
    .qr-img { width: 250px; height: 250px; margin: 0 auto 1.5rem; }
    .qr-img img { width: 100%; height: 100%; object-fit: contain; }
    .qr-url { background: #f5f5f5; border-radius: 10px; padding: 12px 16px; word-break: break-all; font-size: 0.9rem; color: #555; }
    .btn-print { background: #C8A165; color: #fff; border: none; border-radius: 10px; padding: 12px 32px; font-weight: 600; cursor: pointer; margin-top: 1rem; }
    .btn-print:hover { background: #b08c54; color: #fff; }
    @media print { .btn-print, .no-print { display: none !important; } }
</style>
@endsection

@section('content')
<div class="qr-page">
    <div class="qr-card" id="printArea">
        <div class="brand-mark">BRICKSPOINT&trade;</div>
        <h3 class="qr-title"><i class="fas fa-tools me-2" style="color: #C8A165;"></i>Report an Issue</h3>
        <p class="qr-sub">Scan to report a maintenance or facility issue</p>

        <div class="qr-img">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($url) }}"
                 alt="QR Code for maintenance reporting">
        </div>

        <div class="qr-url">{{ $url }}</div>

        <div class="no-print d-flex gap-2 justify-content-center">
            <button class="btn-print" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
            <button class="btn-print" style="background: #6c757d;" onclick="navigator.clipboard.writeText('{{ $url }}').then(function(){ alert('Link copied!'); })">
                <i class="fas fa-copy me-1"></i> Copy Link
            </button>
        </div>
    </div>
</div>
@endsection
