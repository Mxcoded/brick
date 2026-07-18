@extends('layouts.master')

@section('title', 'Shared Files')

@section('page-content')
<div class="container-fluid py-4 px-lg-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color: #1a1a2e;"><i class="fas fa-folder-open me-2" style="color: #C8A165;"></i>Shared Files</h1>
            <p class="text-muted small mb-0">
                {{ $totalCount }} file(s) &middot;
                {{ $totalSize > 0 ? round($totalSize / 1073741824, 2) : 0 }} GB used
                @if($diskTotal > 0)
                    &middot; {{ round(($diskTotal - $diskFree) / $diskTotal * 100) }}% of disk
                @endif
            </p>
        </div>
        <a href="{{ route('staff.documents.create') }}" class="btn btn-sm px-3 fw-semibold" style="background: #C8A165; color: #fff; border: none; border-radius: 8px;">
            <i class="fas fa-upload me-1"></i> Upload Files
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">{{ session('warning') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="alert alert-light border d-flex align-items-center py-2 px-3 mb-3 small" style="border-radius: 8px; color: #7f8c8d;">
        <i class="fas fa-info-circle me-2" style="color: #C8A165;"></i>
        Physical files are automatically removed after <strong class="mx-1">7 days</strong>. Records are kept for reporting.
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="section-header mb-0"><i class="fas fa-file-alt me-2" style="color: #C8A165;"></i>All Documents</h6>
            <form method="GET" class="d-flex gap-2" style="max-width: 320px;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search files..." value="{{ request('search') }}">
                </div>
                @if(request('search'))
                    <a href="{{ route('staff.documents.index') }}" class="btn btn-sm btn-outline-primary">Clear</a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">File</th>
                        <th>Size</th>
                        <th>Uploaded</th>
                        <th>Downloads</th>
                        <th>Expires</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fas {{ $doc->icon() }} fa-lg" style="color: #C8A165; width: 20px; text-align: center;"></i>
                                    <div class="min-width-0">
                                        <div class="fw-semibold text-truncate" style="max-width: 300px;" title="{{ $doc->filename }}">{{ $doc->filename }}</div>
                                        @if($doc->description)
                                            <small class="text-muted text-truncate" style="max-width: 300px;">{{ $doc->description }}</small>
                                        @endif
                                        <small class="text-muted" style="font-size:0.7rem;">by {{ $doc->uploader?->name ?? '—' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-nowrap">{{ $doc->isArchived() ? '—' : $doc->formattedSize() }}</td>
                            <td class="text-nowrap small">{{ $doc->created_at->format('M d, Y') }}<br><span class="text-muted">{{ $doc->created_at->format('g:i a') }}</span></td>
                            <td>{{ $doc->downloads_count }}</td>
                            @php
                                $expiresAt = $doc->created_at->addDays(7);
                                $hoursLeft = now()->diffInHours($expiresAt, false);
                                $daysLeft = (int) floor($hoursLeft / 24);
                                $hoursRemainder = $hoursLeft % 24;
                            @endphp
                            <td class="text-nowrap">
                                @if($doc->isArchived())
                                    <span style="color: #7f8c8d; font-weight: 600;">removed</span>
                                @elseif($doc->isExpired())
                                    <span style="color: #c0392b; font-weight: 600;">expired</span>
                                @elseif($hoursLeft <= 48)
                                    <div style="color: #e67e22; font-weight: 600; line-height: 1.3;">
                                        @if($daysLeft > 0){{ $daysLeft }}d @endif{{ $hoursRemainder }}h
                                        <div style="font-weight:400;font-size:0.7rem;color:#e67e22;">remaining</div>
                                    </div>
                                @else
                                    <div style="line-height: 1.3;">
                                        <span style="font-weight: 600;">{{ $daysLeft }}d</span>
                                        <div style="font-weight:400;font-size:0.7rem;color:#7f8c8d;">{{ $expiresAt->format('M d') }}</div>
                                    </div>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex gap-1 justify-content-end">
                                    @if($doc->isArchived())
                                        <span class="badge bg-secondary bg-opacity-10 text-muted px-3 py-2" style="font-weight: 500; border-radius: 8px; font-size: 0.75rem;">
                                            <i class="fas fa-archive me-1"></i> File has been removed
                                        </span>
                                    @elseif($doc->isExpired())
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2" style="font-weight: 500; border-radius: 8px; font-size: 0.75rem;">
                                            <i class="fas fa-clock me-1"></i> File has been removed
                                        </span>
                                    @else
                                        <button type="button" class="btn btn-sm copy-share-link" data-url="{{ $doc->shareUrl() }}" title="Copy share link" style="background: #f0f7ff; color: #2980b9; border: 1px solid #d0e4f5; border-radius: 8px;">
                                            <i class="fas fa-link"></i>
                                        </button>
                                        <a href="mailto:?subject=Shared%20file%3A%20{{ rawurlencode($doc->filename) }}&body=Hi%2C%0D%0AA%20file%20has%20been%20shared%20with%20you%3A%0D%0A{{ rawurlencode($doc->shareUrl()) }}%0D%0A%0D%0ARegards" class="btn btn-sm" title="Share via email" style="background: #f0f7ff; color: #2980b9; border: 1px solid #d0e4f5; border-radius: 8px;">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                        <a href="{{ route('staff.documents.download', $doc) }}" class="btn btn-sm" style="background: #f8f8f8; color: #2c3e50; border: 1px solid #e0e0e0; border-radius: 8px;">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    @endif
                                    @canany(['employees.update', 'employees.delete'])
                                    <form method="POST" action="{{ route('staff.documents.destroy', $doc) }}" onsubmit="return confirm('Delete {{ $doc->filename }}?')" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm" style="background: #fde8e8; color: #c0392b; border: 1px solid #f5c6c6; border-radius: 8px;">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                @if(request('search'))
                                    No files match your search.
                                @else
                                    No files uploaded yet. <a href="{{ route('staff.documents.create') }}" style="color: #C8A165;">Upload the first file</a>.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($documents->hasPages())
            <div class="card-footer bg-transparent border-top py-3">
                {{ $documents->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('styles')
<style>
    .section-header { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #2c3e50; }
    .table thead th { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: #7f8c8d; border-bottom: 2px solid #f0f0f0; padding: 12px 16px; }
    .table tbody td { padding: 14px 16px; border-bottom: 1px solid #f5f5f5; font-size: 0.85rem; }
    .table tbody tr:hover td { background: #fafaf8; }
    .pagination { margin-bottom: 0; }
    .share-toast { position: fixed; bottom: 30px; right: 30px; z-index: 9999; padding: 12px 24px; border-radius: 10px; background: #1a1a2e; color: #fff; font-size: 0.85rem; box-shadow: 0 4px 20px rgba(0,0,0,0.2); opacity: 0; transform: translateY(20px); transition: all 0.3s ease; pointer-events: none; }
    .share-toast.show { opacity: 1; transform: translateY(0); }
</style>
@endsection

@section('page-scripts')
<script>
document.querySelectorAll('.copy-share-link').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var url = this.getAttribute('data-url');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function () {
                showToast('Share link copied to clipboard');
            });
        } else {
            var textarea = document.createElement('textarea');
            textarea.value = url;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showToast('Share link copied to clipboard');
        }
    });
});

function showToast(msg) {
    var existing = document.querySelector('.share-toast');
    if (existing) existing.remove();
    var toast = document.createElement('div');
    toast.className = 'share-toast';
    toast.innerHTML = '<i class="fas fa-check-circle me-2" style="color: #27ae60;"></i> ' + msg;
    document.body.appendChild(toast);
    requestAnimationFrame(function () {
        toast.classList.add('show');
    });
    setTimeout(function () {
        toast.classList.remove('show');
        setTimeout(function () { toast.remove(); }, 300);
    }, 2500);
}
</script>
@endsection
