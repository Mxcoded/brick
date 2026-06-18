@extends('layouts.master')

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-clipboard-list me-2" style="color: var(--luxury-gold);"></i>Maintenance Log #{{ $maintenanceLog->id }}</h2>
            <p class="text-muted mb-0">Detailed view of the maintenance issue</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Logs
            </a>
            <a href="{{ route('maintenance.edit', $maintenanceLog->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Main Details Card --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Location</label>
                            <p class="fw-semibold mb-0">{{ $maintenanceLog->location }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Department</label>
                            <p class="fw-semibold mb-0">
                                <span class="badge bg-secondary fs-6">{{ $maintenanceLog->department }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Complaint Date & Time</label>
                            <p class="fw-semibold mb-0">{{ $maintenanceLog->complaint_datetime->format('M d, Y h:i A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Completion Date</label>
                            <p class="fw-semibold mb-0">{{ $maintenanceLog->completion_date ? $maintenanceLog->completion_date->format('M d, Y') : '--' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Lodged By</label>
                            <p class="fw-semibold mb-0">{{ $maintenanceLog->lodged_by }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Received By</label>
                            <p class="fw-semibold mb-0">{{ $maintenanceLog->received_by ?? '--' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Priority</label>
                            <p class="mb-0">
                                @php
                                    $pColors = ['low' => '#6c757d', 'medium' => '#ffc107', 'high' => '#fd7e14', 'critical' => '#dc3545'];
                                    $pColor = $pColors[$maintenanceLog->priority] ?? '#6c757d';
                                @endphp
                                <span class="badge rounded-pill fs-6" style="background-color: {{ $pColor }}; color: {{ $maintenanceLog->priority === 'medium' ? '#212529' : '#fff' }};">
                                    {{ ucfirst($maintenanceLog->priority) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Created At</label>
                            <p class="fw-semibold mb-0">{{ $maintenanceLog->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small text-uppercase">Nature of Complaint</label>
                            <div class="p-3 bg-light rounded-3 mt-1">
                                <p class="mb-0">{{ $maintenanceLog->nature_of_complaint }}</p>
                            </div>
                        </div>
                        @if ($maintenanceLog->image_url)
                        <div class="col-12">
                            <label class="text-muted small text-uppercase">Photo</label>
                            <a href="{{ $maintenanceLog->image_url }}" target="_blank" class="d-block mt-1">
                                <img src="{{ $maintenanceLog->image_url }}" alt="Issue photo" class="rounded-3 img-fluid" style="max-height: 300px; object-fit: contain;">
                            </a>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Cost of Fixing (NGN)</label>
                            <p class="fw-semibold fs-5 mb-0" style="color: var(--luxury-gold);">
                                {{ $maintenanceLog->cost_of_fixing ? number_format($maintenanceLog->cost_of_fixing, 2) : '--' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Card --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                    @php
                        $statusIcons = [
                            'new' => ['icon' => 'fa-exclamation-circle', 'color' => '#ffc107', 'text' => 'New Case', 'label' => 'New'],
                            'in_progress' => ['icon' => 'fa-sync-alt', 'color' => '#0d6efd', 'text' => 'In Progress', 'label' => 'Doing'],
                            'completed' => ['icon' => 'fa-check-circle', 'color' => '#198754', 'text' => 'Completed', 'label' => 'Done'],
                            'cancelled' => ['icon' => 'fa-times-circle', 'color' => '#6c757d', 'text' => 'Cancelled', 'label' => 'Cancel'],
                        ];
                        $status = $statusIcons[$maintenanceLog->status] ?? $statusIcons['new'];
                    @endphp
                    <i class="fas {{ $status['icon'] }}" style="font-size: 4rem; color: {{ $status['color'] }}; margin-bottom: 15px;"></i>
                    <h4 style="color: {{ $status['color'] }};">{{ $status['text'] }}</h4>

                    {{-- Status Toggle --}}
                    <div class="mt-3">
                        @php $statusList = ['new', 'in_progress', 'completed', 'cancelled']; @endphp
                        <div class="status-toggle">
                            @foreach ($statusList as $st)
                                <form action="{{ route('maintenance.toggle-status', $maintenanceLog->id) }}" method="POST" class="d-inline status-toggle-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $st }}">
                                    <button type="submit"
                                        class="st-btn st-{{ $st }} {{ $maintenanceLog->status === $st ? 'active' : '' }}"
                                        {{ $maintenanceLog->status === $st ? 'disabled' : '' }}>
                                        {{ $st === 'in_progress' ? 'Doing' : ($st === 'completed' ? 'Done' : ucfirst($st)) }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
<style>
    .card { border-radius: 10px; transition: box-shadow 0.2s; }
    .card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.08) !important; }
    .text-muted.small.text-uppercase { font-size: 0.75rem; letter-spacing: 0.5px; }
    .status-toggle { display: inline-flex; border-radius: 20px; overflow: hidden; border: 1px solid #dee2e6; }
    .status-toggle .st-btn { border: none; padding: 5px 14px; font-size: 0.78rem; cursor: pointer; transition: all 0.15s; font-weight: 500; }
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
    document.querySelectorAll('.status-toggle-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var btn = this.querySelector('.st-btn');
            if (btn && btn.disabled) return;
            e.preventDefault();
            var token = this.querySelector('input[name="_token"]')?.value || '';
            var status = this.querySelector('input[name="status"]')?.value || '';
            var body = new URLSearchParams();
            body.append('_token', token);
            body.append('_method', 'PATCH');
            body.append('status', status);
            fetch(this.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) return;
                var toggle = document.querySelector('.status-toggle');
                toggle.querySelectorAll('.st-btn').forEach(function (b) {
                    var st = b.closest('form') ? b.closest('form').querySelector('input[name="status"]').value : '';
                    b.classList.remove('active');
                    b.disabled = false;
                    if (st === data.status) { b.classList.add('active'); b.disabled = true; }
                });
            })
            .catch(function () {});
        });
    });
});
</script>
@endsection
