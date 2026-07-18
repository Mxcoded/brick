@php
    $themes = [
        'gold-legacy' => [
            'label' => 'Gold Legacy',
            'desc' => 'Warm gold accents on deep navy — the signature Brickspoint look.',
            'preview' => ['#C8A165', '#1e1e2d', '#f0f2f5'],
        ],
        'platinum-noir' => [
            'label' => 'Platinum Noir',
            'desc' => 'Monochromatic silver-on-black for a sleek, ultra-modern aesthetic.',
            'preview' => ['#E8E8E8', '#0D0D0D', '#121212'],
        ],
        'sapphire-regal' => [
            'label' => 'Sapphire Regal',
            'desc' => 'Deep sapphire blue with refined gold trim — timeless corporate luxury.',
            'preview' => ['#1B3A5C', '#0F1A2E', '#f0f2f5'],
        ],
    ];
@endphp

@extends('layouts.master')

@section('title', 'Appearance — Admin')

@section('styles')
<style>
    .card-header.bg-white {
        background-color: var(--theme-card-bg) !important;
    }
    .theme-card {
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
        border-radius: 1rem;
        overflow: hidden;
        position: relative;
    }
    .theme-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 40px rgba(0,0,0,0.12);
    }
    .theme-card.active {
        border-color: var(--sidebar-brand);
        box-shadow: 0 0 0 3px rgba(var(--theme-primary-rgb), 0.15), 0 12px 32px rgba(0,0,0,0.10);
    }
    .theme-card .active-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--sidebar-brand);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        opacity: 0;
        transform: scale(0.5);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        z-index: 2;
    }
    .theme-card.active .active-badge {
        opacity: 1;
        transform: scale(1);
    }
    .theme-preview-bar {
        height: 6px;
        flex: 1;
        transition: all 0.3s ease;
    }
    .theme-preview-sidebar {
        width: 64px;
        height: 48px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    .theme-preview-body {
        flex: 1;
        height: 48px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    .theme-preview-navbar {
        height: 10px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .theme-card .preview-wrapper {
        position: relative;
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .upload-zone {
        border: 2px dashed var(--theme-border);
        border-radius: 1rem;
        padding: 2rem;
        text-align: center;
        transition: all 0.25s ease;
        cursor: pointer;
        position: relative;
    }
    .upload-zone:hover,
    .upload-zone.dragover {
        border-color: var(--theme-primary);
        background: var(--theme-primary-light);
        transform: scale(1.01);
    }
    .upload-zone.has-image {
        border-style: solid;
        border-color: var(--theme-primary);
        background: rgba(var(--theme-primary-rgb), 0.04);
    }
    .logo-preview {
        max-height: 60px;
        width: auto;
        object-fit: contain;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }

    .preview-navbar-sim {
        border-radius: 0.75rem 0.75rem 0 0;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .preview-sidebar-sim {
        border-radius: 0 0 0 0.75rem;
        padding: 16px;
        width: 120px;
        min-height: 80px;
        flex-shrink: 0;
    }
    .preview-content-sim {
        flex: 1;
        border-radius: 0 0 0.75rem 0;
        padding: 16px;
    }

    .section-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--theme-primary-rgb), 0.12);
        color: var(--theme-primary-dark);
    }

    .btn-submit {
        position: relative;
        overflow: hidden;
    }
    .btn-submit .spinner-overlay {
        position: absolute;
        inset: 0;
        background: inherit;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .btn-submit.loading .spinner-overlay {
        opacity: 1;
    }
    .btn-submit.loading .btn-text {
        opacity: 0;
    }

    .toast-container-custom {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }
    .toast-custom {
        background: var(--theme-card-bg);
        border-left: 4px solid var(--theme-primary);
        border-radius: 0.75rem;
        box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideInRight 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        max-width: 380px;
    }
    @keyframes slideInRight {
        from { transform: translateX(120%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes fadeOut {
        to { opacity: 0; transform: translateX(40px); }
    }

    .file-info {
        font-size: 0.75rem;
        padding: 2px 10px;
        border-radius: 50px;
        background: rgba(var(--theme-primary-rgb), 0.08);
        color: var(--theme-text-muted);
        display: inline-block;
    }
</style>
@endsection

@section('page-content')

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="section-icon"><i class="fas fa-palette"></i></span>
            <div>
                <h4 class="fw-bold mb-0" style="color: var(--theme-heading);">Appearance</h4>
                <p class="text-muted mb-0 small">Customise the look and feel of your ERP system.</p>
            </div>
        </div>
        <span class="badge rounded-pill px-3 py-2" style="background: rgba(var(--theme-primary-rgb), 0.12); color: var(--theme-primary-dark); font-weight: 500;">
            <i class="fas fa-magic me-1"></i> {{ $themes[$theme]['label'] ?? 'Gold Legacy' }} active
        </span>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm d-flex align-items-center" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">

        {{-- Theme Selector --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom rounded-top-4 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold d-flex align-items-center gap-2" style="color: var(--theme-heading);">
                        <i class="fas fa-paint-roller" style="color: var(--theme-primary-dark);"></i>Theme
                    </h5>
                    <small class="text-muted">Click card to select</small>
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <form method="POST" action="{{ route('admin.appearance.update') }}" id="themeForm">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-4">
                            @foreach($themes as $key => $t)
                            <div class="col-md-4">
                                <div class="theme-card card border-0 shadow-sm p-0 {{ $theme === $key ? 'active' : '' }}"
                                     onclick="document.getElementById('theme_{{ $key }}').click()">

                                    <div class="active-badge"><i class="fas fa-check"></i></div>

                                    <div class="p-3">
                                        <div class="preview-wrapper mb-3" style="box-shadow: inset 0 0 0 1px rgba(0,0,0,0.06);">
                                            {{-- Simulated navbar --}}
                                            <div class="preview-navbar-sim d-flex align-items-center gap-2" style="background: {{ $t['preview'][0] }};">
                                                <span style="width:18px;height:18px;border-radius:4px;background:rgba(255,255,255,0.25);"></span>
                                                <span style="width:60px;height:8px;border-radius:4px;background:rgba(255,255,255,0.2);"></span>
                                            </div>
                                            {{-- Simulated sidebar + content --}}
                                            <div class="d-flex">
                                                <div class="preview-sidebar-sim" style="background: {{ $t['preview'][1] }};">
                                                    <span style="display:block;width:60%;height:6px;border-radius:3px;background:rgba(255,255,255,0.12);margin-bottom:8px;"></span>
                                                    <span style="display:block;width:40%;height:6px;border-radius:3px;background:rgba(255,255,255,0.08);margin-bottom:6px;"></span>
                                                    <span style="display:block;width:50%;height:6px;border-radius:3px;background:rgba(255,255,255,0.06);"></span>
                                                </div>
                                                <div class="preview-content-sim" style="background: {{ $t['preview'][2] }};">
                                                    <span style="display:block;width:70%;height:6px;border-radius:3px;background:rgba(0,0,0,0.08);margin-bottom:8px;"></span>
                                                    <span style="display:block;width:45%;height:6px;border-radius:3px;background:rgba(0,0,0,0.04);"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <h6 class="fw-bold mb-1" style="color: var(--theme-heading);">{{ $t['label'] }}</h6>
                                        <p class="small mb-2" style="color: var(--theme-text-muted); line-height: 1.4;">{{ $t['desc'] }}</p>

                                        <div class="form-check mb-0">
                                            <input class="form-check-input theme-radio" type="radio" name="theme"
                                                   id="theme_{{ $key }}" value="{{ $key }}"
                                                   {{ $theme === $key ? 'checked' : '' }}
                                                   onchange="this.closest('.theme-card').querySelector('.form-check-input').checked=true">
                                            <label class="form-check-label small" for="theme_{{ $key }}"
                                                   style="color: {{ $theme === $key ? 'var(--theme-primary-dark)' : 'var(--theme-text-muted)' }};">
                                                {{ $theme === $key ? 'Active' : 'Select' }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="text-end mt-auto">
                            <button type="submit" class="btn btn-themed rounded-pill px-5 py-2 fw-bold shadow-sm btn-submit" id="themeSubmitBtn">
                                <span class="btn-text"><i class="fas fa-check me-2"></i>Apply Theme</span>
                                <span class="spinner-overlay"><span class="spinner-border spinner-border-sm"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Logo & Preview --}}
        <div class="col-lg-5 d-flex flex-column gap-3">
            {{-- Logo Upload --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 px-4 border-bottom rounded-top-4 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold d-flex align-items-center gap-2" style="color: var(--theme-heading);">
                        <i class="fas fa-image" style="color: var(--theme-primary-dark);"></i>Logo
                    </h5>
                    <span class="file-info">PNG, JPG, SVG &middot; max 2MB</span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.appearance.logo') }}" enctype="multipart/form-data" id="logoForm">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Brand Mark</label>
                            <div class="upload-zone {{ $logoSetting ? 'has-image' : '' }}" id="logoUploadZone">
                                @if($logoSetting)
                                    <div class="d-flex flex-column align-items-center">
                                        <img src="{{ Storage::url($logoSetting) }}" alt="Logo" class="logo-preview mb-2">
                                        <div class="d-flex align-items-center gap-2">
<span class="badge rounded-pill" style="background: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary-dark); font-weight: 500;">
                                                        <i class="fas fa-check me-1"></i> {{ basename($logoSetting) }}
                                            </span>
                                        </div>
                                        <p class="small text-muted mb-0 mt-2"><i class="fas fa-undo me-1"></i>Click to replace</p>
                                    </div>
                                @else
                                    <div class="py-3">
                                        <i class="fas fa-cloud-upload-alt fa-3x mb-2" style="color: var(--theme-text-muted);"></i>
                                        <p class="mb-1 fw-semibold" style="color: var(--theme-heading);">Drop your logo here</p>
                                        <p class="small text-muted mb-2">or click to browse</p>
                                        <span class="file-info">200×60px recommended</span>
                                    </div>
                                @endif
                            </div>
                            <input type="file" name="logo" id="logoInput" class="d-none"
                                   accept="image/png,image/jpeg,image/svg+xml"
                                   onchange="previewLogo(this)">
                            @error('logo')
                                <div class="text-danger small mt-2 d-flex align-items-center gap-1">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-themed rounded-pill px-4 py-2 fw-bold shadow-sm flex-fill btn-submit" id="logoSubmitBtn">
                                <span class="btn-text"><i class="fas fa-upload me-2"></i>Upload Logo</span>
                                <span class="spinner-overlay"><span class="spinner-border spinner-border-sm"></span></span>
                            </button>
                            @if($logoSetting)
                            <button type="button" class="btn btn-outline-danger rounded-pill px-3 py-2" onclick="if(confirm('Remove the current logo?')) { document.getElementById('removeLogoForm').submit(); }">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            @endif
                        </div>
                    </form>
                    @if($logoSetting)
                    <form method="POST" action="{{ route('admin.appearance.logo.remove') }}" id="removeLogoForm" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                    @endif
                </div>
            </div>

            {{-- Live Preview --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 px-4 border-bottom rounded-top-4 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold d-flex align-items-center gap-2" style="color: var(--theme-heading);">
                        <i class="fas fa-eye" style="color: var(--theme-primary-dark);"></i>Live Preview
                    </h5>
                    <small class="text-muted">Applies selected theme</small>
                </div>
                <div class="card-body p-4">
                    {{-- Simulated navbar --}}
                    <div class="rounded-top-3 p-3 d-flex align-items-center gap-3"
                         style="background: var(--sidebar-bg);">
                        @if($logoSetting)
                            <img src="{{ Storage::url($logoSetting) }}" alt="Logo" style="height: 32px; width: auto; object-fit: contain;">
                        @else
                            <span style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--sidebar-text);">
                                <i class="fas fa-building"></i>
                            </span>
                        @endif
                        <span class="fw-bold" style="color: var(--sidebar-brand); font-size: 1rem; letter-spacing: -0.3px;">
                            BRICKSPOINT<sup>&trade;</sup><sub style="font-size:7pt;">ERP</sub>
                        </span>
                    </div>
                    {{-- Simulated sidebar + content --}}
                    <div class="d-flex rounded-bottom-3 overflow-hidden" style="min-height: 60px;">
                        <div style="width: 80px; background: var(--sidebar-bg); padding: 12px;">
                            <span style="display:block;width:60%;height:5px;border-radius:3px;background:var(--sidebar-text);opacity:0.15;margin-bottom:8px;"></span>
                            <span style="display:block;width:40%;height:5px;border-radius:3px;background:var(--sidebar-text);opacity:0.10;margin-bottom:6px;"></span>
                            <span style="display:block;width:50%;height:5px;border-radius:3px;background:var(--sidebar-text);opacity:0.08;"></span>
                        </div>
                        <div class="flex-fill p-3" style="background: var(--theme-body-bg);">
                            <span style="display:block;width:50%;height:5px;border-radius:3px;background:var(--theme-text);opacity:0.10;margin-bottom:8px;"></span>
                            <span style="display:block;width:30%;height:5px;border-radius:3px;background:var(--theme-text);opacity:0.06;"></span>
                        </div>
                    </div>
                    <p class="small text-muted mt-2 mb-0 d-flex align-items-center gap-1">
                        <i class="fas fa-info-circle"></i> Preview reflects your current theme and logo settings.
                    </p>
                </div>
            </div>
        </div>

    </div>

@push('scripts')
<script>
    // Theme card click behaviour
    document.querySelectorAll('.theme-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.closest('.form-check')) return;
            const radio = this.querySelector('.theme-radio');
            if (radio) {
                radio.checked = true;
                document.querySelectorAll('.theme-card').forEach(c => {
                    c.classList.remove('active');
                    c.querySelector('.form-check-label').style.color = 'var(--theme-text-muted)';
                });
                this.classList.add('active');
                const label = this.querySelector('.form-check-label');
                if (label) label.style.color = 'var(--theme-primary-dark)';
            }
        });
    });

    // Logo preview on file selection
    function previewLogo(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];

        if (file.size > 2 * 1024 * 1024) {
            alert('File is too large. Maximum size is 2MB.');
            input.value = '';
            return;
        }

        const zone = document.getElementById('logoUploadZone');
        const reader = new FileReader();
        reader.onload = function(e) {
            zone.innerHTML = `
                <div class="d-flex flex-column align-items-center">
                    <img src="${e.target.result}" alt="Logo preview" class="logo-preview mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill" style="background: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary-dark); font-weight: 500;">
                            <i class="fas fa-file me-1"></i> ${file.name}
                        </span>
                    </div>
                    <p class="small text-muted mb-0 mt-2"><i class="fas fa-undo me-1"></i>Click to change</p>
                </div>
            `;
            zone.classList.add('has-image');
        };
        reader.readAsDataURL(file);
    }

    // Drag & drop support for logo upload
    const logoZone = document.getElementById('logoUploadZone');
    if (logoZone) {
        logoZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        logoZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        logoZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            const input = document.getElementById('logoInput');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                previewLogo(input);
            }
        });
    }

    // Form submission loading states
    document.getElementById('themeForm')?.addEventListener('submit', function() {
        document.getElementById('themeSubmitBtn')?.classList.add('loading');
    });
    document.getElementById('logoForm')?.addEventListener('submit', function() {
        document.getElementById('logoSubmitBtn')?.classList.add('loading');
    });

    // Auto-dismiss alerts
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });
</script>
@endpush
@endsection
