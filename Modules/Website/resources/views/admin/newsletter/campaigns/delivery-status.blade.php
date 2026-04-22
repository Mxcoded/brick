@extends('layouts.master')

@section('title', 'Delivery Status - ' . $campaign->subject)

@section('page-content')
<style>
    :root {
        --bp-gold: #C8A165;
        --bp-gold-light: #D4B88A;
        --bp-charcoal: #333333;
    }
    
    .status-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    
    .progress-wrapper {
        background: linear-gradient(135deg, var(--bp-charcoal) 0%, #1a1a2e 100%);
        border-radius: 16px;
        padding: 2rem;
        color: white;
    }
    
    .progress-circle {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: conic-gradient(
            var(--bp-gold) calc(var(--progress) * 1%),
            rgba(255,255,255,0.1) calc(var(--progress) * 1%)
        );
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        position: relative;
    }
    
    .progress-inner {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        background: var(--bp-charcoal);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .progress-percent {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--bp-gold);
    }
    
    .progress-label {
        font-size: 0.85rem;
        opacity: 0.7;
    }
    
    .stat-box {
        text-align: center;
        padding: 1.5rem;
        background: rgba(255,255,255,0.05);
        border-radius: 12px;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 800;
    }
    
    .stat-label {
        font-size: 0.8rem;
        opacity: 0.7;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-box.sent .stat-value { color: #22c55e; }
    .stat-box.failed .stat-value { color: #ef4444; }
    .stat-box.pending .stat-value { color: #f59e0b; }
    
    .failed-list {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .failed-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 1rem;
        background: #fff5f5;
        border-radius: 10px;
        margin-bottom: 0.75rem;
        border-left: 4px solid #ef4444;
    }
    
    .failed-email {
        font-weight: 600;
        color: var(--bp-charcoal);
    }
    
    .failed-error {
        font-size: 0.85rem;
        color: #666;
        margin-top: 4px;
    }
    
    .failed-time {
        font-size: 0.75rem;
        color: #999;
        white-space: nowrap;
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
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .status-badge.sending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-badge.sent {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-badge.failed {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .live-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        color: #22c55e;
    }
    
    .live-dot {
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.2); }
    }
    
    .completed-overlay {
        display: none;
    }
    
    .completed-overlay.show {
        display: flex;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.6);
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    
    .completed-modal {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        text-align: center;
        max-width: 400px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }
    
    .completed-icon {
        width: 80px;
        height: 80px;
        background: #d1fae5;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: #22c55e;
        font-size: 2.5rem;
    }
</style>

<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('website.admin.newsletter.campaigns.index') }}" class="text-muted text-decoration-none mb-2 d-inline-block">
                <i class="fas fa-arrow-left me-1"></i> Back to Campaigns
            </a>
            <h1 class="h3 text-gray-800 mb-0">
                <i class="fas fa-paper-plane me-2" style="color: var(--bp-gold);"></i>
                Delivery Status
            </h1>
            <p class="text-muted mb-0 mt-1">{{ $campaign->subject }}</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="live-indicator" id="live-indicator">
                <span class="live-dot"></span>
                <span>Live Updates</span>
            </div>
            <span class="status-badge {{ $campaign->status }}" id="campaign-status">
                <i class="fas {{ $campaign->status_icon }}"></i>
                {{ ucfirst($campaign->status) }}
            </span>
        </div>
    </div>

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Progress Section --}}
        <div class="col-lg-8 mb-4">
            <div class="card status-card">
                <div class="card-body p-0">
                    <div class="progress-wrapper">
                        <div class="row align-items-center">
                            <div class="col-md-5 text-center mb-4 mb-md-0">
                                <div class="progress-circle" id="progress-circle" style="--progress: {{ ($stats['sent'] + $stats['failed']) / max($stats['total'], 1) * 100 }}">
                                    <div class="progress-inner">
                                        <div class="progress-percent" id="progress-percent">
                                            {{ round(($stats['sent'] + $stats['failed']) / max($stats['total'], 1) * 100) }}%
                                        </div>
                                        <div class="progress-label">Complete</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="row g-3">
                                    <div class="col-4">
                                        <div class="stat-box sent">
                                            <div class="stat-value" id="stat-sent">{{ $stats['sent'] }}</div>
                                            <div class="stat-label">Sent</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-box failed">
                                            <div class="stat-value" id="stat-failed">{{ $stats['failed'] }}</div>
                                            <div class="stat-label">Failed</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-box pending">
                                            <div class="stat-value" id="stat-pending">{{ $stats['pending'] }}</div>
                                            <div class="stat-label">Pending</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 text-center">
                                    <small class="opacity-75">
                                        Total Recipients: <strong>{{ $stats['total'] }}</strong>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions Section --}}
        <div class="col-lg-4 mb-4">
            <div class="card status-card h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="mb-0">
                        <i class="fas fa-cog me-2 text-muted"></i>Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('website.admin.newsletter.campaigns.preview', $campaign) }}" 
                           target="_blank" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-eye me-2"></i>Preview Email
                        </a>
                        
                        @if($stats['failed'] > 0)
                        <form action="{{ route('website.admin.newsletter.campaigns.retry-failed', $campaign) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100" id="retry-btn">
                                <i class="fas fa-redo me-2"></i>Retry Failed (<span id="retry-count">{{ $stats['failed'] }}</span>)
                            </button>
                        </form>
                        @endif
                        
                        <a href="{{ route('website.admin.newsletter.campaigns.index') }}" 
                           class="btn btn-bp-gold">
                            <i class="fas fa-list me-2"></i>Back to Campaigns
                        </a>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="small text-muted">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Created:</span>
                            <strong>{{ $campaign->created_at->format('M d, Y h:i A') }}</strong>
                        </div>
                        @if($campaign->sent_at)
                        <div class="d-flex justify-content-between">
                            <span>Sent At:</span>
                            <strong>{{ $campaign->sent_at->format('M d, Y h:i A') }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Failed Deliveries Section --}}
    <div class="card status-card" id="failed-section" style="{{ $stats['failed'] == 0 ? 'display: none;' : '' }}">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Failed Deliveries (<span id="failed-count">{{ $stats['failed'] }}</span>)
            </h5>
        </div>
        <div class="card-body">
            <div class="failed-list" id="failed-list">
                @forelse($failedEmails as $log)
                    <div class="failed-item">
                        <div>
                            <div class="failed-email">{{ $log->email }}</div>
                            <div class="failed-error">{{ $log->error_message ?? 'Unknown error' }}</div>
                        </div>
                        <div class="failed-time">{{ $log->failed_at?->diffForHumans() }}</div>
                    </div>
                @empty
                    <p class="text-muted text-center mb-0">No failed deliveries</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Completion Modal --}}
