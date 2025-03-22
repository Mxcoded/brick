@extends('staff::layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Staff Details</li>
@endsection

@section('page-content')
    <div class="container my-4">
        <h1 class="mb-4 fw-bold">Staff Details</h1>

        <!-- Main Employee Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $employee->name }}</h5>
                <span class="badge bg-light text-dark">{{ $employee->staff_code ?? 'N/A' }}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        @if ($employee->profile_image)
                            <img src="{{ asset('storage/' . $employee->profile_image) }}" alt="Profile Image"
                                class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <div class="mb-3 bg-light rounded-circle d-flex align-items-center justify-content-center text-muted"
                                style="width: 150px; height: 150px;">
                                <i class="fas fa-user fa-3x"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-map-marker-alt me-2"></i> Place of Birth:</strong> {{ $employee->place_of_birth }}</p>
                                <p><strong><i class="fas fa-flag me-2"></i> State of Origin:</strong> {{ $employee->state_of_origin }}</p>
                                <p><strong><i class="fas fa-city me-2"></i> LGA:</strong> {{ $employee->lga }}</p>
                                <p><strong><i class="fas fa-globe me-2"></i> Nationality:</strong> {{ $employee->nationality }}</p>
                                <p><strong><i class="fas fa-venus-mars me-2"></i> Gender:</strong> {{ $employee->gender }}</p>
                                <p><strong><i class="fas fa-birthday-cake me-2"></i> Date of Birth:</strong> {{ $employee->date_of_birth }}</p>
                                <p><strong><i class="fas fa-briefcase me-2"></i> Position:</strong> {{ $employee->position }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-ring me-2"></i> Marital Status:</strong> {{ $employee->marital_status }}</p>
                                <p><strong><i class="fas fa-tint me-2"></i> Blood Group:</strong> {{ $employee->blood_group }}</p>
                                <p><strong><i class="fas fa-dna me-2"></i> Genotype:</strong> {{ $employee->genotype }}</p>
                                <p><strong><i class="fas fa-phone me-2"></i> Phone Number:</strong> {{ $employee->phone_number }}</p>
                                <p><strong><i class="fas fa-home me-2"></i> Residential Address:</strong> {{ $employee->residential_address }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h5 class="border-bottom pb-2"><i class="fas fa-clock me-2"></i> Employment Status</h5>
                    <p>
                        @if ($employee->isActive())
                            <span class="badge bg-success me-2">Active</span> {{ $employee->start_date }} - Present
                            <br><strong>Branch:</strong> {{ $employee->branch_name ?? 'N/A' }}
                        @else
                            <span class="badge bg-danger me-2">Inactive</span> {{ $employee->start_date }} - {{ $employee->end_date }}
                            <br><strong>Reason:</strong> {{ $employee->leaving_reason ?? 'N/A' }}
                            <br><strong>Note:</strong> {{ $employee->note_for_leaving ?? 'N/A' }}
                            <br><strong>Branch:</strong> {{ $employee->branch_name ?? 'N/A' }}
                            @if ($employee->resignation_letter)
                                <br><a href="{{ Storage::url($employee->resignation_letter) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-file-pdf me-1"></i> View Resignation Letter
                                </a>
                            @endif
                        @endif
                    </p>
                </div>

                <div class="mt-4 row">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2"><i class="fas fa-user-friends me-2"></i> Next of Kin</h5>
                        <p><strong>Name:</strong> {{ $employee->next_of_kin_name }}</p>
                        <p><strong>Phone:</strong> {{ $employee->next_of_kin_phone }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2"><i class="fas fa-ambulance me-2"></i> ICE Contact</h5>
                        <p><strong>Name:</strong> {{ $employee->ice_contact_name }}</p>
                        <p><strong>Phone:</strong> {{ $employee->ice_contact_phone }}</p>
                    </div>
                </div>

                <div class="mt-4">
                    <h5 class="border-bottom pb-2"><i class="fas fa-file-alt me-2"></i> CV</h5>
                    @if ($employee->cv_path)
                        <a href="{{ asset('storage/' . $employee->cv_path) }}" target="_blank" class="btn btn-sm btn-primary">
                            <i class="fas fa-download me-1"></i> Download CV
                        </a>
                    @else
                        <p class="text-muted">No CV uploaded.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Employment History Card -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i> Employment History</h5>
            </div>
            <div class="card-body">
                @if ($employee->employmentHistories->isEmpty())
                    <p class="text-muted">No employment history available.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Employer Name</th>
                                    <th>Employer Contact</th>
                                    <th>Position Held</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employee->employmentHistories as $history)
                                    <tr>
                                        <td>{{ $history->employer_name }}</td>
                                        <td>{{ $history->employer_contact }}</td>
                                        <td>{{ $history->position_held }}</td>
                                        <td>{{ $history->duration }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Educational Background Card -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i> Educational Background</h5>
            </div>
            <div class="card-body">
                @if ($employee->educationalBackgrounds->isEmpty())
                    <p class="text-muted">No educational background available.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>School Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Qualification</th>
                                    <th>Certificate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employee->educationalBackgrounds as $education)
                                    <tr>
                                        <td>{{ $education->school_name }}</td>
                                        <td>{{ $education->start_date }}</td>
                                        <td>{{ $education->end_date }}</td>
                                        <td>{{ $education->qualification }}</td>
                                        <td>
                                            @if ($education->certificate_path)
                                                <a href="{{ asset('storage/' . $education->certificate_path) }}"
                                                    target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-download me-1"></i> Download
                                                </a>
                                            @else
                                                <span class="text-muted">No certificate</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ route('staff.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Staff List
            </a>
            <a href="{{ route('staff.edit', $employee->id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Edit Staff
            </a>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .card { border: none; border-radius: 8px; }
        .card-header { border-radius: 8px 8px 0 0; }
        .table th, .table td { vertical-align: middle; }
        .badge { font-size: 0.9rem; }
        h5 { font-weight: 500; }
        p { margin-bottom: 0.5rem; }
    </style>
@endsection