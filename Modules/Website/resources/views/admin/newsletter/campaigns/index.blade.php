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
    
    .stat-card {
        border-radius: 12px;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
    
    .campaign-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: all 0.3s;
    }
    .campaign-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }
    
    .status-badge {
        font-size: 0.75rem;
        padding: 0.35em 0.75em;
        border-radius: 50px;
    }
    
    .campaign-subject {
        font-weight: 600;
        color: var(--bp-charcoal);
    }
    
    .empty-state {
        padding: 60px 20px;
    }
    .empty-state i {
        color: var(--bp-gold);
        opacity: 0.5;
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

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-gray-800 mb-1">
                <i class="fas fa-paper-plane me-2" style="color: var(--bp-gold);"></i>Newsletter Campaigns
            </h1>
            <p class="text-muted mb-0">Create and manage email campaigns for your subscribers</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('website.admin.newsletter.subscribers') }}" class="btn btn-outline-secondary">
                <i class="fas fa-users me-1"></i> Subscribers
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
                        <div class="rounded-circle p-2" style="background: rgba(200, 161, 101, 0.1);">
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
                        <div class="rounded-circle p-2 bg-light">
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
                        <div class="rounded-circle p-2" style="background: rgba(40, 167, 69, 0.1);">
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
                        <div class="rounded-circle p-2" style="background: rgba(0, 123, 255, 0.1);">
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
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Drafts</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="sending" {{ request('status') == 'sending' ? 'selected' : '' }}>Sending</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
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
                        <th class="ps-4">Campaign</th>
                        <th>Status</th>
                        <th>Recipients</th>
                        <th>Delivery</th>
                        <th>Created</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($newsletters as $newsletter)
                    <tr>
                        <td class="ps-4">
                            <div class="campaign-subject">{{ $newsletter->subject }}</div>
                            @if($newsletter->preview_text)
                                <small class="text-muted">{{ Str::limit($newsletter->preview_text, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge status-badge bg-{{ $newsletter->status_color }}">
                                <i class="fas {{ $newsletter->status_icon }} me-1"></i>
                                {{ ucfirst($newsletter->status) }}
                            </span>
                            @if($newsletter->status === 'scheduled' && $newsletter->scheduled_at)
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>{{ $newsletter->scheduled_at->format('M d, H:i') }}
                                </small>
                            @endif
                        </td>
                        <td>
                            @if($newsletter->recipients_count > 0)
                                <span class="fw-semibold">{{ number_format($newsletter->recipients_count) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($newsletter->status === 'sent' || $newsletter->status === 'sending')
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; width: 80px;">
                                        <div class="progress-bar bg-success" style="width: {{ $newsletter->delivery_rate }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $newsletter->delivery_rate }}%</small>
                                </div>
                                <small class="text-muted">
                                    {{ $newsletter->sent_count }} sent
                                    @if($newsletter->failed_count > 0)
                                        / <span class="text-danger">{{ $newsletter->failed_count }} failed</span>
                                    @endif
                                </small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $newsletter->created_at->format('M d, Y') }}<br>
                                {{ $newsletter->created_at->format('h:i A') }}
                            </small>
                        </td>
                        <td class="text-end pe-4">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('website.admin.newsletter.campaigns.preview', $newsletter) }}" target="_blank">
                                            <i class="fas fa-eye me-2 text-muted"></i>Preview
                                        </a>
                                    </li>
                                    @if($newsletter->canEdit())
                                        <li>
                                            <a class="dropdown-item" href="{{ route('website.admin.newsletter.campaigns.edit', $newsletter) }}">
                                                <i class="fas fa-edit me-2 text-muted"></i>Edit
                                            </a>
                                        </li>
                                    @endif
                                    <li>
                                        <form action="{{ route('website.admin.newsletter.campaigns.duplicate', $newsletter) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-copy me-2 text-muted"></i>Duplicate
                                            </button>
                                        </form>
                                    </li>
                                    @if($newsletter->canSend())
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('website.admin.newsletter.campaigns.send', $newsletter) }}" method="POST" 
                                                  onsubmit="return confirm('Send this newsletter to {{ $stats['subscribers'] }} subscribers now?');">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="fas fa-paper-plane me-2"></i>Send Now
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('website.admin.newsletter.campaigns.destroy', $newsletter) }}" method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete this campaign?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty-state text-center">
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
            <div class="card-footer bg-white">
                {{ $newsletters->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
