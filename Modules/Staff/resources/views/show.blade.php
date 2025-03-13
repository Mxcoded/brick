@extends('staff::layouts.master')


@section('content')
<div class="container">
    <h1 class="mb-4">Employee Details</h1>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">{{ $employee->name }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Profile Image -->
                <div class="col-md-4 text-center">
                    @if($employee->profile_image)
                        <img src="{{ asset('storage/' . $employee->profile_image) }}" alt="Profile Image" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px;">
                    @else
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px;">
                            <span class="text-muted">No Image</span>
                        </div>
                    @endif
                </div>

                <!-- Employee Details -->
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Place of Birth:</strong> {{ $employee->place_of_birth }}</p>
                            <p><strong>State of Origin:</strong> {{ $employee->state_of_origin }}</p>
                            <p><strong>LGA:</strong> {{ $employee->lga }}</p>
                            <p><strong>Nationality:</strong> {{ $employee->nationality }}</p>
                            <p><strong>Gender:</strong> {{ $employee->gender }}</p>
                            <p><strong>Date of Birth:</strong> {{ $employee->date_of_birth }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Marital Status:</strong> {{ $employee->marital_status }}</p>
                            <p><strong>Blood Group:</strong> {{ $employee->blood_group }}</p>
                            <p><strong>Genotype:</strong> {{ $employee->genotype }}</p>
                            <p><strong>Phone Number:</strong> {{ $employee->phone_number }}</p>
                            <p><strong>Residential Address:</strong> {{ $employee->residential_address }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next of Kin and ICE Contact -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <h5>Next of Kin</h5>
                    <p><strong>Name:</strong> {{ $employee->next_of_kin_name }}</p>
                    <p><strong>Phone:</strong> {{ $employee->next_of_kin_phone }}</p>
                </div>
                <div class="col-md-6">
                    <h5>In Case of Emergency (ICE) Contact</h5>
                    <p><strong>Name:</strong> {{ $employee->ice_contact_name }}</p>
                    <p><strong>Phone:</strong> {{ $employee->ice_contact_phone }}</p>
                </div>
            </div>

            <!-- CV Section -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5>CV</h5>
                    @if($employee->cv_path)
                        <a href="{{ asset('storage/' . $employee->cv_path) }}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download CV
                        </a>
                    @else
                        <p class="text-muted">No CV uploaded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Employment History Section -->
    <div class="card mt-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="card-title mb-0">Employment History</h5>
        </div>
        <div class="card-body">
            @if($employee->employmentHistories->isEmpty())
                <p class="text-muted">No employment history available.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Employer Name</th>
                                <th>Employer Contact</th>
                                <th>Position Held</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employee->employmentHistories as $history)
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

    <!-- Educational Background Section -->
    <div class="card mt-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="card-title mb-0">Educational Background</h5>
        </div>
        <div class="card-body">
            @if($employee->educationalBackgrounds->isEmpty())
                <p class="text-muted">No educational background available.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>School Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Qualification</th>
                                <th>Certificate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employee->educationalBackgrounds as $education)
                                <tr>
                                    <td>{{ $education->school_name }}</td>
                                    <td>{{ $education->start_date }}</td>
                                    <td>{{ $education->end_date }}</td>
                                    <td>{{ $education->qualification }}</td>
                                    <td>
                                        @if($education->certificate_path)
                                            <a href="{{ asset('storage/' . $education->certificate_path) }}" target="_blank" class="btn btn-sm btn-primary">
                                                <i class="fas fa-download"></i> Download
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

    <!-- Back Button -->
    <div class="mt-4">
        <a href="{{ route('staff.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Staff List
        </a>
    </div>
</div>
@endsection