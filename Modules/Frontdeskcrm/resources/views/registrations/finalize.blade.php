@extends('layouts.master')

@section('title', 'Finalize Check-in')

@section('page-content')
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <form action="{{ route('frontdesk.registrations.finalize', $registration) }}" method="POST" novalidate>
                    @csrf
                    <div class="card shadow-lg">
                        <div class="card-header bg-warning text-dark">
                            <h4><i class="fas fa-check-double me-2"></i>Finalize Check-in for {{ $registration->full_name }}
                            </h4>
                        </div>
                        <div class="card-body p-4">

                            {{-- ====================================================== --}}
                            {{-- NEW: CATCH-ALL ERROR NOTIFICATION --}}
                            {{-- ====================================================== --}}
                            @if ($errors->any())
                                <div class="alert alert-danger mb-4" role="alert">
                                    <strong>There were errors with your submission:</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- SECTION 1: GUEST SUBMITTED DATA (REVIEW) --}}
                            <fieldset class="mb-4">
                                <legend class="h5 border-bottom pb-2 mb-3">Guest Submitted Information (Review)</legend>
                                <div class="row">
                                    <div class="col-md-8">
                                        <p><strong>Lead Guest:</strong> {{ $registration->full_name }}
                                            ({{ $registration->title }})</p>
                                        <p><strong>Contact:</strong> {{ $registration->contact_number }} |
                                            {{ $registration->email }}</p>
                                            <p><strong>Gender:</strong> {{ strtoupper($registration->gender) }}</p>
                                        <p><strong>Stay:</strong> {{ $registration->check_in->format('M d, Y') }} to
                                            {{ $registration->check_out->format('M d, Y') }}</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <label>Guest Signature:</label>
                                        @if ($registration->guest_signature)
                                            <img src="{{ Storage::url($registration->guest_signature) }}"
                                                alt="Guest Signature" class="img-fluid border rounded bg-white"
                                                style="max-height: 100px;">
                                        @else
                                            <p class="text-danger">No signature provided.</p>
                                        @endif
                                    </div>
                                </div>
                            </fieldset>

                            {{-- SECTION 2: AGENT FINALIZATION FORM (EDITABLE) --}}
                            <fieldset>
                                <legend class="h5 border-bottom pb-2 mb-3">Front Desk Booking Details (Finalize)</legend>

                                {{-- Group Lead's Details --}}
                                <h6 class="mt-3">Group Lead's Booking</h6>
                                <div class="row bg-light p-3 rounded border">
                                    <div class="col-md-4 mb-3">
                                        <label for="room_allocation" class="form-label">Room Allocation*</label>
                                        {{-- REMOVED 'required' --}}
                                        <input type="text" class="form-control @error('room_allocation') is-invalid @enderror" name="room_allocation" value="{{ old('room_allocation') }}">
                                        {{-- ADDED ERROR HANDLING --}}
                                        @error('room_allocation')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="room_rate" class="form-label">Room Rate (per night)*</label>
                                        {{-- REMOVED 'required' --}}
                                        <input type="number" step="0.01" class="form-control @error('room_rate') is-invalid @enderror" name="room_rate" value="{{ old('room_rate') }}">
                                        {{-- ADDED ERROR HANDLING --}}
                                        @error('room_rate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3 d-flex align-items-end">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="bed_breakfast"
                                                value="1" id="bed_breakfast" @checked(old('bed_breakfast'))>
                                            <label class="form-check-label" for="bed_breakfast">Bed & Breakfast</label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Room allocation for group members with individual rates --}}
                                @if ($registration->is_group_lead && $groupMembers->count() > 0)
                                    <h6 class="mt-4">Group Member Bookings</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Member Name</th>
                                                    <th>Room Allocation*</th>
                                                    <th>Room Rate*</th>
                                                    <th>B&B</th>
                                                    <th>Status*</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($groupMembers as $member)
                                                    <tr>
                                                        <td>{{ $member->full_name }}</td>
                                                        <td>
                                                            {{-- Note the syntax for array error handling --}}
                                                            <input type="text" class="form-control @error('group_members.' . $member->id . '.room_allocation') is-invalid @enderror"
                                                                name="group_members[{{ $member->id }}][room_allocation]"
                                                                placeholder="Room No." value="{{ old('group_members.' . $member->id . '.room_allocation') }}">
                                                            @error('group_members.' . $member->id . '.room_allocation')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" class="form-control @error('group_members.' . $member->id . '.room_rate') is-invalid @enderror"
                                                                name="group_members[{{ $member->id }}][room_rate]"
                                                                placeholder="Rate" value="{{ old('group_members.' . $member->id . '.room_rate') }}">
                                                            @error('group_members.' . $member->id . '.room_rate')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="group_members[{{ $member->id }}][bed_breakfast]"
                                                                    value="1" @checked(old('group_members.' . $member->id . '.bed_breakfast'))>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <select class="form-select @error('group_members.' . $member->id . '.status') is-invalid @enderror"
                                                                name="group_members[{{ $member->id }}][status]">
                                                                {{-- Use old() to remember the selection --}}
                                                                <option value="checked_in" @selected(old('group_members.' . $member->id . '.status', 'checked_in') == 'checked_in')>Check-in</option>
                                                                <option value="no_show" @selected(old('group_members.' . $member->id . '.status') == 'no_show')>No-Show</option>
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
                                @endif

                                <h6 class="mt-4">Overall Booking Details</h6>
                                <div class="row">
                                    @if ($registration->is_group_lead)
                                        <div class="col-md-4 mb-3">
                                            <label for="billing_type" class="form-label">Billing Method*</label>
                                            <select name="billing_type" class="form-select @error('billing_type') is-invalid @enderror">
                                                <option value="consolidate" @selected(old('billing_type', 'consolidate') == 'consolidate')>Consolidate on Group Lead</option>
                                                <option value="individual" @selected(old('billing_type') == 'individual')>Individual Billing (Each Pays Own)</option>
                                            </select>
                                            @error('billing_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif
                                    <div class="col-md-4 mb-3">
                                        <label for="guest_type_id" class="form-label">Guest Type*</label>
                                        <select name="guest_type_id" class="form-select @error('guest_type_id') is-invalid @enderror">
                                            @foreach ($guestTypes as $type)
                                                <option value="{{ $type->id }}" @selected(old('guest_type_id') == $type->id)>{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('guest_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="booking_source_id" class="form-label">Booking Source*</label>
                                        <select name="booking_source_id" class="form-select @error('booking_source_id') is-invalid @enderror">
                                            @foreach ($bookingSources as $source)
                                                <option value="{{ $source->id }}" @selected(old('booking_source_id') == $source->id)>{{ $source->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('booking_source_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="payment_method" class="form-label">Payment Method*</label>
                                        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
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
                            </fieldset>

                            <hr class="my-4">
                            <button type="submit" class="btn btn-success btn-lg w-100">@if($registration->is_group_lead)Complete Group Check-in @else Complete check-in @endif</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection