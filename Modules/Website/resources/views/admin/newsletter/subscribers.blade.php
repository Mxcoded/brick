@extends('layouts.master')

@section('title', 'Newsletter Subscribers')

@section('page-content')
<div class="container-fluid py-4">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800"><i class="fas fa-envelope-open-text me-2 text-primary"></i>Newsletter Subscribers</h1>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import me-1"></i> Import
            </button>
            <a href="{{ route('website.admin.newsletter.subscribers.export') }}" class="btn btn-success">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 opacity-75">Total Subscribers</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 opacity-75">Active</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['active'] }}</h2>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 opacity-75">Unsubscribed</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['inactive'] }}</h2>
                        </div>
                        <i class="fas fa-user-slash fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('website.admin.newsletter.subscribers') }}" method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Subscribers</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Unsubscribed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Subscribers Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Subscribed At</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscribers as $sub)
                    <tr>
                        <td class="ps-4">
                            <i class="fas fa-user text-muted me-2"></i>
                            {{ $sub->name ?: '—' }}
                        </td>
                        <td>
                            <i class="fas fa-envelope text-muted me-2"></i>
                            {{ $sub->email }}
                        </td>
                        <td>
                            @if($sub->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Unsubscribed</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $sub->subscribed_at?->format('M d, Y H:i') ?? $sub->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group" role="group">
                                <form action="{{ route('website.admin.newsletter.subscribers.toggle', $sub->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $sub->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                            title="{{ $sub->is_active ? 'Deactivate' : 'Reactivate' }}">
                                        <i class="fas {{ $sub->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('website.admin.newsletter.subscribers.destroy', $sub->id) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('Are you sure you want to permanently delete this subscriber?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-envelope-open fa-3x mb-3 d-block"></i>
                            No subscribers found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscribers->hasPages())
            <div class="card-footer bg-white">
                {{ $subscribers->links() }}
            </div>
        @endif
    </div>
{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('website.admin.newsletter.subscribers.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel"><i class="fas fa-file-import me-2"></i>Import Subscribers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Upload Excel/CSV File</label>
                        <input type="file" class="form-control" id="importFile" name="file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">
                            Accepted formats: .xlsx, .xls, .csv (max 5MB)
                        </div>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Your file must have columns named <strong>name</strong> and <strong>email</strong>.
                        Existing subscribers will be updated if found.
                        <a href="{{ route('website.admin.newsletter.subscribers.import.sample') }}" class="alert-link d-block mt-1">
                            <i class="fas fa-download me-1"></i>Download sample template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('import_failures'))
    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ session('import_failures') }} row(s) had validation errors. Check the logs for details.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

</div>
@endsection
