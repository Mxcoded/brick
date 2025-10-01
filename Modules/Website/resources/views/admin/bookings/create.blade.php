@extends('website::layouts.admin')

@section('title', 'Create Booking')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-0">Create Booking</h1>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('website.admin.bookings.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="room_id" class="form-label">Room</label>
                            <select class="form-control @error('room_id') is-invalid @enderror" id="room_id" name="room_id" required>
                                <option value="">Select Room</option>
                                @foreach ($rooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                                @endforeach
                            </select>
                            @error('room_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Registered User (Optional)</label>
                            <input type="text" class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id" value="{{ old('user_id') }}" placeholder="Enter User ID if applicable">
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="guest_name" class="form-label">Guest Name</label>
                            <input type="text" class="form-control @error('guest_name') is-invalid @enderror" id="guest_name" name="guest_name" value="{{ old('guest_name') }}" required>
                            @error('guest_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="guest_email" class="form-label">Guest Email</label>
                            <input type="email" class="form-control @error('guest_email') is-invalid @enderror" id="guest_email" name="guest_email" value="{{ old('guest_email') }}" required>
                            @error('guest_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="guest_phone" class="form-label">Guest Phone</label>
                            <input type="text" class="form-control @error('guest_phone') is-invalid @enderror" id="guest_phone" name="guest_phone" value="{{ old('guest_phone') }}" required>
                            @error('guest_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="guest_company" class="form-label">Company (Optional)</label>
                            <input type="text" class="form-control @error('guest_company') is-invalid @enderror" id="guest_company" name="guest_company" value="{{ old('guest_company') }}">
                            @error('guest_company')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="guest_address" class="form-label">Address (Optional)</label>
                            <textarea class="form-control @error('guest_address') is-invalid @enderror" id="guest_address" name="guest_address">{{ old('guest_address') }}</textarea>
                            @error('guest_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="guest_nationality" class="form-label">Nationality (Optional)</label>
                            <input type="text" class="form-control @error('guest_nationality') is-invalid @enderror" id="guest_nationality" name="guest_nationality" value="{{ old('guest_nationality') }}">
                            @error('guest_nationality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="guest_id_type" class="form-label">ID Type (Optional)</label>
                            <select class="form-control @error('guest_id_type') is-invalid @enderror" id="guest_id_type" name="guest_id_type">
                                <option value="">Select ID Type</option>
                                <option value="passport" {{ old('guest_id_type') == 'passport' ? 'selected' : '' }}>Passport</option>
                                <option value="driver_license" {{ old('guest_id_type') == 'driver_license' ? 'selected' : '' }}>Driver’s License</option>
                                <option value="national_id" {{ old('guest_id_type') == 'national_id' ? 'selected' : '' }}>National ID</option>
                            </select>
                            @error('guest_id_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="guest_id_number" class="form-label">ID Number (Optional)</label>
                            <input type="text" class="form-control @error('guest_id_number') is-invalid @enderror" id="guest_id_number" name="guest_id_number" value="{{ old('guest_id_number') }}">
                            @error('guest_id_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="check_in" class="form-label">Check-In Date</label>
                            <input type="date" class="form-control @error('check_in') is-invalid @enderror" id="check_in" name="check_in" value="{{ old('check_in') }}" required>
                            @error('check_in')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="check_out" class="form-label">Check-Out Date</label>
                            <input type="date" class="form-control @error('check_out') is-invalid @enderror" id="check_out" name="check_out" value="{{ old('check_out') }}" required>
                            @error('check_out')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="number_of_guests" class="form-label">Number of Guests</label>
                            <input type="number" class="form-control @error('number_of_guests') is-invalid @enderror" id="number_of_guests" name="number_of_guests" value="{{ old('number_of_guests', 1) }}" min="1" required>
                            @error('number_of_guests')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="number_of_children" class="form-label">Number of Children</label>
                            <input type="number" class="form-control @error('number_of_children') is-invalid @enderror" id="number_of_children" name="number_of_children" value="{{ old('number_of_children', 0) }}" min="0" required>
                            @error('number_of_children')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="special_requests" class="form-label">Special Requests</label>
                            <textarea class="form-control @error('special_requests') is-invalid @enderror" id="special_requests" name="special_requests">{{ old('special_requests') }}</textarea>
                            @error('special_requests')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="total_price" class="form-label">Total Price</label>
                            <input type="number" step="0.01" class="form-control @error('total_price') is-invalid @enderror" id="total_price" name="total_price" value="{{ old('total_price') }}" required>
                            @error('total_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="deposit_amount" class="form-label">Deposit Amount (Optional)</label>
                            <input type="number" step="0.01" class="form-control @error('deposit_amount') is-invalid @enderror" id="deposit_amount" name="deposit_amount" value="{{ old('deposit_amount') }}">
                            @error('deposit_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select class="form-control @error('payment_status') is-invalid @enderror" id="payment_status" name="payment_status" required>
                                <option value="pending" {{ old('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="partial" {{ old('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="refunded" {{ old('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                            @error('payment_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method (Optional)</label>
                            <select class="form-control @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method">
                                <option value="">Select Payment Method</option>
                                <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="source" class="form-label">Booking Source (Optional)</label>
                            <select class="form-control @error('source') is-invalid @enderror" id="source" name="source">
                                <option value="">Select Source</option>
                                <option value="website" {{ old('source') == 'website' ? 'selected' : '' }}>Website</option>
                                <option value="phone" {{ old('source') == 'phone' ? 'selected' : '' }}>Phone</option>
                                <option value="OTA" {{ old('source') == 'OTA' ? 'selected' : '' }}>OTA</option>
                                <option value="walk_in" {{ old('source') == 'walk_in' ? 'selected' : '' }}>Walk-in</option>
                            </select>
                            @error('source')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="checked_in" {{ old('status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                                <option value="checked_out" {{ old('status') == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="no_show" {{ old('status') == 'no_show' ? 'selected' : '' }}>No Show</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Create Booking</button>
                <a href="{{ route('website.admin.bookings.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection