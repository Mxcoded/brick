@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banquet.index') }}">Banquet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('banquet.event-leads.index') }}">Event Leads</a></li>
    <li class="breadcrumb-item active">{{ $lead->name }}</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 banquet-theme">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-charcoal">
                <i class="fas fa-user me-2 text-gold"></i>{{ $lead->name }}
            </h1>
            <p class="text-muted mb-0">Lead captured {{ $lead->created_at->diffForHumans() }}</p>
        </div>
        <a href="{{ route('banquet.event-leads.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Leads
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex align-items-center">
                    <i class="fas fa-info-circle me-2 text-gold"></i>
                    <h5 class="mb-0 fw-bold">Contact Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-semibold">Full Name</label>
                            <p class="fw-bold mb-0">{{ $lead->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-semibold">Email</label>
                            <p class="mb-0"><a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></p>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-semibold">Phone</label>
                            <p class="mb-0"><a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a></p>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-semibold">Company</label>
                            <p class="mb-0">{{ $lead->company ?: '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-semibold">Source</label>
                            <p class="mb-0">{{ $lead->source ?: '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-semibold">Status</label>
                            <p class="mb-0">
                                @php $colors = ['New' => 'warning', 'Contacted' => 'info', 'Converted' => 'success', 'Closed' => 'secondary']; @endphp
                                @php $statusColors = $colors; @endphp
                                <span id="leadStatusBadge" class="badge bg-{{ $colors[$lead->status] ?? 'secondary' }} rounded-pill px-3">{{ $lead->status }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3 d-flex align-items-center">
                    <i class="fas fa-calendar me-2 text-gold"></i>
                    <h5 class="mb-0 fw-bold">Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-3 mb-3">
                        <div class="text-center" style="width: 40px;">
                            <div class="rounded-circle bg-gold text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="fas fa-plus" style="font-size: 0.7rem;"></i>
                            </div>
                        </div>
                        <div>
                            <p class="mb-0 fw-semibold">Lead Captured</p>
                            <small class="text-muted">{{ $lead->created_at->format('M d, Y h:i A') }}</small>
                        </div>
                    </div>
                    @if($lead->updated_at != $lead->created_at)
                    <div class="d-flex gap-3">
                        <div class="text-center" style="width: 40px;">
                            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="fas fa-pen" style="font-size: 0.7rem;"></i>
                            </div>
                        </div>
                        <div>
                            <p class="mb-0 fw-semibold">Last Updated</p>
                            <small class="text-muted">{{ $lead->updated_at->format('M d, Y h:i A') }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex align-items-center">
                    <i class="fas fa-tag me-2 text-gold"></i>
                    <h5 class="mb-0 fw-bold">Status</h5>
                </div>
                <div class="card-body" id="statusCard">
                    <div class="d-flex flex-wrap gap-2">
                        @php $statuses = ['New', 'Contacted', 'Converted', 'Closed']; @endphp
                        @foreach ($statuses as $s)
                            @php
                                $colors = [
                                    'New' => ['bg' => 'primary', 'icon' => 'fas fa-circle'],
                                    'Contacted' => ['bg' => 'warning', 'icon' => 'fas fa-phone'],
                                    'Converted' => ['bg' => 'success', 'icon' => 'fas fa-check-circle'],
                                    'Closed' => ['bg' => 'secondary', 'icon' => 'fas fa-ban'],
                                ];
                                $active = $lead->status === $s;
                            @endphp
                            <button type="button"
                                class="btn status-btn @if($active) btn-{{ $colors[$s]['bg'] }} @else btn-outline-{{ $colors[$s]['bg'] }} @endif d-inline-flex align-items-center gap-1"
                                data-status="{{ $s }}"
                                data-url="{{ route('banquet.event-leads.update-status', $lead->id) }}"
                                @if($active) disabled @endif>
                                <i class="{{ $colors[$s]['icon'] }}"></i> {{ $s }}
                            </button>
                        @endforeach
                    </div>
                    <small class="text-muted mt-2 d-block">Click a status to update instantly</small>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3 d-flex align-items-center">
                    <i class="fas fa-sticky-note me-2 text-gold"></i>
                    <h5 class="mb-0 fw-bold">Notes</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('banquet.event-leads.update-notes', $lead->id) }}" method="POST">
                        @csrf
                        <textarea name="notes" class="form-control mb-3" rows="4" placeholder="Internal notes...">{{ $lead->notes }}</textarea>
                        <button type="submit" class="btn btn-gold btn-sm w-100">
                            <i class="fas fa-save me-1"></i> Save Notes
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body text-center">
                    <form action="{{ route('banquet.event-leads.destroy', $lead->id) }}" method="POST"
                          onsubmit="return confirm('Delete this lead?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="fas fa-trash-alt me-1"></i> Delete Lead
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.banquet-theme .text-gold { color: #C8A165 !important; }
.banquet-theme .bg-gold { background-color: #C8A165 !important; }
.btn-gold { background-color: #C8A165; border-color: #C8A165; color: #fff; }
.btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #fff; }
.btn-outline-gold { border-color: #C8A165; color: #C8A165; }
.btn-outline-gold:hover { background-color: #C8A165; color: #fff; }
</style>
@endsection

@section('scripts')
<script>
    $('#statusCard').on('click', '.status-btn:not([disabled])', function () {
        const btn = $(this);
        const url = btn.data('url');
        const status = btn.data('status');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: url,
            method: 'PATCH',
            data: { status, _token: '{{ csrf_token() }}' },
            success: function () {
                const card = $('#statusCard');
                const old = card.find('.status-btn[disabled]');
                old.prop('disabled', false);
                old.removeClass((i, cls) => cls.split(' ').filter(c => c.startsWith('btn-')).join(' '));
                old.addClass((i, cls) => cls.includes('btn-primary') ? 'btn-outline-primary' :
                    cls.includes('btn-warning') ? 'btn-outline-warning' :
                    cls.includes('btn-success') ? 'btn-outline-success' :
                    cls.includes('btn-secondary') ? 'btn-outline-secondary' : 'btn-outline-primary');

                const btnIcons = { New: 'fa-circle', Contacted: 'fa-phone', Converted: 'fa-check-circle', Closed: 'fa-ban' };
                btn.removeClass((i, cls) => cls.split(' ').filter(c => c.startsWith('btn-')).join(' '));
                btn.addClass('btn-' + (status === 'New' ? 'primary' : status === 'Contacted' ? 'warning' : status === 'Converted' ? 'success' : 'secondary'));
                btn.prop('disabled', true).html('<i class="fas ' + btnIcons[status] + '"></i> ' + status);

                const badgeColors = { New: 'warning', Contacted: 'info', Converted: 'success', Closed: 'secondary' };
                const badge = $('#leadStatusBadge');
                badge.removeClass('bg-warning bg-info bg-success bg-secondary').addClass('bg-' + badgeColors[status]).text(status);
            },
            error: function () {
                alert('Failed to update status. Please try again.');
                const btnIcons = { New: 'fa-circle', Contacted: 'fa-phone', Converted: 'fa-check-circle', Closed: 'fa-ban' };
                btn.prop('disabled', false).html('<i class="fas ' + btnIcons[status] + '"></i> ' + status);
            }
        });
    });
</script>
@endsection
