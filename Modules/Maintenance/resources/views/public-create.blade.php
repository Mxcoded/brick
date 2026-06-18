@extends('layouts.base')

@section('styles')
<style>
    .report-header { background: linear-gradient(135deg, #333 0%, #555 100%); padding: 40px 0; margin-bottom: 40px; }
    .report-header h1 { color: #C8A165; font-weight: 800; }
    .report-header p { color: #ccc; }
    .form-card { border: none; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); margin-bottom: 40px; }
    .form-card .card-body { padding: 2.5rem; }
    .form-label { font-weight: 600; color: #555; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-control, .form-select { border-radius: 10px; border: 1px solid #e0e0e0; padding: 12px 16px; font-size: 0.95rem; }
    .form-control:focus, .form-select:focus { border-color: #C8A165; box-shadow: 0 0 0 3px rgba(200,161,101,0.15); }
    .brand-mark { font-family: 'Proxima Nova', Arial, sans-serif; font-weight: 800; font-size: 1.6rem; color: #C8A165; letter-spacing: -0.5px; }
    .footer-text { color: #999; font-size: 0.85rem; }
</style>
@endsection

@section('content')
<div class="report-header text-center">
    <div class="container">
        <span class="brand-mark">BRICKSPOINT<sup>&trade;</sup></span>
        <h1 class="mt-3"><i class="fas fa-tools me-2"></i>Report a Maintenance Issue</h1>
        <p class="mb-0">Use this form to report any facility or IT issue. Our team will follow up.</p>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card form-card">
                <div class="card-body">
                    <form method="POST" action="{{ route('maintenance.public.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label">Your Name <span class="text-danger">*</span></label>
                            <input type="text" name="lodged_by" class="form-control" value="{{ old('lodged_by') }}" required maxlength="100" placeholder="e.g. John Doe">
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" name="location" class="form-control" value="{{ old('location') }}" required maxlength="100" placeholder="e.g. Room 204, Lobby, Kitchen">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department <span class="text-danger">*</span></label>
                                <select name="department" class="form-select" required>
                                    <option value="">-- Select Department --</option>
                                    @foreach (\Modules\Maintenance\Models\MaintenanceLog::DEPARTMENTS as $key => $label)
                                        <option value="{{ $key }}" {{ old('department') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Priority</label>
                            <div class="d-flex gap-2 flex-wrap">
                                @foreach (\Modules\Maintenance\Models\MaintenanceLog::PRIORITIES as $key => $label)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="priority" id="p-{{ $key }}" value="{{ $key }}" {{ old('priority', 'medium') === $key ? 'checked' : '' }}>
                                        <label class="form-check-label" for="p-{{ $key }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Photo <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="file" name="image" class="form-control" accept="image/*" capture="environment" data-compress="1200">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description of Issue <span class="text-danger">*</span></label>
                            <textarea name="nature_of_complaint" class="form-control" rows="5" required placeholder="Please describe the issue in detail...">{{ old('nature_of_complaint') }}</textarea>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-lg px-5" id="publicSubmitBtn" style="background-color: #C8A165; color: #fff; border-radius: 10px; font-weight: 600;">
                                <i class="fas fa-paper-plane me-2" id="publicSubmitIcon"></i> <span id="publicSubmitText">Submit Report</span>
                                <span class="spinner-border spinner-border-sm d-none" id="publicSubmitSpinner" role="status"></span>
                            </button>
                            <a href="{{ url('/') }}" class="btn btn-lg btn-outline-secondary px-4" style="border-radius: 10px;">Back to Home</a>
                        </div>
                    </form>
                </div>
            </div>

            <p class="text-center footer-text mb-5">
                <i class="fas fa-shield-alt me-1"></i> Your report will be sent directly to the maintenance team.
            </p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
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

document.getElementById('publicSubmitBtn')?.addEventListener('click', function (e) {
    var btn = this;
    setTimeout(function () {
        btn.disabled = true;
        document.getElementById('publicSubmitIcon').classList.add('d-none');
        document.getElementById('publicSubmitText').textContent = 'Submitting...';
        document.getElementById('publicSubmitSpinner').classList.remove('d-none');
    }, 50);
});
</script>
@endsection
