@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">HR Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('staff.performance.index') }}">Performance Reviews</a></li>
    <li class="breadcrumb-item active">{{ $performanceReview->employee->name }}</li>
@endsection

@section('page-content')
<div class="container-fluid my-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="mb-1">{{ $performanceReview->employee->name }}</h1>
            <p class="text-muted mb-0">
                {{ ucfirst($performanceReview->review_period) }} Review &middot;
                {{ $performanceReview->review_date->format('F d, Y') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('staff.performance.edit', $performanceReview) }}" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <a href="{{ route('staff.performance.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card shadow-sm h-100 text-center">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Overall Score</h6>
                    @php
                        $score = $performanceReview->overall_score;
                        $color = $score >= 4 ? 'success' : ($score >= 3 ? 'warning' : 'danger');
                    @endphp
                    <div class="display-3 fw-bold text-{{ $color }}">{{ number_format($score, 1) }}</div>
                    <small class="text-muted">out of 5.0</small>
                    <hr>
                    <div class="text-start small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Status</span>
                            <span class="badge bg-{{ $performanceReview->status === 'submitted' ? 'success' : 'secondary' }}">
                                {{ ucfirst($performanceReview->status) }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Department</span>
                            <span>{{ $performanceReview->employee->department ?? '—' }}</span>
                        </div>
                        @if ($performanceReview->reviewer)
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Reviewed By</span>
                                <span>{{ $performanceReview->reviewer->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Rating Breakdown</h5>
                </div>
                <div class="card-body">
                    @php
                        $ratings = [
                            'Punctuality & Attendance' => $performanceReview->rating_punctuality,
                            'Teamwork & Collaboration' => $performanceReview->rating_teamwork,
                            'Communication Skills' => $performanceReview->rating_communication,
                            'Quality of Work' => $performanceReview->rating_quality,
                            'Initiative & Problem Solving' => $performanceReview->rating_initiative,
                        ];
                    @endphp
                    @foreach ($ratings as $label => $rating)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>{{ $label }}</span>
                                <span class="fw-bold">{{ $rating }}/5</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                @php
                                    $barColor = $rating >= 4 ? 'bg-success' : ($rating >= 3 ? 'bg-warning' : 'bg-danger');
                                @endphp
                                <div class="progress-bar {{ $barColor }}" style="width: {{ ($rating / 5) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-star text-success me-2"></i>Strengths</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $performanceReview->strengths ?: 'No strengths recorded.' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-arrow-up text-warning me-2"></i>Areas for Improvement</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $performanceReview->areas_for_improvement ?: 'No areas for improvement recorded.' }}</p>
                </div>
            </div>
        </div>
    </div>

    @if ($performanceReview->comments)
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-comment me-2 text-gold"></i>Comments</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $performanceReview->comments }}</p>
            </div>
        </div>
    @endif

</div>
@endsection
