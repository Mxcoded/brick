@extends('layouts.base')
@section('content')
    <div class="d-flex" id="wrapper">

        @include('layouts.sidebar')

        {{-- These new classes create a flex container that takes up the full viewport height --}}
        <div id="page-content-wrapper" class="d-flex flex-column min-vh-100">

            @include('layouts.navbar')

            {{-- This new div will grow to fill all available space, pushing the footer down --}}
            <div class="container-fluid p-4 flex-grow-1" style="font-family: 'Proxima Nova', Arial, Helvetica, sans-serif;">
                @yield('page-content')
            </div>

            {{-- Floating Report Issue Button (auth only) --}}
            @auth
                <button id="fabReport" class="btn fab-maintenance" onclick="$('#fabReportModal').modal('show')" title="Report an Issue">
                    <i class="fas fa-wrench"></i>
                    <span class="fab-label d-none d-md-inline">Report Issue</span>
                </button>
            @endauth

            <footer class="p-3 mt-auto border-top" style="background-color: var(--theme-card-bg); border-color: var(--theme-border) !important;">
                <div class="container-fluid text-center">
                    <p class="mb-0" style="color: var(--theme-text-muted);">
                        &copy; {{ date('Y') }}
                        <span class="fw-bold" style="color: var(--sidebar-brand);">
                            BRICKSPOINT<sup>&trade;</sup><sub style="font-size:9pt;">ERP</sub>
                        </span>
                        . All rights reserved.
                    </p>
                </div>
            </footer>
        </div>
    </div>
@endsection

{{-- Shared Quick Report Modal (auth only) --}}
@auth
<div class="modal fade" id="fabReportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0" style="background: var(--theme-primary); color: #fff;">
                <h5 class="modal-title fw-bold fs-6"><i class="fas fa-wrench me-2"></i>Report Issue</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('maintenance.quick-store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <input type="text" name="lodged_by" class="form-control form-control-sm" value="{{ Auth::user()->name }}" placeholder="Your name" required>
                    </div>
                    <div class="row g-1 mb-2">
                        <div class="col-7">
                            <input type="text" name="location" class="form-control form-control-sm" placeholder="Location *" required>
                        </div>
                        <div class="col-5">
                            <select name="department" class="form-select form-select-sm" required>
                                <option value="">Dept</option>
                                @foreach (\Modules\Maintenance\Models\MaintenanceLog::DEPARTMENTS as $key => $label)
                                    <option value="{{ $key }}">{{ $key }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <select name="priority" class="form-select form-select-sm">
                            @foreach (\Modules\Maintenance\Models\MaintenanceLog::PRIORITIES as $key => $label)
                                <option value="{{ $key }}" {{ $key === 'medium' ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <textarea name="nature_of_complaint" class="form-control form-control-sm" rows="2" placeholder="Describe the issue..." required></textarea>
                    </div>
                    <div class="mb-1">
                        <input type="file" name="image" class="form-control form-control-sm" accept="image/*" capture="environment" data-compress="1200">
                    </div>
                    <input type="hidden" name="complaint_datetime" value="{{ now()->format('Y-m-d\TH:i') }}">
                    <input type="hidden" name="received_by" value="{{ Auth::user()->name }}">
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-gold btn-sm w-100" id="fabSubmit">
                        <i class="fas fa-paper-plane me-1" id="fabIcon"></i> <span id="fabText">Submit</span>
                        <span class="spinner-border spinner-border-sm d-none" id="fabSpinner" role="status"></span>
                    </button>
                </div>
                <div class="modal-loading d-none" id="fabOverlay">
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center">
                            <div class="spinner-border text-light mb-2" style="width: 3rem; height: 3rem;" role="status"></div>
                            <p class="text-light mb-0 fw-semibold">Submitting report...</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .modal-loading { position: absolute; inset: 0; background: rgba(0,0,0,0.6); border-radius: inherit; z-index: 10; }
    .fab-maintenance { position: fixed; bottom: 30px; right: 30px; z-index: 9999; background: var(--theme-primary); color: #fff; border: none; border-radius: 50px; padding: 14px 22px; box-shadow: 0 6px 20px rgba(var(--theme-primary-rgb), 0.4); transition: all 0.2s; display: flex; align-items: center; gap: 8px; cursor: pointer; }
    .fab-maintenance:hover { background: var(--theme-primary-hover); color: #fff; transform: translateY(-2px); box-shadow: 0 8px 28px rgba(var(--theme-primary-rgb), 0.5); }
    .fab-maintenance i { font-size: 1.2rem; }
    .fab-label { font-weight: 600; font-size: 0.85rem; }
    @media (max-width: 576px) { .fab-maintenance { padding: 12px 16px; bottom: 20px; right: 20px; } }

</style>

@endauth

@section('scripts')
    <script>
        window.addEventListener('DOMContentLoaded', event => {
            const sidebarToggle = document.body.querySelector('#sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', event => {
                    event.preventDefault();
                    document.body.querySelector('#wrapper').classList.toggle('toggled');
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            if (typeSelect) {
                const valueField = document.getElementById('value-field');
                const imageField = document.getElementById('image-field');
                const videoField = document.getElementById('video-field');

                function toggleFields() {
                    valueField.style.display = typeSelect.value === 'string' || typeSelect.value === 'json' ?
                        'block' : 'none';
                    imageField.style.display = typeSelect.value === 'image' ? 'block' : 'none';
                    videoField.style.display = typeSelect.value === 'video' ? 'block' : 'none';
                }
                typeSelect.addEventListener('change', toggleFields);
                toggleFields();
            }
        });
    </script>

    <script>
    // Image compression helper — compresses files on data-compress inputs before submit
    document.addEventListener('change', function (e) {
        var input = e.target;
        if (input.matches('input[type="file"][data-compress]') && input.files && input.files[0]) {
            var maxDim = parseInt(input.dataset.compress) || 1200;
            var file = input.files[0];
            if (!file.type.startsWith('image/')) return;
            var reader = new FileReader();
            reader.onload = function (ev) {
                var img = new Image();
                img.onload = function () {
                    var canvas = document.createElement('canvas');
                    var w = img.width, h = img.height;
                    if (w > maxDim || h > maxDim) {
                        var ratio = Math.min(maxDim / w, maxDim / h);
                        w = Math.round(w * ratio);
                        h = Math.round(h * ratio);
                    }
                    canvas.width = w;
                    canvas.height = h;
                    var ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, w, h);
                    canvas.toBlob(function (blob) {
                        if (blob.size < file.size) {
                            var newFile = new File([blob], file.name, { type: 'image/jpeg', lastModified: Date.now() });
                            var dt = new DataTransfer();
                            dt.items.add(newFile);
                            input.files = dt.files;
                        }
                    }, 'image/jpeg', 0.7);
                };
                img.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Loading spinner for all maintenance report forms
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form.action && (form.action.indexOf('maintenance') !== -1 || form.action.indexOf('quick-report') !== -1) && form.method.toLowerCase() === 'post') {
            var overlay = form.querySelector('.modal-loading');
            var btn = form.querySelector('button[type="submit"]');
            if (overlay) overlay.classList.remove('d-none');
            if (btn) {
                var orig = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Submitting...';
                setTimeout(function () { btn.disabled = false; btn.innerHTML = orig; if (overlay) overlay.classList.add('d-none'); }, 30000);
            }
        }
    });
    </script>
    @yield('page-scripts')
@endsection
