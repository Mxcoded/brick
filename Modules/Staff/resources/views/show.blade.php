@extends('staff::layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">Staff</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $employee->name }}</li>
@endsection

@section('page-content')
    <div class="container my-5">
        <h1 class="mb-4 fw-bold text-dark">Staff Details</h1>

        <!-- Main Employee Card -->
        <div class="card shadow-sm mb-5">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-semibold  text-white">{{ $employee->name }}</h5>
                <span class="badge bg-light text-dark px-2 py-1">{{ $employee->staff_code ?? 'N/A' }}</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-4 text-center">
                        @if ($employee->profile_image)
                            <img src="{{ asset('storage/' . $employee->profile_image) }}" 
                                 alt="{{ $employee->name }}'s Profile Image"
                                 class="img-fluid rounded-circle mb-3"
                                 style="width: 150px; height: 150px; object-fit: cover;">
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
                                <p><strong><i class="fas fa-map-marker-alt me-2"></i> Place of Birth:</strong> 
                                    {{ $employee->place_of_birth ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-flag me-2"></i> State of Origin:</strong> 
                                    {{ $employee->state_of_origin ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-city me-2"></i> LGA:</strong> 
                                    {{ $employee->lga ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-globe me-2"></i> Nationality:</strong> 
                                    {{ $employee->nationality ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-venus-mars me-2"></i> Gender:</strong> 
                                    {{ $employee->gender ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-birthday-cake me-2"></i> Date of Birth:</strong> 
                                    {{ $employee->date_of_birth ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-briefcase me-2"></i> Position:</strong> 
                                    {{ $employee->position ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-ring me-2"></i> Marital Status:</strong> 
                                    {{ $employee->marital_status ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-tint me-2"></i> Blood Group:</strong> 
                                    {{ $employee->blood_group ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-dna me-2"></i> Genotype:</strong> 
                                    {{ $employee->genotype ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-phone me-2"></i> Phone Number:</strong> 
                                    {{ $employee->phone_number ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-home me-2"></i> Residential Address:</strong> 
                                    {{ $employee->residential_address ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-id-card me-2"></i> NIN:</strong> 
                                    {{ $employee->nin ?? 'N/A' }}</p>
                                <p>
                                    <strong><i class="fas fa-bank me-2"></i> BVN:</strong>
                                    @if ($employee->bvn)
                                        <span id="bvn-display">
                                            {{ substr($employee->bvn, 0, 5) . str_repeat('•', strlen($employee->bvn) - 5) }}
                                        </span>
                                        <button class="btn btn-link p-0 ms-2 toggle-bvn" type="button" 
                                                data-bs-toggle="tooltip" title="Show Full BVN" aria-label="Show Full BVN">
                                            <i class="fas fa-eye" id="bvn-eye"></i>
                                        </button>
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h5 class="border-bottom pb-2 fw-semibold text-dark">
                        <i class="fas fa-clock me-2"></i> Employment Status
                    </h5>
                    <p>
                        @if ($employee->isActive())
                            <span class="badge bg-success me-2">Active</span> {{ $employee->start_date ?? 'N/A' }} - Present
                            <br><strong>Branch:</strong> {{ $employee->branch_name ?? 'N/A' }}
                        @else
                            <span class="badge bg-danger me-2">Inactive</span> 
                            {{ $employee->start_date ?? 'N/A' }} - {{ $employee->end_date ?? 'N/A' }}
                            <br><strong>Reason:</strong> {{ $employee->leaving_reason ?? 'N/A' }}
                            <br><strong>Note:</strong> {{ $employee->note_for_leaving ?? 'N/A' }}
                            <br><strong>Branch:</strong> {{ $employee->branch_name ?? 'N/A' }}
                            @if ($employee->resignation_letter)
                                <br><a href="{{ Storage::url($employee->resignation_letter) }}" target="_blank"
                                    class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-file-pdf me-1"></i> View Resignation Letter
                                </a>
                            @endif
                        @endif
                    </p>
                </div>

                <div class="mt-4 row g-4">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 fw-semibold text-dark">
                            <i class="fas fa-user-friends me-2"></i> Next of Kin
                        </h5>
                        <p><strong>Name:</strong> {{ $employee->next_of_kin_name ?? 'N/A' }}</p>
                        <p><strong>Phone:</strong> {{ $employee->next_of_kin_phone ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 fw-semibold text-dark">
                            <i class="fas fa-ambulance me-2"></i> ICE Contact
                        </h5>
                        <p><strong>Name:</strong> {{ $employee->ice_contact_name ?? 'N/A' }}</p>
                        <p><strong>Phone:</strong> {{ $employee->ice_contact_phone ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="mt-4">
                    <h5 class="border-bottom pb-2 fw-semibold text-dark">
                        <i class="fas fa-file-alt me-2"></i> CV
                    </h5>
                    @if ($employee->cv_path)
                        <a href="{{ asset('storage/' . $employee->cv_path) }}" target="_blank"
                            class="btn btn-sm btn-primary">
                            <i class="fas fa-download me-1"></i> Download CV
                        </a>
                    @else
                        <p class="text-muted">No CV uploaded.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Employment History Card -->
        <div class="card shadow-sm mt-5">
            <div class="card-header bg-secondary text-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-history me-2"></i> Employment History</h5>
            </div>
            <div class="card-body p-4">
                @if ($employee->employmentHistories->isEmpty())
                    <p class="text-muted">No employment history available.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Employer Name</th>
                                    <th scope="col">Employer Contact</th>
                                    <th scope="col">Position Held</th>
                                    <th scope="col">Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employee->employmentHistories as $history)
                                    <tr>
                                        <td>{{ $history->employer_name ?? 'N/A' }}</td>
                                        <td>{{ $history->employer_contact ?? 'N/A' }}</td>
                                        <td>{{ $history->position_held ?? 'N/A' }}</td>
                                        <td>{{ $history->duration ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Educational Background Card -->
        <div class="card shadow-sm mt-5">
            <div class="card-header bg-secondary text-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-graduation-cap me-2"></i> Educational Background</h5>
            </div>
            <div class="card-body p-4">
                @if ($employee->educationalBackgrounds->isEmpty())
                    <p class="text-muted">No educational background available.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">School Name</th>
                                    <th scope="col">Start Date</th>
                                    <th scope="col">End Date</th>
                                    <th scope="col">Qualification</th>
                                    <th scope="col">Certificate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employee->educationalBackgrounds as $education)
                                    <tr>
                                        <td>{{ $education->school_name ?? 'N/A' }}</td>
                                        <td>{{ $education->start_date ?? 'N/A' }}</td>
                                        <td>{{ $education->end_date ?? 'N/A' }}</td>
                                        <td>{{ $education->qualification ?? 'N/A' }}</td>
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

        <div class="mt-5 d-flex justify-content-between flex-wrap gap-3">
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
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            border-radius: 8px 8px 0 0;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .badge {
            font-size: 0.9rem;
            padding: 0.4em 0.6em;
        }

        h5 {
            font-weight: 600;
            color: #343a40;
        }

        p {
            margin-bottom: 0.75rem;
        }

        .btn-link {
            color: #495057;
            text-decoration: none;
        }

        .btn-link:hover {
            color: #0d6efd;
        }

        @media (max-width: 767.98px) {
            .text-center img, .text-center div {
                margin: 0 auto;
            }
        }
    </style>
@endsection