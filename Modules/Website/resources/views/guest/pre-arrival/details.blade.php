@extends('website::layouts.master')

@section('title', 'Pre-Arrival — Personal Details')

@section('content')
<div class="min-vh-100 py-5" style="background: linear-gradient(135deg, #f8f6f1 0%, #efece4 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                @include('website::guest.pre-arrival._steps', ['steps' => $steps, 'current' => 'details'])

                <div class="card border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 px-lg-5">
                        <h4 class="fw-bold mb-1">Personal Details</h4>
                        <p class="text-muted small mb-0">Confirm your details and add any special requests.</p>
                    </div>
                    <div class="card-body p-4 p-lg-5">
                        <form method="POST" action="{{ route('guest.pre-arrival.update-details', $registration) }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Title</label>
                                    <select name="title" class="form-select">
                                        <option value="">Select</option>
                                        <option value="Mr" @selected(old('title', $registration->guest->title) === 'Mr')>Mr</option>
                                        <option value="Mrs" @selected(old('title', $registration->guest->title) === 'Mrs')>Mrs</option>
                                        <option value="Ms" @selected(old('title', $registration->guest->title) === 'Ms')>Ms</option>
                                        <option value="Dr" @selected(old('title', $registration->guest->title) === 'Dr')>Dr</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control" required
                                           value="{{ old('full_name', $registration->guest->full_name) }}">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Nationality</label>
                                    <input type="text" name="nationality" class="form-control"
                                           value="{{ old('nationality', $registration->guest->nationality) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" required
                                           value="{{ old('email', $registration->guest->email ?? $registration->email) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_number" class="form-control" required
                                           value="{{ old('contact_number', $registration->guest->contact_number) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Occupation</label>
                                    <input type="text" name="occupation" class="form-control"
                                           value="{{ old('occupation', $registration->guest->occupation) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Company Name</label>
                                    <input type="text" name="company_name" class="form-control"
                                           value="{{ old('company_name', $registration->guest->company_name) }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Home Address</label>
                                    <textarea name="home_address" class="form-control" rows="2">{{ old('home_address', $registration->guest->home_address) }}</textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">City</label>
                                    <input type="text" name="city" class="form-control"
                                           value="{{ old('city', $registration->guest->city) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">State</label>
                                    <input type="text" name="state" class="form-control"
                                           value="{{ old('state', $registration->guest->state) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Number of Guests <span class="text-danger">*</span></label>
                                    <input type="number" name="no_of_guests" class="form-control" required min="1" max="20"
                                           value="{{ old('no_of_guests', $registration->no_of_guests ?? 1) }}">
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5 class="fw-bold mb-3">Emergency Contact</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Name</label>
                                    <input type="text" name="emergency_name" class="form-control"
                                           value="{{ old('emergency_name', $registration->guest->emergency_name) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Relationship</label>
                                    <input type="text" name="emergency_relationship" class="form-control"
                                           value="{{ old('emergency_relationship', $registration->guest->emergency_relationship) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <input type="text" name="emergency_contact" class="form-control"
                                           value="{{ old('emergency_contact', $registration->guest->emergency_contact) }}">
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5 class="fw-bold mb-3">Arrival & Requests</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Estimated Arrival Time</label>
                                    <input type="datetime-local" name="estimated_arrival_at" class="form-control"
                                           value="{{ old('estimated_arrival_at', $registration->estimated_arrival_at?->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="opt_in_marketing" class="form-check-input" id="optMarketing" value="1"
                                               @checked(old('opt_in_marketing', $registration->opt_in_marketing))>
                                        <label class="form-check-label" for="optMarketing">
                                            I'd like to receive offers and updates
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Special Requests</label>
                                    <textarea name="special_requests" class="form-control" rows="3"
                                              placeholder="e.g. Extra pillows, high floor, late check-in...">{{ old('special_requests', $registration->special_requests) }}</textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                <a href="{{ route('guest.pre-arrival') }}" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Back
                                </a>
                                <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                                    Continue <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
