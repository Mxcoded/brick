@extends('layouts.master')

@section('title', 'Newsletter Campaigns')

@section('page-content')
<style>
    :root {
        --bp-gold: #C8A165;
        --bp-gold-light: #D4B88A;
        --bp-charcoal: #333333;
    }

    .btn-bp-gold {
        background-color: var(--bp-gold);
        border-color: var(--bp-gold);
        color: white;
    }
    .btn-bp-gold:hover {
        background-color: var(--bp-gold-light);
        border-color: var(--bp-gold-light);
        color: white;
    }

    .btn-outline-dark {
        border-color: #495057;
        color: #495057;
    }
    .btn-outline-dark:hover {
        background-color: #495057;
        color: white;
    }

    .stat-card {
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: default;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important;
    }

    .campaign-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }

    .status-badge {
        font-size: 0.7rem;
        padding: 0.3em 0.7em;
        border-radius: 50px;
        font-weight: 600;
        letter-spacing: 0.3px;
        border: 1px solid transparent;
    }

    .status-badge.bg-secondary {
        background: #e5e7eb !important;
        color: #374151 !important;
        border-color: #d1d5db;
    }
    .status-badge.bg-info {
        background: #e0f2fe !important;
        color: #075985 !important;
        border-color: #bae6fd;
    }
    .status-badge.bg-warning {
        background: #fef3c7 !important;
        color: #92400e !important;
        border-color: #fde68a;
    }
    .status-badge.bg-success {
        background: #d1fae5 !important;
        color: #065f46 !important;
        border-color: #a7f3d0;
    }
    .status-badge.bg-danger {
        background: #fee2e2 !important;
        color: #991b1b !important;
        border-color: #fecaca;
    }

    .campaign-subject {
        font-weight: 600;
        color: var(--bp-charcoal);
        font-size: 0.95rem;
    }

    .empty-state {
        padding: 60px 20px;
    }
    .empty-state i {
        color: var(--bp-gold);
        opacity: 0.5;
    }

    .delivery-metric {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.8rem;
        padding: 2px 10px;
        border-radius: 20px;
        font-weight: 600;
    }
    .delivery-metric.sent {
        background: #d1fae5;
        color: #065f46;
    }
    .delivery-metric.failed {
        background: #fee2e2;
        color: #991b1b;
    }

    .schedule-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.75rem;
        padding: 3px 10px;
        background: #fef3c7;
        color: #92400e;
        border-radius: 20px;
        font-weight: 500;
    }

    .preview-text-truncate {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        max-width: 260px;
    }

    .table > :not(caption) > * > * {
        padding: 0.85rem 0.75rem;
        vertical-align: middle;
    }

    .dropdown-item i {
        width: 18px;
        text-align: center;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: background 0.15s;
    }
    .action-btn:hover {
        background: #f3f4f6;
    }

    .author-initials {
        width: 22px;
        height: 22px;
        font-size: 0.6rem;
        font-weight: 700;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        color: #6b7280;
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .stat-card h3 { font-size: 1.25rem; }
        .table > :not(caption) > * > * { padding: 0.6rem 0.5rem; }
        .d-mobile-none { display: none !important; }
        .campaign-subject { font-size: 0.85rem; }
        .action-btn { width: 28px; height: 28px; font-size: 0.75rem; }
    }
</style>

