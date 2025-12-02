@extends('layouts.master')

@section('title', 'Finalize Check-in')

@section('page-content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="card border-0 shadow-lg rounded-3">
                    <div class="card-header border-0 rounded-top-3 py-3" 
                         style="background: linear-gradient(135deg, #C8A165 0%, #b08c54 100%);">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 me-3">
                                <i class="fas fa-check-double fa-lg text-gold"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 text-white fw-bold">Finalize Check-in for {{ $registration->full_name }}</h4>
                                <p class="mb-0 text-white opacity-75 small">Complete booking details and assign rooms</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('frontdesk.registrations.finalize', $registration) }}" method="POST" 
                              novalidate id="finalize-form">
                            @csrf
                            
                            {{-- Error Notification --}}
                            @if ($errors->any())
                                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 border-start border-3 border-danger rounded-2 mb-4" role="alert">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <strong class="fw-bold">There were errors with your submission:</strong>
                                    </div>
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- SECTION 1: Guest Submitted Data (Read-only) --}}
                            <div class="card border rounded-3 mb-5">
                                <div class="card-header bg-light border-0 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-white p-2 me-3">
                                            <i class="fas fa-user-check text-gold"></i>
                                        </div>
                                        <h5 class="mb-0 text-dark fw-bold">Guest Submitted Information (Review)</h5>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small mb-1">Lead Guest</label>
                                                    <p class="fw-bold text-dark mb-0">
                                                        {{ $registration->full_name }}
                                                        @if ($registration->title)
                                                            <span class="text-muted">({{ $registration->title }})</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small mb-1">Contact Information</label>
                                                    <p class="fw-bold text-dark mb-0">
                                                        <i class="fas fa-phone text-muted me-1"></i> {{ $registration->contact_number }}
                                                    </p>
                                                    <p class="fw-bold text-dark mb-0">
                                                        <i class="fas fa-envelope text-muted me-1"></i> {{ $registration->email }}
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small mb-1">Gender</label>
                                                    <p class="fw-bold text-dark mb-0">{{ strtoupper($registration->gender) }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small mb-1">Stay Duration</label>
                                                    <p class="fw-bold text-dark mb-0">
                                                        {{ $registration->check_in->format('M d, Y') }} to {{ $registration->check_out->format('M d, Y') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 text-center">
                                            <label class="form-label text-muted small mb-2">Guest Signature</label>
                                            @if ($registration->guest_signature)
                                                <img src="{{ Storage::url($registration->guest_signature) }}"
                                                     alt="Guest Signature" 
                                                     class="img-fluid border rounded bg-white p-2"
                                                     style="max-height: 100px;">
                                            @else
                                                <div class="border rounded p-4 bg-light">
                                                    <i class="fas fa-signature fa-2x text-muted mb-2"></i>
                                                    <p class="text-danger small mb-0">No signature provided</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 2: Front Desk Finalization --}}
                            <div class="mb-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="rounded-circle bg-light p-2 me-3">
                                        <i class="fas fa-edit text-gold"></i>
                                    </div>
                                    <h5 class="mb-0 text-dark fw-bold">Front Desk Booking Details (Finalize)</h5>
                                </div>

                                {{-- Group Lead's Details --}}
                                <div class="card border rounded-3 mb-4">
                                    <div class="card-header bg-light border-0 py-3">
                                        <h6 class="mb-0 text-dark fw-bold">
                                            <i class="fas fa-user-tie text-gold me-2"></i>
                                            Group Lead's Booking
                                        </h6>
                                    </div>
                                    
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="room_allocation" class="form-label fw-semibold text-dark">
                                                    Room Allocation <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-door-closed text-muted"></i>
                                                    </span>
                                                    <input type="text" class="form-control @error('room_allocation') is-invalid @enderror"
                                                           name="room_allocation" value="{{ old('room_allocation') }}"
                                                           placeholder="e.g., Room 101">
                                                    @error('room_allocation')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="room_rate" class="form-label fw-semibold text-dark">
                                                    Room Rate (per night) <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-money-bill-wave text-muted"></i>
                                                    </span>
                                                    <input type="text" class="form-control @error('room_rate') is-invalid @enderror"
                                                           name="room_rate" id="room_rate" value="{{ old('room_rate') }}"
                                                           placeholder="e.g. 50,000">
                                                    @error('room_rate')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="bed_breakfast"
                                                           value="1" id="bed_breakfast" @checked(old('bed_breakfast'))>
                                                    <label class="form-check-label fw-semibold text-dark" for="bed_breakfast">
                                                        <i class="fas fa-coffee text-gold me-1"></i>
                                                        Include Bed & Breakfast
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Group Members Table --}}
                                @if ($registration->is_group_lead && $groupMembers->count() > 0)
                                    <div class="card border rounded-3 mb-4">
                                        <div class="card-header bg-light border-0 py-3">
                                            <h6 class="mb-0 text-dark fw-bold">
                                                <i class="fas fa-users text-gold me-2"></i>
                                                Group Member Bookings ({{ $groupMembers->count() }} members)
                                            </h6>
                                        </div>
                                        
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th class="border-0 py-3 ps-4">Member Name</th>
                                                            <th class="border-0 py-3">Room Allocation*</th>
                                                            <th class="border-0 py-3">Room Rate*</th>
                                                            <th class="border-0 py-3 text-center">B&B</th>
                                                            <th class="border-0 py-3 pe-4">Status*</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($groupMembers as $member)
                                                            <tr>
                                                                <td class="ps-4">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="rounded-circle bg-light p-1 me-2">
                                                                            <i class="fas fa-user fa-sm text-gold"></i>
                                                                        </div>
                                                                        <span class="fw-semibold text-dark">{{ $member->full_name }}</span>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <input type="text"
                                                                           class="form-control @error('group_members.' . $member->id . '.room_allocation') is-invalid @enderror"
                                                                           name="group_members[{{ $member->id }}][room_allocation]"
                                                                           placeholder="Room No."
                                                                           value="{{ old('group_members.' . $member->id . '.room_allocation') }}">
                                                                    @error('group_members.' . $member->id . '.room_allocation')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <div class="input-group">
                                                                        <span class="input-group-text bg-light border-end-0">₦</span>
                                                                        <input type="number" step="0.01"
                                                                               class="form-control @error('group_members.' . $member->id . '.room_rate') is-invalid @enderror"
                                                                               name="group_members[{{ $member->id }}][room_rate]"
                                                                               placeholder="Rate"
                                                                               value="{{ old('group_members.' . $member->id . '.room_rate') }}">
                                                                        @error('group_members.' . $member->id . '.room_rate')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </td>
                                                                <td class="text-center">
                                                                    <div class="form-check d-inline-block">
                                                                        <input class="form-check-input" type="checkbox"
                                                                               name="group_members[{{ $member->id }}][bed_breakfast]"
                                                                               value="1" @checked(old('group_members.' . $member->id . '.bed_breakfast'))>
                                                                    </div>
                                                                </td>
                                                                <td class="pe-4">
                                                                    <select class="form-select @error('group_members.' . $member->id . '.status') is-invalid @enderror"
                                                                            name="group_members[{{ $member->id }}][status]">
                                                                        <option value="checked_in" @selected(old('group_members.' . $member->id . '.status', 'checked_in') == 'checked_in')>
                                                                            Check-in
                                                                        </option>
                                                                        <option value="no_show" @selected(old('group_members.' . $member->id . '.status') == 'no_show')>
                                                                            No-Show
                                                                        </option>
                                                                    </select>
                                                                    @error('group_members.' . $member->id . '.status')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Overall Booking Details --}}
                                <div class="card border rounded-3">
                                    <div class="card-header bg-light border-0 py-3">
                                        <h6 class="mb-0 text-dark fw-bold">
                                            <i class="fas fa-cog text-gold me-2"></i>
                                            Overall Booking Details
                                        </h6>
                                    </div>
                                    
                                    <div class="card-body">
                                        <div class="row g-3">
                                            @if ($registration->is_group_lead)
                                                <div class="col-md-6">
                                                    <label for="billing_type" class="form-label fw-semibold text-dark">
                                                        Billing Method <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="billing_type"
                                                            class="form-select @error('billing_type') is-invalid @enderror">
                                                        <option value="consolidate" @selected(old('billing_type', 'consolidate') == 'consolidate')>
                                                            Consolidate on Group Lead
                                                        </option>
                                                        <option value="individual" @selected(old('billing_type') == 'individual')>
                                                            Individual Billing (Each Pays Own)
                                                        </option>
                                                    </select>
                                                    @error('billing_type')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            @endif
                                            
                                            <div class="col-md-6">
                                                <label for="guest_type_id" class="form-label fw-semibold text-dark">
                                                    Guest Type <span class="text-danger">*</span>
                                                </label>
                                                <select name="guest_type_id"
                                                        class="form-select @error('guest_type_id') is-invalid @enderror">
                                                    @foreach ($guestTypes as $type)
                                                        <option value="{{ $type->id }}" @selected(old('guest_type_id') == $type->id)>
                                                            {{ $type->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('guest_type_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="booking_source_id" class="form-label fw-semibold text-dark">
                                                    Booking Source <span class="text-danger">*</span>
                                                </label>
                                                <select name="booking_source_id"
                                                        class="form-select @error('booking_source_id') is-invalid @enderror">
                                                    @foreach ($bookingSources as $source)
                                                        <option value="{{ $source->id }}" @selected(old('booking_source_id') == $source->id)>
                                                            {{ $source->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('booking_source_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="payment_method" class="form-label fw-semibold text-dark">
                                                    Payment Method <span class="text-danger">*</span>
                                                </label>
                                                <select name="payment_method"
                                                        class="form-select @error('payment_method') is-invalid @enderror">
                                                    <option value="pos" @selected(old('payment_method') == 'pos')>POS</option>
                                                    <option value="cash" @selected(old('payment_method') == 'cash')>Cash</option>
                                                    <option value="transfer" @selected(old('payment_method') == 'transfer')>Transfer</option>
                                                    <option value="credit_balance" @selected(old('payment_method') == 'credit_balance')>Credit Balance</option>
                                                    <option value="credit" @selected(old('payment_method') == 'credit')>Credit from other branches</option>
                                                </select>
                                                @error('payment_method')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Submit Button --}}
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-4 border-top">
                                <a href="{{ route('frontdesk.registrations.index') }}" class="btn btn-outline-dark me-2 px-4">
                                    <i class="fas fa-times me-2"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-gold px-4">
                                    @if ($registration->is_group_lead)
                                        <i class="fas fa-users me-2"></i> Complete Group Check-in
                                    @else
                                        <i class="fas fa-check-double me-2"></i> Complete Check-in
                                    @endif
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script for Comma Formatting --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roomRateInput = document.getElementById('room_rate');
            const form = document.getElementById('finalize-form');

            if (roomRateInput) {
                // 1. Helper function to format number (10000 -> 10,000)
                function formatNumber(val) {
                    // Remove existing commas to get clean number
                    let cleanVal = val.replace(/,/g, '');
                    if (!cleanVal || isNaN(cleanVal)) return val;
                    // Add commas back
                    return Number(cleanVal).toLocaleString();
                }

                // 2. Format on Input (While typing)
                roomRateInput.addEventListener('input', function(e) {
                    // Get current value without commas
                    let rawValue = e.target.value.replace(/,/g, '');

                    // Only format if it's a valid number
                    if (!isNaN(rawValue) && rawValue !== '') {
                        e.target.value = Number(rawValue).toLocaleString();
                    }
                });

                // 3. Strip Commas on Submit (Crucial for Database)
                if (form) {
                    form.addEventListener('submit', function() {
                        roomRateInput.value = roomRateInput.value.replace(/,/g, '');
                    });
                }
            }
        });
    </script>
@endsection