<div class="completed-overlay" id="completed-overlay">
    <div class="completed-modal">
        <div class="completed-icon">
            <i class="fas fa-check"></i>
        </div>
        <h3 class="mb-2">Delivery Complete!</h3>
        <p class="text-muted mb-4">
            Your newsletter has been sent to <strong id="final-sent">0</strong> subscribers.
            <span id="final-failed-text" style="display: none;">
                <br><span class="text-danger"><strong id="final-failed">0</strong> failed</span>
            </span>
        </p>
        <div class="d-grid gap-2">
            <a href="{{ route('website.admin.newsletter.campaigns.index') }}" class="btn btn-bp-gold btn-lg">
                <i class="fas fa-list me-2"></i>View All Campaigns
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('completed-overlay').classList.remove('show')">
                Stay on This Page
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const campaignId = {{ $campaign->id }};
    const apiUrl = '{{ route("website.admin.newsletter.campaigns.delivery-status.api", $campaign) }}';
    let isCompleted = {{ $campaign->status === 'sent' ? 'true' : 'false' }};
    let pollInterval = null;
    
    function updateUI(data) {
        // Update stats
        document.getElementById('stat-sent').textContent = data.sent;
        document.getElementById('stat-failed').textContent = data.failed;
        document.getElementById('stat-pending').textContent = data.pending;
        
        // Update progress circle
        const progress = data.progress;
        document.getElementById('progress-circle').style.setProperty('--progress', progress);
        document.getElementById('progress-percent').textContent = Math.round(progress) + '%';
        
        // Update retry count
        const retryCount = document.getElementById('retry-count');
        if (retryCount) {
            retryCount.textContent = data.failed;
        }
        
        // Update failed count in header
        document.getElementById('failed-count').textContent = data.failed;
        
        // Show/hide failed section
        const failedSection = document.getElementById('failed-section');
        if (data.failed > 0) {
            failedSection.style.display = '';
        }
        
        // Update failed list with recent failures
        if (data.recent_failures && data.recent_failures.length > 0) {
            const failedList = document.getElementById('failed-list');
            let html = '';
            data.recent_failures.forEach(function(failure) {
                html += `
                    <div class="failed-item">
                        <div>
                            <div class="failed-email">${failure.email}</div>
                            <div class="failed-error">${failure.error || 'Unknown error'}</div>
                        </div>
                        <div class="failed-time">${failure.failed_at}</div>
                    </div>
                `;
            });
            if (data.failed > 5) {
                html += `<p class="text-center text-muted mt-3">And ${data.failed - 5} more...</p>`;
            }
            failedList.innerHTML = html;
        }
        
        // Update status badge
        const statusBadge = document.getElementById('campaign-status');
        statusBadge.className = 'status-badge ' + data.status;
        
        const statusIcons = {
            'draft': 'fa-file-alt',
            'scheduled': 'fa-clock',
            'sending': 'fa-spinner fa-spin',
            'sent': 'fa-check-circle',
            'failed': 'fa-exclamation-circle'
        };
        
        statusBadge.innerHTML = `<i class="fas ${statusIcons[data.status] || 'fa-circle'}"></i> ${data.status.charAt(0).toUpperCase() + data.status.slice(1)}`;
        
        // Check if completed
        if (data.completed && !isCompleted) {
            isCompleted = true;
            
            // Hide live indicator
            document.getElementById('live-indicator').style.display = 'none';
            
            // Show completion modal
            document.getElementById('final-sent').textContent = data.sent;
            if (data.failed > 0) {
                document.getElementById('final-failed').textContent = data.failed;
                document.getElementById('final-failed-text').style.display = '';
            }
            document.getElementById('completed-overlay').classList.add('show');
            
            // Stop polling
            if (pollInterval) {
                clearInterval(pollInterval);
            }
        }
    }
    
    function pollStatus() {
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                updateUI(data);
            })
            .catch(error => {
                console.error('Error fetching status:', error);
            });
    }
    
    // Start polling if not completed
    if (!isCompleted) {
        // Poll every 2 seconds
        pollInterval = setInterval(pollStatus, 2000);
        
        // Initial poll
        pollStatus();
    } else {
        // Hide live indicator if already completed
        document.getElementById('live-indicator').style.display = 'none';
    }
});
</script>
@endsection
