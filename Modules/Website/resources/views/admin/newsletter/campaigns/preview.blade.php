@extends('layouts.master')

@section('title', 'Preview Campaign')

@section('page-content')
<style>
    :root {
        --bp-gold: #C8A165;
        --bp-gold-light: #D4B88A;
        --bp-charcoal: #333333;
    }

    .preview-header {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .device-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .device-btn {
        border-color: #dee2e6;
        color: #495057;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .device-btn.active {
        background-color: var(--bp-charcoal);
        border-color: var(--bp-charcoal);
        color: #ffffff;
    }

    .device-stage {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: center;
        gap: 40px;
        overflow-x: auto;
        padding-bottom: 10px;
    }

    .device-stage[data-mode="desktop"] .frame-mobile-wrap {
        display: none;
    }

    .device-stage[data-mode="mobile"] .frame-desktop-wrap {
        display: none;
    }

    .device-label {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #888;
        margin-bottom: 10px;
    }

    .device-label span {
        font-weight: 400;
        color: #aaa;
        text-transform: none;
        letter-spacing: 0;
    }

    /* Desktop / browser frame */
    .frame-desktop-wrap {
        flex: 1 1 480px;
        max-width: 720px;
        min-width: 320px;
    }

    .browser-frame {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        background: #ffffff;
    }

    .browser-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        height: 40px;
        padding: 0 16px;
        background: #eef0f2;
        border-bottom: 1px solid #dfe3e6;
    }

    .browser-bar .dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }

    .dot-red { background: #ff5f57; }
    .dot-yellow { background: #febc2e; }
    .dot-green { background: #28c840; }

    .browser-url {
        flex: 1;
        margin-left: 10px;
        height: 22px;
        line-height: 22px;
        padding: 0 12px;
        background: #ffffff;
        border-radius: 11px;
        font-size: 0.72rem;
        color: #888;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .desktop-iframe {
        display: block;
        width: 100%;
        height: 820px;
        border: 0;
        background: #ffffff;
    }

    /* Mobile / phone frame */
    .frame-mobile-wrap {
        flex: 0 0 auto;
    }

    .phone-frame {
        position: relative;
        width: 390px;
        max-width: 100%;
        padding: 12px;
        border-radius: 48px;
        background: #1a1a1a;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
    }

    .phone-notch {
        position: absolute;
        top: 12px;
        left: 50%;
        transform: translateX(-50%);
        width: 150px;
        height: 26px;
        background: #1a1a1a;
        border-radius: 0 0 16px 16px;
        z-index: 2;
    }

    .mobile-iframe {
        display: block;
        width: 100%;
        height: 780px;
        border: 0;
        border-radius: 36px;
        background: #ffffff;
    }

    @media (max-width: 575.98px) {
        .device-stage {
            justify-content: flex-start;
        }
    }
</style>

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="card preview-header mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <a href="{{ route('website.admin.newsletter.campaigns.index') }}" class="text-muted text-decoration-none mb-2 d-inline-block">
                        <i class="fas fa-arrow-left me-1"></i> Back to Campaigns
                    </a>
                    <h1 class="h4 fw-bold mb-1">
                        <i class="fas fa-eye me-2" style="color: var(--bp-gold);"></i>
                        {{ $campaign->subject }}
                    </h1>
                    <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                        <span class="badge bg-{{ $campaign->status_color }}">{{ ucfirst($campaign->status) }}</span>
                        <span><i class="fas fa-calendar me-1"></i>{{ $campaign->created_at->format('M d, Y') }}</span>
                        @if($campaign->author)
                            <span><i class="fas fa-user me-1"></i>{{ $campaign->author->name }}</span>
                        @endif
                    </div>
                </div>
                @if($campaign->canEdit())
                    <a href="{{ route('website.admin.newsletter.campaigns.edit', $campaign) }}" class="btn btn-outline-dark">
                        <i class="fas fa-edit me-1"></i> Edit Campaign
                    </a>
                @endif
            </div>

            @if($campaign->preview_text)
                <hr>
                <p class="mb-0 small text-muted">
                    <i class="fas fa-quote-left me-1"></i>
                    <strong>Preview text:</strong> {{ $campaign->preview_text }}
                </p>
            @endif
        </div>
    </div>

    {{-- Device toolbar --}}
    <div class="device-toolbar">
        <div class="btn-group" role="group" aria-label="Preview device">
            <button type="button" class="btn device-btn active" data-mode="both">
                <i class="fas fa-columns me-1"></i> Side by Side
            </button>
            <button type="button" class="btn device-btn" data-mode="desktop">
                <i class="fas fa-desktop me-1"></i> Desktop
            </button>
            <button type="button" class="btn device-btn" data-mode="mobile">
                <i class="fas fa-mobile-alt me-1"></i> Mobile
            </button>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="refreshBtn">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
            <a href="{{ route('website.admin.newsletter.campaigns.preview.email', $campaign) }}"
               target="_blank" class="btn btn-outline-dark btn-sm">
                <i class="fas fa-external-link-alt me-1"></i> Open Raw Email
            </a>
        </div>
    </div>

    {{-- Device frames --}}
    <div class="device-stage" id="deviceStage" data-mode="both">
        <div class="frame-desktop-wrap">
            <div class="device-label"><i class="fas fa-desktop me-1"></i> Desktop <span>&middot; 640px+</span></div>
            <div class="browser-frame">
                <div class="browser-bar">
                    <span class="dot dot-red"></span>
                    <span class="dot dot-yellow"></span>
                    <span class="dot dot-green"></span>
                    <div class="browser-url">{{ $campaign->subject }}</div>
                </div>
                <iframe class="device-iframe desktop-iframe"
                        src="{{ route('website.admin.newsletter.campaigns.preview.email', $campaign) }}"
                        title="Desktop email preview"></iframe>
            </div>
        </div>

        <div class="frame-mobile-wrap">
            <div class="device-label"><i class="fas fa-mobile-alt me-1"></i> Mobile <span>&middot; 390px</span></div>
            <div class="phone-frame">
                <div class="phone-notch"></div>
                <iframe class="device-iframe mobile-iframe"
                        src="{{ route('website.admin.newsletter.campaigns.preview.email', $campaign) }}"
                        title="Mobile email preview"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const stage = document.getElementById('deviceStage');

    document.querySelectorAll('.device-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.device-btn').forEach(function (b) {
                b.classList.remove('active');
            });
            btn.classList.add('active');
            stage.setAttribute('data-mode', btn.dataset.mode);
        });
    });

    document.getElementById('refreshBtn').addEventListener('click', function () {
        document.querySelectorAll('.device-iframe').forEach(function (frame) {
            try {
                frame.contentWindow.location.reload();
            } catch (e) {
                frame.src = frame.src;
            }
        });
    });
});
</script>
@endsection
