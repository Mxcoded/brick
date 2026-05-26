@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('frontdesk.registrations.dashboard') }}">Front Desk</a></li>
    <li class="breadcrumb-item"><a href="{{ route('frontdesk.guests.index') }}">Guests</a></li>
    <li class="breadcrumb-item active">Edit Guest</li>
@endsection

@section('page-content')
<div class="container-fluid py-4">
    
    {{-- Flash Messages --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fas fa-user-edit me-2 text-primary"></i>Edit Guest
            </h1>
            <p class="text-muted mb-0">Update guest profile information</p>
        </div>
        <a href="{{ route('frontdesk.guests.show', $guest->id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Profile
        </a>
    </div>

    <form action="{{ route('frontdesk.guests.update', $guest->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        {{-- Personal Information --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="card-title mb-0"><i class="fas fa-user me-2"></i>Personal Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Title</label>
                        <select name="title" class="form-select">
                            <option value="">Select</option>
                            <option value="Mr." {{ old('title', $guest->title) == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                            <option value="Mrs." {{ old('title', $guest->title) == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                            <option value="Ms." {{ old('title', $guest->title) == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                            <option value="Dr." {{ old('title', $guest->title) == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                            <option value="Chief" {{ old('title', $guest->title) == 'Chief' ? 'selected' : '' }}>Chief</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" 
                            value="{{ old('full_name', $guest->full_name) }}" required>
                        @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Select</option>
                            <option value="Male" {{ old('gender', $guest->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', $guest->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender', $guest->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="birthday" class="form-control" value="{{ old('birthday', $guest->birthday?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nationality</label>
                        <input type="text" name="nationality" class="form-control" value="{{ old('nationality', $guest->nationality) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ID Type</label>
                        <select name="identification_type" class="form-select">
                            <option value="">Select</option>
                            <option value="NIN" {{ old('identification_type', $guest->identification_type) == 'NIN' ? 'selected' : '' }}>National ID (NIN)</option>
                            <option value="Passport" {{ old('identification_type', $guest->identification_type) == 'Passport' ? 'selected' : '' }}>Passport</option>
                            <option value="Driver's License" {{ old('identification_type', $guest->identification_type) == "Driver's License" ? 'selected' : '' }}>Driver's License</option>
                            <option value="Voter's Card" {{ old('identification_type', $guest->identification_type) == "Voter's Card" ? 'selected' : '' }}>Voter's Card</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ID Number</label>
                        <input type="text" name="identification_number" class="form-control" value="{{ old('identification_number', $guest->identification_number) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Information --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light py-3">
                <h5 class="card-title mb-0"><i class="fas fa-phone-alt me-2"></i>Contact Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="contact_number" class="form-control @error('contact_number') is-invalid @enderror" 
                            value="{{ old('contact_number', $guest->contact_number) }}" required>
                        @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                            value="{{ old('email', $guest->email) }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $guest->company_name) }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Home Address</label>
                        <input type="text" name="home_address" class="form-control" value="{{ old('home_address', $guest->home_address) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $guest->city) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state', $guest->state) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Zip Code</label>
                        <input type="text" name="zip_code" class="form-control" value="{{ old('zip_code', $guest->zip_code) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Emergency Contact --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light py-3">
                <h5 class="card-title mb-0"><i class="fas fa-first-aid me-2"></i>Emergency Contact</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Contact Name</label>
                        <input type="text" name="emergency_name" class="form-control" value="{{ old('emergency_name', $guest->emergency_name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Relationship</label>
                        <input type="text" name="emergency_relationship" class="form-control" value="{{ old('emergency_relationship', $guest->emergency_relationship) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="emergency_contact" class="form-control" value="{{ old('emergency_contact', $guest->emergency_contact) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('frontdesk.guests.show', $guest->id) }}" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-times me-2"></i>Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-2"></i>Update Guest
            </button>
        </div>
    </form>
</div>
@endsection
