@extends('layouts.master')

@section('title', 'New Walk-in Registration')

@section('page-content')
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form action="{{ route('frontdesk.registrations.storeWalkin') }}" method="POST">
                    @csrf
                    <div class="card shadow-lg">
                        <div class="card-header bg-warning text-dark">
                            <h4 class="mb-0"><i class="fas fa-walking me-2"></i>Create New Walk-in Guest</h4>
                        </div>
                        <div class="card-body p-4">

                            @if ($errors->any())
                                <div class="alert alert-danger mb-4" role="alert">
                                    <strong>Please fix the errors:</strong>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <h6 class="mt-3">Guest Details</h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="full_name" class="form-label">Full Name*</label>
                                    <input type="text" class="form-control @error('full_name') is-invalid @enderror" name="full_name" value="{{ old('full_name') }}" required placeholder="Enter guest's full name">
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="contact_number" class="form-label">Contact Number*</label>
                                    <input type="tel" class="form-control @error('contact_number') is-invalid @enderror" name="contact_number" value="{{ old('contact_number') }}" required placeholder="+234...">
                                    @error('contact_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="optional@email.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <h6 class="mt-4">Stay Details</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="check_in" class="form-label">Check-in Date*</label>
                                    <input type="date" class="form-control @error('check_in') is-invalid @enderror" name="check_in" value="{{ old('check_in', now()->format('Y-m-d')) }}" required>
                                    @error('check_in')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="check_out" class="form-label">Check-out Date*</label>
                                    <input type="date" class="form-control @error('check_out') is-invalid @enderror" name="check_out" value="{{ old('check_out', now()->addDay()->format('Y-m-d')) }}" required>
                                    @error('check_out')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-1"></i> Submitting this form creates a "Draft". You will be redirected to finalize the room and rate immediately.
                            </div>

                            <hr class="my-4">
                            <button type="submit" class="btn btn-warning btn-lg w-100">
                                Create Draft & Finalize <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection