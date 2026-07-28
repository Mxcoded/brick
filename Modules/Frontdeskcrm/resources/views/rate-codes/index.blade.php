@extends('layouts.master')

@section('title', 'Rate Codes')
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Rate Codes</h4>
        <a href="{{ route('frontdesk.rate-codes.create') }}" class="btn btn-gold">
            <i class="fas fa-plus me-2"></i>New Rate Code
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Default Rate</th>
                            <th>Currency</th>
                            <th>Min LOS</th>
                            <th>Max LOS</th>
                            <th>Restrictions</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rateCodes as $rc)
                        <tr>
                            <td><strong>{{ $rc->code }}</strong></td>
                            <td>{{ $rc->name }}</td>
                            <td>{{ number_format($rc->default_rate, 2) }}</td>
                            <td>{{ $rc->currency }}</td>
                            <td>{{ $rc->min_los }}</td>
                            <td>{{ $rc->max_los ?? '∞' }}</td>
                            <td>
                                @if($rc->closed_to_arrival)<span class="badge bg-warning me-1">CTA</span>@endif
                                @if($rc->closed_to_departure)<span class="badge bg-danger me-1">CTD</span>@endif
                                @if(!$rc->apply_weekdays)<span class="badge bg-secondary me-1">No WD</span>@endif
                                @if(!$rc->apply_weekends)<span class="badge bg-secondary me-1">No WE</span>@endif
                                @if(!$rc->closed_to_arrival && !$rc->closed_to_departure && $rc->apply_weekdays && $rc->apply_weekends)
                                    <span class="text-muted small">None</span>
                                @endif
                            </td>
                            <td>
                                @if($rc->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('frontdesk.rate-codes.calendar', $rc) }}" class="btn btn-sm btn-outline-info me-1" title="Rate Calendar">
                                    <i class="fas fa-calendar-alt"></i>
                                </a>
                                <a href="{{ route('frontdesk.rate-codes.edit', $rc) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('frontdesk.rate-codes.destroy', $rc) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete rate code {{ $rc->code }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center py-4 text-muted">No rate codes defined yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $rateCodes->links() }}
        </div>
    </div>
</div>
@endsection
