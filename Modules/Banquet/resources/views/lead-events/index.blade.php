@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banquet.index') }}">Banquet</a></li>
    <li class="breadcrumb-item active">Lead Events</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 banquet-theme">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-charcoal">
                <i class="fas fa-calendar-alt me-2 text-gold"></i>Lead Events
            </h1>
            <p class="text-muted mb-0">Manage event campaigns for lead capture</p>
        </div>
        <a href="{{ route('banquet.lead-events.create') }}" class="btn btn-gold">
            <i class="fas fa-plus me-1"></i> New Event
        </a>
    </div>

    @if ($events->count())
    <div class="row g-4">
        @foreach ($events as $event)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-calendar-day text-gold me-1"></i>
                            {{ $event->title }}
                        </h5>
                        @if ($event->is_active)
                            <span class="badge bg-success rounded-pill px-2">Active</span>
                        @else
                            <span class="badge bg-secondary rounded-pill px-2">Inactive</span>
                        @endif
                    </div>
                    @if ($event->description)
                        <p class="text-muted small mb-2">{{ Str::limit($event->description, 120) }}</p>
                    @endif
                    <div class="d-flex gap-3 mb-3 flex-wrap">
                        @if ($event->organizer)
                            <small class="text-muted"><i class="fas fa-building me-1"></i>{{ $event->organizer }}</small>
                        @endif
                        @if ($event->event_date)
                            <small class="text-muted"><i class="fas fa-clock me-1"></i>{{ $event->event_date->format('M d, Y') }}</small>
                        @endif
                        @if ($event->location)
                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $event->location }}</small>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-light text-dark rounded-pill px-3 py-2">
                            <i class="fas fa-users me-1"></i> {{ $event->leads_count }} leads
                        </span>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('banquet.lead-events.qrcode', $event->id) }}">
                                        <i class="fas fa-qrcode me-2 text-dark"></i> QR Code
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item copy-link" href="#"
                                       data-url="{{ route('website.event-lead', $event->slug) }}">
                                        <i class="fas fa-link me-2 text-primary"></i> Copy Link
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('banquet.lead-events.edit', $event->id) }}">
                                        <i class="fas fa-edit me-2 text-warning"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('banquet.event-leads.index', ['event_id' => $event->id]) }}">
                                        <i class="fas fa-users me-2 text-info"></i> View Leads
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('banquet.lead-events.destroy', $event->id) }}" method="POST"
                                          onsubmit="return confirm('Delete this event? Leads linked to it will remain but unlinked.');">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger">
                                            <i class="fas fa-trash-alt me-2"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">
        {{ $events->links() }}
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No events yet</h5>
            <p class="text-muted">Create your first event to start collecting leads.</p>
            <a href="{{ route('banquet.lead-events.create') }}" class="btn btn-gold">
                <i class="fas fa-plus me-1"></i> Create Event
            </a>
        </div>
    </div>
    @endif
</div>

<style>
.banquet-theme .text-gold { color: #C8A165 !important; }
.btn-gold { background-color: #C8A165; border-color: #C8A165; color: #fff; }
.btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #fff; }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('.copy-link').on('click', function (e) {
        e.preventDefault();
        const url = $(this).data('url');
        navigator.clipboard.writeText(url).then(() => {
            const btn = $(this);
            const orig = btn.html();
            btn.html('<i class="fas fa-check me-2 text-success"></i> Copied!');
            setTimeout(() => btn.html(orig), 2000);
        }).catch(() => {
            prompt('Copy this link:', url);
        });
    });
});
</script>
@endsection
