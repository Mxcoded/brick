@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">HR Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('staff.performance.index') }}">Performance Reviews</a></li>
    <li class="breadcrumb-item active">Skills Matrix</li>
@endsection

@section('page-content')
<div class="container-fluid my-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="mb-1">Skills Matrix</h1>
            <p class="text-muted mb-0">Employee skills &amp; proficiency levels</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('staff.performance.skills-create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Skill
            </a>
            <a href="{{ route('staff.performance.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-clipboard-list me-1"></i> Reviews
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="GET" class="row g-2 mb-4">
        <div class="col-auto">
            <select name="category" class="form-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <option value="technical" {{ $category === 'technical' ? 'selected' : '' }}>Technical</option>
                <option value="soft" {{ $category === 'soft' ? 'selected' : '' }}>Soft Skills</option>
                <option value="language" {{ $category === 'language' ? 'selected' : '' }}>Language</option>
                <option value="certification" {{ $category === 'certification' ? 'selected' : '' }}>Certification</option>
                <option value="other" {{ $category === 'other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>
        <div class="col-auto">
            <select name="department" class="form-select" onchange="this.form.submit()">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
        </div>
    </form>

    @forelse ($skills as $category => $categorySkills)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex align-items-center">
                <i class="fas fa-tag me-2 text-gold"></i>
                <h5 class="mb-0">{{ ucfirst($category) }}</h5>
                <span class="badge bg-secondary ms-2">{{ $categorySkills->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Skill</th>
                                <th class="text-center">Proficiency</th>
                                <th class="text-center">Years Exp</th>
                                <th class="text-center">Certified</th>
                                <th class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categorySkills as $skill)
                                <tr>
                                    <td><strong>{{ $skill->employee->name }}</strong></td>
                                    <td>{{ $skill->employee->department ?? '—' }}</td>
                                    <td>{{ $skill->skill_name }}</td>
                                    <td class="text-center">
                                        @php
                                            $levelColor = match($skill->proficiency_level) {
                                                'expert' => 'danger',
                                                'advanced' => 'warning',
                                                'intermediate' => 'info',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $levelColor }}">{{ ucfirst($skill->proficiency_level) }}</span>
                                    </td>
                                    <td class="text-center">{{ $skill->years_experience ? $skill->years_experience . 'y' : '—' }}</td>
                                    <td class="text-center">
                                        @if ($skill->is_certified)
                                            <i class="fas fa-certificate text-gold" title="Certified"></i>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('staff.performance.skills-destroy', $skill) }}" method="POST"
                                              onsubmit="return confirm('Remove this skill?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Remove"><i class="fas fa-times"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-brain fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">No skills recorded yet. <a href="{{ route('staff.performance.skills-create') }}">Add one</a>.</p>
            </div>
        </div>
    @endforelse

</div>
@endsection
