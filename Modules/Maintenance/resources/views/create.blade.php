@extends('layouts.master')

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-plus-circle me-2" style="color: var(--luxury-gold);"></i>Create Maintenance Log</h2>
            <p class="text-muted mb-0">Log a new maintenance or IT issue</p>
        </div>
        <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Logs
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('maintenance.store') }}">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Location <span class="text-danger">*</span></label>
                        <input type="text" name="location" class="form-control" value="{{ old('location') }}" required maxlength="100">
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

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Complaint Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="complaint_datetime" class="form-control" value="{{ old('complaint_datetime') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Lodged By <span class="text-danger">*</span></label>
                        <input type="text" name="lodged_by" class="form-control" value="{{ old('lodged_by', Auth::check() ? Auth::user()->name : '') }}" required maxlength="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Received By <span class="text-danger">*</span></label>
                        <input type="text" name="received_by" class="form-control" value="{{ old('received_by', Auth::check() ? Auth::user()->name : '') }}" required maxlength="100" readonly>
                        <div class="form-text">Auto-filled with your name.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nature of Complaint <span class="text-danger">*</span></label>
                    <textarea name="nature_of_complaint" class="form-control" rows="4" required>{{ old('nature_of_complaint') }}</textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <div class="status-toggle-wrapper">
                            <input type="hidden" name="status" id="statusInput" value="{{ old('status', 'new') }}">
                            <div class="status-toggle">
                                <button type="button" class="st-btn st-new {{ old('status', 'new') === 'new' ? 'active' : '' }}" data-value="new">New</button>
                                <button type="button" class="st-btn st-in_progress {{ old('status') === 'in_progress' ? 'active' : '' }}" data-value="in_progress">Doing</button>
                                <button type="button" class="st-btn st-completed {{ old('status') === 'completed' ? 'active' : '' }}" data-value="completed">Done</button>
                                <button type="button" class="st-btn st-cancelled {{ old('status') === 'cancelled' ? 'active' : '' }}" data-value="cancelled">Cancel</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cost of Fixing (NGN)</label>
                        <input type="number" step="0.01" name="cost_of_fixing" class="form-control" value="{{ old('cost_of_fixing') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Completion Date</label>
                        <input type="date" name="completion_date" class="form-control" value="{{ old('completion_date') }}">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-lg px-4" style="background-color: var(--luxury-gold); color: #fff;">
                        <i class="fas fa-save me-1"></i> Create Log
                    </button>
                    <a href="{{ route('maintenance.index') }}" class="btn btn-lg btn-outline-secondary px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('styles')
<style>
.status-toggle { display: inline-flex; border-radius: 20px; overflow: hidden; border: 1px solid #dee2e6; }
.status-toggle .st-btn { border: none; padding: 5px 16px; font-size: 0.8rem; cursor: pointer; transition: all 0.15s; font-weight: 500; }
.status-toggle .st-btn:not(:last-child) { border-right: 1px solid #dee2e6; }
.status-toggle .st-btn.st-new { background: #fff8e1; color: #8a6d00; }
.status-toggle .st-btn.st-in_progress { background: #e3f2fd; color: #0a58ca; }
.status-toggle .st-btn.st-completed { background: #e8f5e9; color: #146c43; }
.status-toggle .st-btn.st-cancelled { background: #f5f5f5; color: #6c757d; }
.status-toggle .st-btn.active.st-new { background: #ffc107; color: #212529; }
.status-toggle .st-btn.active.st-in_progress { background: #0d6efd; color: #fff; }
.status-toggle .st-btn.active.st-completed { background: #198754; color: #fff; }
.status-toggle .st-btn.active.st-cancelled { background: #6c757d; color: #fff; }
.status-toggle .st-btn:not(.active):hover { filter: brightness(0.92); }
</style>
@endsection

@section('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.status-toggle .st-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var wrapper = this.closest('.status-toggle-wrapper');
            wrapper.querySelectorAll('.st-btn').forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            wrapper.querySelector('#statusInput').value = this.dataset.value;
        });
    });
});
</script>
@endsection
