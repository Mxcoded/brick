@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">HR Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('staff.performance.index') }}">Performance Reviews</a></li>
    <li class="breadcrumb-item active">New Review</li>
@endsection

@section('page-content')
<div class="container-fluid my-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="mb-1">New Performance Review</h1>
            <p class="text-muted mb-0">Rate the employee across five dimensions</p>
        </div>
        <a href="{{ route('staff.performance.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Reviews
        </a>
    </div>

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

    <form method="POST" action="{{ route('staff.performance.store') }}" class="card shadow-sm">
        @csrf
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                    <select name="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                        <option value="">Select Employee</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }} ({{ $emp->department ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="review_date" class="form-label">Review Date <span class="text-danger">*</span></label>
                    <input type="date" name="review_date" id="review_date" class="form-control @error('review_date') is-invalid @enderror"
                           value="{{ old('review_date', now()->format('Y-m-d')) }}" required>
                    @error('review_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="review_period" class="form-label">Review Period <span class="text-danger">*</span></label>
                    <select name="review_period" id="review_period" class="form-select @error('review_period') is-invalid @enderror" required>
                        <option value="annual" {{ old('review_period') === 'annual' ? 'selected' : '' }}>Annual</option>
                        <option value="quarterly" {{ old('review_period') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="probation" {{ old('review_period') === 'probation' ? 'selected' : '' }}>Probation</option>
                    </select>
                    @error('review_period')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <h5 class="mb-3">Ratings (1 = Needs Improvement, 5 = Excellent)</h5>
            <div class="row g-3 mb-4">
                @php
                    $dimensions = [
                        'rating_punctuality' => 'Punctuality & Attendance',
                        'rating_teamwork' => 'Teamwork & Collaboration',
                        'rating_communication' => 'Communication Skills',
                        'rating_quality' => 'Quality of Work',
                        'rating_initiative' => 'Initiative & Problem Solving',
                    ];
                @endphp
                @foreach ($dimensions as $field => $label)
                    <div class="col-md-4">
                        <label for="{{ $field }}" class="form-label">{{ $label }} <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input @error($field) is-invalid @enderror" type="radio"
                                           name="{{ $field }}" id="{{ $field }}_{{ $i }}"
                                           value="{{ $i }}" {{ old($field, 3) == $i ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="{{ $field }}_{{ $i }}">{{ $i }}</label>
                                </div>
                            @endfor
                        </div>
                        @error($field)<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="strengths" class="form-label">Strengths</label>
                    <textarea name="strengths" id="strengths" rows="3" class="form-control @error('strengths') is-invalid @enderror">{{ old('strengths') }}</textarea>
                    @error('strengths')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="areas_for_improvement" class="form-label">Areas for Improvement</label>
                    <textarea name="areas_for_improvement" id="areas_for_improvement" rows="3" class="form-control @error('areas_for_improvement') is-invalid @enderror">{{ old('areas_for_improvement') }}</textarea>
                    @error('areas_for_improvement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="comments" class="form-label">Additional Comments</label>
                <textarea name="comments" id="comments" rows="3" class="form-control @error('comments') is-invalid @enderror">{{ old('comments') }}</textarea>
                @error('comments')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Submit Review
                </button>
                <a href="{{ route('staff.performance.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </form>

</div>
@endsection
