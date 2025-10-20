@extends('layouts.master')

@section('title', 'Finalize Check-in')

@section('page-content')
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <form action="{{ route('frontdesk.registrations.finalize', $registration) }}" method="POST">
                    @csrf
                    <div class="card shadow-lg">
                        <div class="card-header bg-warning text-dark">
                            <h4><i class="fas fa-check-double me-2"></i>Finalize Check-in for {{ $registration->full_name }}
                            </h4>
                        </div>
                        <div class="card-body p-4">

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
                                        <input type="text" class="form-control" name="room_allocation" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="room_rate" class="form-label">Room Rate (per night)*</label>
                                        <input type="number" step="0.01" class="form-control" name="room_rate" required>
                                    </div>
                                    <div class="col-md-4 mb-3 d-flex align-items-end">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="bed_breakfast"
                                                value="1" id="bed_breakfast">
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
                                                            <input type="text" class="form-control"
                                                                name="group_members[{{ $member->id }}][room_allocation]"
                                                                placeholder="Room No." required>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" class="form-control"
                                                                name="group_members[{{ $member->id }}][room_rate]"
                                                                placeholder="Rate" required>
                                                        </td>
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="group_members[{{ $member->id }}][bed_breakfast]"
                                                                    value="1">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <select class="form-select"
                                                                name="group_members[{{ $member->id }}][status]" required>
                                                                <option value="checked_in" selected>Check-in</option>
                                                                <option value="no_show">No-Show</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                <h6 class="mt-4">Overall Booking Details</h6>
                                <div class="row">
                                    {{-- WRAP THE BILLING DROPDOWN IN THIS IF STATEMENT --}}
                                    @if ($registration->is_group_lead)
                                        <div class="col-md-4 mb-3">
                                            <label for="billing_type" class="form-label">Billing Method*</label>
                                            <select name="billing_type" class="form-select" required>
                                                <option value="consolidate" selected>Consolidate on Group Lead</option>
                                                <option value="individual">Individual Billing (Each Pays Own)</option>
                                            </select>
                                        </div>
                                    @endif
                                    <div class="col-md-4 mb-3">
                                        <label for="guest_type_id" class="form-label">Guest Type*</label>
                                        <select name="guest_type_id" class="form-select" required>
                                            @foreach ($guestTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="booking_source_id" class="form-label">Booking Source*</label>
                                        <select name="booking_source_id" class="form-select" required>
                                            @foreach ($bookingSources as $source)
                                                <option value="{{ $source->id }}">{{ $source->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="payment_method" class="form-label">Payment Method*</label>
                                        <select name="payment_method" class="form-select" required>
                                            <option value="pos">POS</option>
                                            <option value="cash">Cash</option>
                                            <option value="transfer">Transfer</option>
                                            <option value="credit_balance">Credit Balance</option>
                                            <option value="credit">Credit from other branches</option>
                                        </select>
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
