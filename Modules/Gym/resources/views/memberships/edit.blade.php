@extends('layouts.master')

@section('page-content')
<div class="container-fluid my-4">
    <div class="form-container">
        <div class="form-header">
            <h1><i class="fas fa-dumbbell me-2"></i>Edit Gym Membership #{{ $membership->id }}</h1>
            <p>FITNESSZONE BY BRICKSPOINT</p>
        </div>
        <a href="{{ route('gym.index') }}" class="btn btn-danger mt-2 mb-3"><i class="fas fa-arrow-circle-left"></i> Cancel</a>

        <div class="form-body">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" id="membershipForm" action="{{ route('gym.memberships.update', $membership->id) }}">
                @csrf
                @method('PUT')

                <!-- Package Selection -->
                <div class="mb-4">
                    <h3 class="section-title">Membership Package</h3>
                    <div class="toggle-container">
                        <div class="toggle-option {{ old('package_type', $membership->package_type) === 'individual' ? 'active' : '' }}" data-value="individual" id="individualOption">
                            <i class="fas fa-user"></i>
                            <h5>Individual</h5>
                            <p>Perfect for solo training</p>
                        </div>
                        <div class="toggle-option {{ old('package_type', $membership->package_type) === 'couple' ? 'active' : '' }}" data-value="couple" id="coupleOption">
                            <i class="fas fa-users"></i>
                            <h5>Couple</h5>
                            <p>Great for partners</p>
                        </div>
                    </div>
                    <input type="hidden" name="package_type" id="packageType" value="{{ old('package_type', $membership->package_type) }}">
                    @error('package_type')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Membership Details -->
                <div class="form-card">
                    <h3 class="section-title">Membership Details</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Subscription Plan</label>
                            <select class="form-select" name="subscription_plan" required>
                                <option value="monthly" {{ old('subscription_plan', $membership->subscription_plan) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('subscription_plan', $membership->subscription_plan) === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="6months" {{ old('subscription_plan', $membership->subscription_plan) === '6months' ? 'selected' : '' }}>6 Months</option>
                                <option value="yearly" {{ old('subscription_plan', $membership->subscription_plan) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                            @error('subscription_plan')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Start Date</label>
                            <input type="date" class="form-control" name="start_date" value="{{ old('start_date', $membership->start_date->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Personal Trainer</label>
                            <div>
                                <input class="form-check-input" type="radio" id="personalTrainerYes" name="personal_trainer" value="yes" {{ old('personal_trainer', $membership->personal_trainer) === 'yes' ? 'checked' : '' }} required>
                                <label for="personalTrainerYes">Yes</label>
                                <input class="form-check-input" type="radio" id="personalTrainerNo" name="personal_trainer" value="no" {{ old('personal_trainer', $membership->personal_trainer) === 'no' ? 'checked' : '' }} required>
                                <label for="personalTrainerNo">No</label>
                            </div>
                            @error('personal_trainer')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3" id="sessionsSection" style="display: {{ old('personal_trainer', $membership->personal_trainer) === 'yes' ? 'block' : 'none' }};">
                            <label class="form-label">Sessions (required if personal trainer is selected)</label>
                            <div>
                                <input class="form-check-input" type="radio" id="session1" name="sessions" value="1" {{ old('sessions', $membership->sessions) == 1 ? 'checked' : '' }}>
                                <label for="session1">1 Session</label>
                                <input class="form-check-input" type="radio" id="session6" name="sessions" value="6" {{ old('sessions', $membership->sessions) == 6 ? 'checked' : '' }}>
                                <label for="session6">6 Sessions</label>
                                <input class="form-check-input" type="radio" id="session12" name="sessions" value="12" {{ old('sessions', $membership->sessions) == 12 ? 'checked' : '' }}>
                                <label for="session12">12 Sessions</label>
                                <input class="form-check-input" type="radio" id="session20" name="sessions" value="20" {{ old('sessions', $membership->sessions) == 20 ? 'checked' : '' }}>
                                <label for="session20">20 Sessions</label>
                            </div>
                            @error('sessions')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- <!-- Payment Information -->
                <div class="form-card">
                    <h3 class="section-title">Payment Information</h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Payment Amount (₦)</label>
                            <div class="input-group">
                                <span class="input-group-text">₦</span>
                                <input type="number" class="form-control" name="payment_amount" step="0.01" min="0" value="{{ old('payment_amount', $membership->payment_amount) }}" required placeholder="0.00">
                            </div>
                            @error('payment_amount')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Payment Status</label>
                            <select class="form-select" name="payment_status" required>
                                <option value="paid" {{ old('payment_status', $membership->payment_status) === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="partial" {{ old('payment_status', $membership->payment_status) === 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="pending" {{ old('payment_status', $membership->payment_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="overdue" {{ old('payment_status', $membership->payment_status) === 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                            @error('payment_status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Payment Mode</label>
                            <select class="form-select" name="payment_mode" required>
                                <option value="cash" {{ old('payment_mode', $membership->payment_mode) === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank_transfer" {{ old('payment_mode', $membership->payment_mode) === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="pos" {{ old('payment_mode', $membership->payment_mode) === 'pos' ? 'selected' : '' }}>POS</option>
                                <option value="crypto" {{ old('payment_mode', $membership->payment_mode) === 'crypto' ? 'selected' : '' }} disabled title="Coming Soon">Crypto</option>
                            </select>
                            @error('payment_mode')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div> --}}

                <!-- Member 1 Information -->
                @php $member1 = $membership->members->first() @endphp
                <div class="member-card">
                    <div class="member-header">
                        <div class="member-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4>Primary Member Information</h4>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Full Name</label>
                            <input type="text" class="form-control" name="full_name_1" value="{{ old('full_name_1', $member1->full_name) }}" required>
                            @error('full_name_1')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth_1" value="{{ old('date_of_birth_1', $member1->date_of_birth->format('Y-m-d')) }}" required>
                            @error('date_of_birth_1')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Gender</label>
                            <select class="form-select" name="gender_1" required>
                                <option value="male" {{ old('gender_1', $member1->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender_1', $member1->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender_1', $member1->gender) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender_1')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Phone Number</label>
                            <input type="tel" class="form-control" name="phone_number_1" value="{{ old('phone_number_1', $member1->phone_number) }}" required placeholder="e.g., 08012345678">
                            @error('phone_number_1')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email_address_1" value="{{ old('email_address_1', $member1->email_address) }}" placeholder="name@example.com">
                            @error('email_address_1')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Home Address</label>
                        <textarea class="form-control" name="home_address_1" rows="2" required>{{ old('home_address_1', $member1->home_address) }}</textarea>
                        @error('home_address_1')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <h5 class="mt-4 mb-3" style="color: var(--secondary);">Emergency Contact</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Full Name</label>
                            <input type="text" class="form-control" name="emergency_contact_name_1" value="{{ old('emergency_contact_name_1', $member1->emergency_contact_name) }}" required>
                            @error('emergency_contact_name_1')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Relationship</label>
                            <input type="text" class="form-control" name="emergency_contact_relationship_1" value="{{ old('emergency_contact_relationship_1', $member1->emergency_contact_relationship) }}" required placeholder="e.g., Spouse">
                            @error('emergency_contact_relationship_1')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Phone Number</label>
                            <input type="tel" class="form-control" name="emergency_contact_number_1" value="{{ old('emergency_contact_number_1', $member1->emergency_contact_number) }}" required placeholder="e.g., 08012345678">
                            @error('emergency_contact_number_1')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Do you have any medical conditions or injuries?</label>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="medical_conditions_yes_1" name="has_medical_conditions_1" value="yes" {{ old('has_medical_conditions_1', $member1->medical_conditions ? 'yes' : 'no') === 'yes' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="medical_conditions_yes_1">Yes</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="medical_conditions_no_1" name="has_medical_conditions_1" value="no" {{ old('has_medical_conditions_1', $member1->medical_conditions ? 'yes' : 'no') === 'no' ? 'checked' : '' }}>
                            <label class="form-check-label" for="medical_conditions_no_1">No</label>
                        </div>
                        <div id="medical_conditions_details_1" class="mt-2" style="display: {{ old('has_medical_conditions_1', $member1->medical_conditions ? 'yes' : 'no') === 'yes' ? 'block' : 'none' }};">
                            <label for="medical_conditions_1" class="form-label">If yes, please specify</label>
                            <textarea class="form-control" id="medical_conditions_1" name="medical_conditions_1" rows="3">{{ old('medical_conditions_1', $member1->medical_conditions) }}</textarea>
                            @error('medical_conditions_1')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Fitness Goals</label>
                        @php
                            $fitnessGoals1 = old('fitness_goals_1', json_decode($member1->fitness_goals ?? '[]', true));
                            $otherGoal1 = !empty($fitnessGoals1) && !in_array('Weight Loss', $fitnessGoals1) && !in_array('Muscle Gain', $fitnessGoals1) && !in_array('General Fitness', $fitnessGoals1) ? $fitnessGoals1[0] : '';
                            $hasOther1 = !empty($otherGoal1);
                        @endphp
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="fitness_goals_weight_loss_1" name="fitness_goals_1[]" value="Weight Loss" {{ in_array('Weight Loss', $fitnessGoals1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="fitness_goals_weight_loss_1">Weight Loss</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="fitness_goals_muscle_gain_1" name="fitness_goals_1[]" value="Muscle Gain" {{ in_array('Muscle Gain', $fitnessGoals1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="fitness_goals_muscle_gain_1">Muscle Gain</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="fitness_goals_general_fitness_1" name="fitness_goals_1[]" value="General Fitness" {{ in_array('General Fitness', $fitnessGoals1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="fitness_goals_general_fitness_1">General Fitness</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="fitness_goals_other_1" name="fitness_goals_1[]" value="Other" {{ $hasOther1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="fitness_goals_other_1">Other</label>
                        </div>
                        <div id="fitness_goals_other_details_1" class="mt-2" style="display: {{ $hasOther1 ? 'block' : 'none' }};">
                            <label for="fitness_goals_other_1" class="form-label">If Other, please specify</label>
                            <input type="text" class="form-control" id="fitness_goals_other_1" name="fitness_goals_other_1" value="{{ old('fitness_goals_other_1', $otherGoal1) }}">
                            @error('fitness_goals_other_1')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        @error('fitness_goals_1')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Member 2 Information (Conditional) -->
                @php $member2 = $membership->members->skip(1)->first() @endphp
                <div class="member-card {{ old('package_type', $membership->package_type) === 'individual' ? 'hidden' : '' }}" id="member2">
                    <div class="member-header">
                        <div class="member-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4>Secondary Member Information</h4>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name_2" value="{{ old('full_name_2', $member2 ? $member2->full_name : '') }}">
                            @error('full_name_2')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth_2" value="{{ old('date_of_birth_2', $member2 ? $member2->date_of_birth->format('Y-m-d') : '') }}">
                            @error('date_of_birth_2')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender_2">
                                <option value="male" {{ old('gender_2', $member2 ? $member2->gender : '') === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender_2', $member2 ? $member2->gender : '') === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender_2', $member2 ? $member2->gender : '') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender_2')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone_number_2" value="{{ old('phone_number_2', $member2 ? $member2->phone_number : '') }}" placeholder="e.g., 08012345678">
                            @error('phone_number_2')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email_address_2" value="{{ old('email_address_2', $member2 ? $member2->email_address : '') }}" placeholder="name@example.com">
                            @error('email_address_2')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Home Address</label>
                        <textarea class="form-control" name="home_address_2" rows="2">{{ old('home_address_2', $member2 ? $member2->home_address : '') }}</textarea>
                        @error('home_address_2')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <h5 class="mt-4 mb-3" style="color: var(--secondary);">Emergency Contact</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="emergency_contact_name_2" value="{{ old('emergency_contact_name_2', $member2 ? $member2->emergency_contact_name : '') }}">
                            @error('emergency_contact_name_2')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Relationship</label>
                            <input type="text" class="form-control" name="emergency_contact_relationship_2" value="{{ old('emergency_contact_relationship_2', $member2 ? $member2->emergency_contact_relationship : '') }}" placeholder="e.g., Spouse">
                            @error('emergency_contact_relationship_2')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="emergency_contact_number_2" value="{{ old('emergency_contact_number_2', $member2 ? $member2->emergency_contact_number : '') }}" placeholder="e.g., 08012345678">
                            @error('emergency_contact_number_2')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Do you have any medical conditions or injuries?</label>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="medical_conditions_yes_2" name="has_medical_conditions_2" value="yes" {{ old('has_medical_conditions_2', $member2 && $member2->medical_conditions ? 'yes' : 'no') === 'yes' ? 'checked' : '' }}>
                            <label class="form-check-label" for="medical_conditions_yes_2">Yes</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="medical_conditions_no_2" name="has_medical_conditions_2" value="no" {{ old('has_medical_conditions_2', $member2 && $member2->medical_conditions ? 'yes' : 'no') === 'no' ? 'checked' : '' }}>
                            <label class="form-check-label" for="medical_conditions_no_2">No</label>
                        </div>
                        <div id="medical_conditions_details_2" class="mt-2" style="display: {{ old('has_medical_conditions_2', $member2 && $member2->medical_conditions ? 'yes' : 'no') === 'yes' ? 'block' : 'none' }};">
                            <label for="medical_conditions_2" class="form-label">If yes, please specify</label>
                            <textarea class="form-control" id="medical_conditions_2" name="medical_conditions_2" rows="3">{{ old('medical_conditions_2', $member2 ? $member2->medical_conditions : '') }}</textarea>
                            @error('medical_conditions_2')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fitness Goals</label>
                        @php
                            $fitnessGoals2 = old('fitness_goals_2', $member2 ? json_decode($member2->fitness_goals ?? '[]', true) : []);
                            $otherGoal2 = !empty($fitnessGoals2) && !in_array('Weight Loss', $fitnessGoals2) && !in_array('Muscle Gain', $fitnessGoals2) && !in_array('General Fitness', $fitnessGoals2) ? $fitnessGoals2[0] : '';
                            $hasOther2 = !empty($otherGoal2);
                        @endphp
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="fitness_goals_weight_loss_2" name="fitness_goals_2[]" value="Weight Loss" {{ in_array('Weight Loss', $fitnessGoals2) ? 'checked' : '' }}>
                            <label class="form-check-label" for="fitness_goals_weight_loss_2">Weight Loss</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="fitness_goals_muscle_gain_2" name="fitness_goals_2[]" value="Muscle Gain" {{ in_array('Muscle Gain', $fitnessGoals2) ? 'checked' : '' }}>
                            <label class="form-check-label" for="fitness_goals_muscle_gain_2">Muscle Gain</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="fitness_goals_general_fitness_2" name="fitness_goals_2[]" value="General Fitness" {{ in_array('General Fitness', $fitnessGoals2) ? 'checked' : '' }}>
                            <label class="form-check-label" for="fitness_goals_general_fitness_2">General Fitness</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="fitness_goals_other_2" name="fitness_goals_2[]" value="Other" {{ $hasOther2 ? 'checked' : '' }}>
                            <label class="form-check-label" for="fitness_goals_other_2">Other</label>
                        </div>
                        <div id="fitness_goals_other_details_2" class="mt-2" style="display: {{ $hasOther2 ? 'block' : 'none' }};">
                            <label for="fitness_goals_other_2" class="form-label">If Other, please specify</label>
                            <input type="text" class="form-control" id="fitness_goals_other_2" name="fitness_goals_other_2" value="{{ old('fitness_goals_other_2', $otherGoal2) }}">
                            @error('fitness_goals_other_2')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        @error('fitness_goals_2')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="terms-container">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="termsAgreed" name="terms_agreed" required {{ old('terms_agreed', $membership->terms_agreed) ? 'checked' : '' }}>
                        <label class="form-check-label" for="termsAgreed">
                            I agree to the <a href="#" class="text-decoration-none">terms and conditions</a> and 
                            <a href="#" class="text-decoration-none">privacy policy</a>
                        </label>
                        @error('terms_agreed')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-check-circle me-2"></i>Update Membership
                    </button>
                </div>
            </form>

            <div class="form-footer">
                <p>Have questions? Contact IT</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Package type toggle
    const individualOption = document.getElementById('individualOption');
    const coupleOption = document.getElementById('coupleOption');
    const packageType = document.getElementById('packageType');
    const member2Section = document.getElementById('member2');

    individualOption.addEventListener('click', () => {
        individualOption.classList.add('active');
        coupleOption.classList.remove('active');
        packageType.value = 'individual';
        member2Section.classList.add('hidden');
    });

    coupleOption.addEventListener('click', () => {
        coupleOption.classList.add('active');
        individualOption.classList.remove('active');
        packageType.value = 'couple';
        member2Section.classList.remove('hidden');
    });

    // Personal Trainer and Sessions toggle
    const personalTrainerYes = document.getElementById('personalTrainerYes');
    const personalTrainerNo = document.getElementById('personalTrainerNo');
    const sessionsSection = document.getElementById('sessionsSection');

    if (personalTrainerYes && personalTrainerNo && sessionsSection) {
        personalTrainerYes.addEventListener('change', () => {
            sessionsSection.style.display = personalTrainerYes.checked ? 'block' : 'none';
        });
        personalTrainerNo.addEventListener('change', () => {
            sessionsSection.style.display = personalTrainerNo.checked ? 'none' : 'block';
        });
    }

    // Medical Conditions toggle for Member 1
    const medicalConditionsYes1 = document.getElementById('medical_conditions_yes_1');
    const medicalConditionsNo1 = document.getElementById('medical_conditions_no_1');
    const medicalConditionsDetails1 = document.getElementById('medical_conditions_details_1');

    if (medicalConditionsYes1 && medicalConditionsNo1 && medicalConditionsDetails1) {
        medicalConditionsYes1.addEventListener('change', () => {
            medicalConditionsDetails1.style.display = medicalConditionsYes1.checked ? 'block' : 'none';
        });
        medicalConditionsNo1.addEventListener('change', () => {
            medicalConditionsDetails1.style.display = medicalConditionsNo1.checked ? 'none' : 'block';
        });
    }

    // Medical Conditions toggle for Member 2
    const medicalConditionsYes2 = document.getElementById('medical_conditions_yes_2');
    const medicalConditionsNo2 = document.getElementById('medical_conditions_no_2');
    const medicalConditionsDetails2 = document.getElementById('medical_conditions_details_2');

    if (medicalConditionsYes2 && medicalConditionsNo2 && medicalConditionsDetails2) {
        medicalConditionsYes2.addEventListener('change', () => {
            medicalConditionsDetails2.style.display = medicalConditionsYes2.checked ? 'block' : 'none';
        });
        medicalConditionsNo2.addEventListener('change', () => {
            medicalConditionsDetails2.style.display = medicalConditionsNo2.checked ? 'none' : 'block';
        });
    }

    // Fitness Goals "Other" toggle for Member 1
    const fitnessGoalsOther1 = document.getElementById('fitness_goals_other_1');
    const fitnessGoalsOtherDetails1 = document.getElementById('fitness_goals_other_details_1');

    if (fitnessGoalsOther1 && fitnessGoalsOtherDetails1) {
        fitnessGoalsOther1.addEventListener('change', () => {
            fitnessGoalsOtherDetails1.style.display = fitnessGoalsOther1.checked ? 'block' : 'none';
        });
    }

    // Fitness Goals "Other" toggle for Member 2
    const fitnessGoalsOther2 = document.getElementById('fitness_goals_other_2');
    const fitnessGoalsOtherDetails2 = document.getElementById('fitness_goals_other_details_2');

    if (fitnessGoalsOther2 && fitnessGoalsOtherDetails2) {
        fitnessGoalsOther2.addEventListener('change', () => {
            fitnessGoalsOtherDetails2.style.display = fitnessGoalsOther2.checked ? 'block' : 'none';
        });
    }
</script>
@endsection
@section('styles')
@include('gym::partials.styles')
@endsection
@section('page-scripts')
@include('gym::partials.script')
@endsection