<div class="container-fluid py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">
                <i class="fas fa-paper-plane me-2" style="color: var(--bp-gold);"></i>Newsletter Campaigns
            </h1>
            <p class="text-muted mb-0">Create and manage email campaigns for your subscribers</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('website.admin.newsletter.subscribers') }}" class="btn btn-outline-dark">
                <i class="fas fa-users me-1"></i> <span class="d-none d-sm-inline">Subscribers</span>
            </a>
            <a href="{{ route('website.admin.newsletter.campaigns.create') }}" class="btn btn-bp-gold">
                <i class="fas fa-plus me-1"></i> New Campaign
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted small mb-1">Total Campaigns</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="rounded-circle p-3" style="background: rgba(200, 161, 101, 0.1);">
                            <i class="fas fa-envelope fa-lg" style="color: var(--bp-gold);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted small mb-1">Drafts</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['draft'] }}</h3>
                        </div>
                        <div class="rounded-circle p-3 bg-light">
                            <i class="fas fa-file-alt fa-lg text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted small mb-1">Sent</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['sent'] }}</h3>
                        </div>
                        <div class="rounded-circle p-3" style="background: rgba(40, 167, 69, 0.1);">
                            <i class="fas fa-check-circle fa-lg text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted small mb-1">Active Subscribers</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['subscribers'] }}</h3>
                        </div>
                        <div class="rounded-circle p-3" style="background: rgba(0, 123, 255, 0.1);">
                            <i class="fas fa-users fa-lg text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('website.admin.newsletter.campaigns.index') }}" method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by subject..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Campaigns</option>
                        @foreach(['draft' => 'Drafts', 'scheduled' => 'Scheduled', 'sending' => 'Sending', 'sent' => 'Sent', 'failed' => 'Failed'] as $val => $label)
                            <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-dark w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Campaigns List --}}
    <div class="card campaign-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="min-width: 200px;">Campaign</th>
                        <th style="min-width: 120px;">Status</th>
                        <th class="d-mobile-none" style="min-width: 100px;">Performance</th>
                        <th class="d-mobile-none">Created</th>
                        <th class="text-end pe-4" style="min-width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($newsletters as $newsletter)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                @if($newsletter->author)
                                    <span class="author-initials" title="{{ $newsletter->author->name }}">
                                        {{ strtoupper(substr($newsletter->author->name, 0, 2)) }}
                                    </span>
                                @endif
                                <div class="campaign-subject">{{ $newsletter->subject }}</div>
                            </div>
                            @if($newsletter->preview_text)
                                <small class="text-muted preview-text-truncate d-block mt-1" title="{{ $newsletter->preview_text }}">
                                    <i class="fas fa-quote-left me-1" style="font-size: 0.6rem; opacity: 0.5;"></i>
                                    {{ $newsletter->preview_text }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <span class="badge status-badge bg-{{ $newsletter->status_color }}">
                                <i class="fas {{ $newsletter->status_icon }} me-1"></i>
                                {{ ucfirst($newsletter->status) }}
                            </span>
                            @if($newsletter->status === 'scheduled' && $newsletter->scheduled_at)
                                <div class="mt-1">
                                    <span class="schedule-badge">
                                        <i class="fas fa-clock"></i>
                                        {{ $newsletter->scheduled_at->format('M d') }}
                                        <strong>{{ $newsletter->scheduled_at->format('h:i A') }}</strong>
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="d-mobile-none">
                            @if($newsletter->recipients_count > 0)
                                <div class="fw-semibold" style="font-size: 0.9rem;">
                                    {{ number_format($newsletter->recipients_count) }}
                                    <small class="text-muted fw-normal">recipients</small>
                                </div>
                                @if(in_array($newsletter->status, ['sent', 'sending']))
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <div class="progress flex-grow-1" style="height: 4px; max-width: 60px;">
                                            <div class="progress-bar bg-success" style="width: {{ $newsletter->delivery_rate }}%"></div>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <span class="delivery-metric sent">
                                                <i class="fas fa-check-circle" style="font-size: 0.6rem;"></i>
                                                {{ $newsletter->sent_count }}
                                            </span>
                                            @if($newsletter->failed_count > 0)
                                                <span class="delivery-metric failed">
                                                    <i class="fas fa-exclamation-circle" style="font-size: 0.6rem;"></i>
                                                    {{ $newsletter->failed_count }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @elseif($newsletter->status === 'failed')
                                    <span class="text-danger small fw-semibold">
                                        <i class="fas fa-times-circle me-1"></i>All failed
                                    </span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="d-mobile-none">
                            <small class="text-muted" style="white-space: nowrap;">
                                {{ $newsletter->created_at->format('M d, Y') }}
                            </small>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                <a href="{{ route('website.admin.newsletter.campaigns.preview', $newsletter) }}" target="_blank"
                                   class="action-btn btn btn-sm btn-light text-muted" title="Preview">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($newsletter->canEdit())
                                    <a href="{{ route('website.admin.newsletter.campaigns.edit', $newsletter) }}"
                                       class="action-btn btn btn-sm btn-light text-muted" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if($newsletter->canSend())
                                    <form action="{{ route('website.admin.newsletter.campaigns.send', $newsletter) }}" method="POST"
                                          onsubmit="return confirm('Send this newsletter to {{ $stats['subscribers'] }} subscribers now?');" class="d-inline">
                                        @csrf
                                        <button type="submit" class="action-btn btn btn-sm btn-light text-success" title="Send Now">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                @endif
                                <div class="dropdown d-inline-flex">
                                    <button class="action-btn btn btn-sm btn-light text-muted dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" title="More" style="border: none;">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.85rem;">
                                        <li>
                                            <form action="{{ route('website.admin.newsletter.campaigns.duplicate', $newsletter) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-copy fa-fw text-muted"></i>Duplicate
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('website.admin.newsletter.campaigns.destroy', $newsletter) }}" method="POST"
                                                  onsubmit="return confirm('Are you sure you want to delete this campaign?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash fa-fw"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="empty-state text-center">
                            <i class="fas fa-envelope-open-text fa-4x mb-3 d-block"></i>
                            <h5>No campaigns yet</h5>
                            <p class="text-muted mb-3">Create your first newsletter campaign to engage with your subscribers.</p>
                            <a href="{{ route('website.admin.newsletter.campaigns.create') }}" class="btn btn-bp-gold">
                                <i class="fas fa-plus me-1"></i> Create Campaign
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($newsletters->hasPages())
            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-center">
                    {{ $newsletters->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
