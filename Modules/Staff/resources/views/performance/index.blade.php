@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">HR Dashboard</a></li>
    <li class="breadcrumb-item active">Performance Reviews</li>
@endsection

@section('page-content')
<div class="container-fluid my-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="mb-1">Performance Reviews</h1>
            <p class="text-muted mb-0">Employee appraisals &amp; evaluations</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('staff.performance.skills') }}" class="btn btn-outline-primary">
                <i class="fas fa-brain me-1"></i> Skills Matrix
            </a>
            <a href="{{ route('staff.performance.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Review
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong><i class="fas fa-exclamation-triangle me-1"></i> Please fix the following errors:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm text-center py-3">
                <h4 class="mb-0">{{ $summary->total }}</h4>
                <small>Total Reviews</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm text-center py-3">
                <h4 class="mb-0">{{ $summary->submitted }}</h4>
                <small>Submitted</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark shadow-sm text-center py-3">
                <h4 class="mb-0">{{ $summary->draft }}</h4>
                <small>Drafts</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm text-center py-3">
                <h4 class="mb-0">{{ $summary->avg_score }} / 5</h4>
                <small>Avg Score</small>
            </div>
        </div>
    </div>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-auto">
            <select name="department" class="form-select" onchange="this.form.submit()">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select name="period" class="form-select" onchange="this.form.submit()">
                <option value="">All Periods</option>
                <option value="quarterly" {{ $period === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                <option value="annual" {{ $period === 'annual' ? 'selected' : '' }}>Annual</option>
                <option value="probation" {{ $period === 'probation' ? 'selected' : '' }}>Probation</option>
            </select>
        </div>
        <div class="col-auto">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="submitted" {{ $status === 'submitted' ? 'selected' : '' }}>Submitted</option>
            </select>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Review Date</th>
                            <th>Period</th>
                            <th class="text-center">Score</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reviews as $review)
                            <tr>
                                <td><strong>{{ $review->employee->name }}</strong></td>
                                <td>{{ $review->employee->department ?? '—' }}</td>
                                <td>{{ $review->review_date->format('M d, Y') }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($review->review_period) }}</span></td>
                                <td class="text-center">
                                    @php
                                        $scoreColor = $review->overall_score >= 4 ? 'success' : ($review->overall_score >= 3 ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge bg-{{ $scoreColor }} fs-6">{{ number_format($review->overall_score, 1) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $review->status === 'submitted' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($review->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('staff.performance.show', $review) }}" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('staff.performance.edit', $review) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-clipboard-list fa-2x mb-2 d-block"></i>
                                    No performance reviews yet. <a href="{{ route('staff.performance.create') }}">Create one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $reviews->links() }}
    </div>

</div>
@endsection
