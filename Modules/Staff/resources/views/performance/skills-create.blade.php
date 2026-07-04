@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">HR Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('staff.performance.skills') }}">Skills Matrix</a></li>
    <li class="breadcrumb-item active">Add Skill</li>
@endsection

@section('page-content')
<div class="container-fluid my-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="mb-1">Add Skill</h1>
            <p class="text-muted mb-0">Record an employee's skill or certification</p>
        </div>
        <a href="{{ route('staff.performance.skills') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Skills
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

    <form method="POST" action="{{ route('staff.performance.skills-store') }}" class="card shadow-sm">
        @csrf
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
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
                <div class="col-md-6">
                    <label for="skill_name" class="form-label">Skill Name <span class="text-danger">*</span></label>
                    <input type="text" name="skill_name" id="skill_name" class="form-control @error('skill_name') is-invalid @enderror"
                           value="{{ old('skill_name') }}" placeholder="e.g., Python, Leadership, French" required>
                    @error('skill_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                    <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                        <option value="technical" {{ old('category') === 'technical' ? 'selected' : '' }}>Technical</option>
                        <option value="soft" {{ old('category') === 'soft' ? 'selected' : '' }}>Soft Skills</option>
                        <option value="language" {{ old('category') === 'language' ? 'selected' : '' }}>Language</option>
                        <option value="certification" {{ old('category') === 'certification' ? 'selected' : '' }}>Certification</option>
                        <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="proficiency_level" class="form-label">Proficiency Level <span class="text-danger">*</span></label>
                    <select name="proficiency_level" id="proficiency_level" class="form-select @error('proficiency_level') is-invalid @enderror" required>
                        <option value="beginner" {{ old('proficiency_level') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="intermediate" {{ old('proficiency_level') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced" {{ old('proficiency_level') === 'advanced' ? 'selected' : '' }}>Advanced</option>
                        <option value="expert" {{ old('proficiency_level') === 'expert' ? 'selected' : '' }}>Expert</option>
                    </select>
                    @error('proficiency_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="years_experience" class="form-label">Years Experience</label>
                    <input type="number" name="years_experience" id="years_experience" step="0.1" min="0" max="50"
                           class="form-control @error('years_experience') is-invalid @enderror"
                           value="{{ old('years_experience') }}" placeholder="e.g., 3.5">
                    @error('years_experience')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="last_used_date" class="form-label">Last Used Date</label>
                    <input type="date" name="last_used_date" id="last_used_date"
                           class="form-control @error('last_used_date') is-invalid @enderror"
                           value="{{ old('last_used_date') }}">
                    @error('last_used_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_certified" id="is_certified" class="form-check-input"
                               value="1" {{ old('is_certified') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_certified">Certified</label>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="notes" class="form-label">Notes</label>
                <textarea name="notes" id="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Add Skill
            </button>
        </div>
    </form>

</div>
@endsection
