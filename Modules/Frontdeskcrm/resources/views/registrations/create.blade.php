@extends('layouts.master')

@section('page-content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0"
                style="background: var(--glass-effect); border: 1px solid var(--glass-border);">
                <div class="card-header bg-transparent border-0">
                    <h4 class="mb-0 text-dark fw-bold">Guest Registration Form</h4>
                    <p class="text-muted small">Welcome to Brickspoint Aparthotel</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('frontdesk.registrations.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Guest Details --}}
                        <h5 class="border-bottom pb-2 mb-4">Guest Details</h5>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                    name="title" value="{{ old('title') }}" placeholder="Mr./Mrs.">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-9 mb-3">
                                <label class="form-label">Full Name (Surname first) <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                    name="full_name" value="{{ old('full_name') }}" required>
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nationality</label>
                                <input type="text" class="form-control" name="nationality"
                                    value="{{ old('nationality') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('contact_number') is-invalid @enderror"
                                    name="contact_number" value="{{ old('contact_number') }}" required>
                                @error('contact_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Birthday</label>
                                <input type="date" class="form-control" name="birthday" value="{{ old('birthday') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Occupation</label>
                                <input type="text" class="form-control" name="occupation"
                                    value="{{ old('occupation') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company/Group Name</label>
                                <input type="text" class="form-control" name="company_name"
                                    value="{{ old('company_name') }}">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Home Address</label>
                                <textarea class="form-control" name="home_address" rows="2">{{ old('home_address') }}</textarea>
                            </div>
                        </div>

                        {{-- Booking Information --}}
                        <h5 class="border-bottom pb-2 mb-4">Booking Information</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Room Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('room_type') is-invalid @enderror" name="room_type"
                                    required>
                                    <option value="">Select Type</option>
                                    <option value="Standard" {{ old('room_type') == 'Standard' ? 'selected' : '' }}>
                                        Standard</option>
                                    <option value="Deluxe" {{ old('room_type') == 'Deluxe' ? 'selected' : '' }}>Deluxe
                                    </option>
                                    <option value="Suite" {{ old('room_type') == 'Suite' ? 'selected' : '' }}>Suite
                                    </option>
                                </select>
                                @error('room_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Room Rate <span class="text-danger">*</span></label>
                                <input type="number" step="0.01"
                                    class="form-control @error('room_rate') is-invalid @enderror" name="room_rate"
                                    value="{{ old('room_rate') }}" required>
                                @error('room_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Check-in <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('check_in') is-invalid @enderror"
                                    name="check_in" value="{{ old('check_in') }}" required
                                    min="{{ now()->format('Y-m-d') }}">
                                @error('check_in')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">No. of Guests <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('no_of_guests') is-invalid @enderror"
                                    name="no_of_guests" value="{{ old('no_of_guests', 1) }}" min="1"
                                    max="10" required>
                                @error('no_of_guests')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Check-out <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('check_out') is-invalid @enderror"
                                    name="check_out" value="{{ old('check_out') }}" required>
                                @error('check_out')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Bed & Breakfast</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="bed_breakfast"
                                        id="bed_breakfast" {{ old('bed_breakfast') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bed_breakfast">Included</label>
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="payment_method" id="cash"
                                        value="cash" {{ old('payment_method', 'cash') == 'cash' ? 'checked' : '' }}
                                        required>
                                    <label class="btn btn-outline-primary" for="cash">CASH</label>
                                    <input type="radio" class="btn-check" name="payment_method" id="pos"
                                        value="pos" {{ old('payment_method') == 'pos' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="pos">POS</label>
                                    <input type="radio" class="btn-check" name="payment_method" id="transfer"
                                        value="transfer" {{ old('payment_method') == 'transfer' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="transfer">TRANSFER</label>
                                </div>
                                @error('payment_method')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        {{-- After Booking Info section --}}
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_group_lead" id="is_group"
                                    value="1" {{ old('is_group_lead') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_group">Group Booking? (Add members below)</label>
                            </div>
                        </div>

                        {{-- Conditional Group Section --}}
                        <div id="group-section" style="display: none;" class="border p-3 rounded">
                            <h6>Group Members (up to {{ $max_members ?? 10 }})</h6>
                            <div id="group-members-container">
                                {{-- Dynamic rows via JS --}}
                                <div class="row mb-2 group-member-row">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="group_members[0][full_name]"
                                            placeholder="Full Name">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="tel" class="form-control"
                                            name="group_members[0][contact_number]" placeholder="Contact">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control"
                                            name="group_members[0][room_assignment]" placeholder="Room # (optional)">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="removeRow(this)">Remove</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="addGroupRow()">Add
                                Member</button>
                        </div>

                        {{-- Guest Type & Source (after Payment Method) --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Guest Type</label>
                                <select name="guest_type_id" class="form-select" required>
                                    @foreach ($guestTypes as $type)
                                        <option value="{{ $type->id }}"
                                            {{ old('guest_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }} ({{ $type->discount_rate }}% off)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Booking Source <span class="text-danger">*</span></label>
                                <select class="form-select @error('booking_source_id') is-invalid @enderror"
                                    name="booking_source_id" required>
                                    <option value="">Select Source</option>
                                    @foreach ($bookingSources as $source)
                                        <option value="{{ $source->id }}"
                                            {{ old('booking_source_id') == $source->id ? 'selected' : '' }}>
                                            {{ $source->name }}
                                            {{ $source->commission_rate > 0 ? '(Comm: ' . $source->commission_rate . '%)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('booking_source_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Emergency Contact --}}
                        <h5 class="border-bottom pb-2 mb-4">Emergency Contact</h5>
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="emergency_name"
                                    value="{{ old('emergency_name') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Relationship</label>
                                <input type="text" class="form-control" name="emergency_relationship"
                                    value="{{ old('emergency_relationship') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" name="emergency_contact"
                                    value="{{ old('emergency_contact') }}">
                            </div>
                        </div>

                        {{-- Policy Agreement --}}
                        <div class="alert alert-info">
                            <h6>Policy Agreement</h6>
                            <ul class="mb-0 small">
                                <li>The above agreed rate is valid for this stay only. For discounted rate for long stays,
                                    Bricks Point reserves the right to revert to the RACK RATE in the case of a check out
                                    before the agreed length of stay.</li>
                                <li>Check-in is at 3 pm and Check-out is at 12 noon. Early check-in and late check-out are
                                    subject to availability and additional fees. After 5 pm, a full night's rate applies. No
                                    show attracts a full day charge.</li>
                                <li>Room Key: A fine will be charged for lost room keys.</li>
                                <li>Personal safes are provided in each apartment. Please use them to store your valuables.
                                    Bricks Point is not liable for any loss.</li>
                                <li>I agree that in the event of sustaining an injury during my stay at the hotel or of my
                                    property being lost or damaged, I will notify the management of the hotel prior to my
                                    departure. I also agree that any claim that I may have arising out of such matters shall
                                    be subject to the laws of the country in which the hotel is situated and that the courts
                                    of this country shall have exclusive jurisdiction over any such claim.</li>
                            </ul>
                            <div class="form-check mt-3">
                                <input class="form-check-input @error('agreed_to_policies') is-invalid @enderror"
                                    type="checkbox" name="agreed_to_policies" id="agreed" value="1" required>
                                <label class="form-check-label" for="agreed">I agree to abide by the policies <span
                                        class="text-danger">*</span></label>
                                @error('agreed_to_policies')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Signature --}}
                        <div class="mb-4">
                            <label class="form-label">Guest Signature <span class="text-danger">*</span></label>
                            <canvas id="signature-pad" width="400" height="200" class="border rounded"
                                style="background: white;"></canvas>
                            <input type="hidden" name="guest_signature" id="signature-input">
                            <button type="button" class="btn btn-sm btn-secondary mt-2"
                                onclick="clearSignature()">Clear</button>
                        </div>

                        <div class="d-flex justify-content-between">
                            <input type="text" class="form-control w-auto" name="front_desk_agent"
                                value="{{ auth()->user()->name }}" readonly hidden>
                            <button type="submit" class="btn btn-primary px-4">Submit Registration</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        const canvas = document.getElementById('signature-pad');
        const signaturePad = new SignaturePad(canvas);
        const input = document.getElementById('signature-input');

        function clearSignature() {
            signaturePad.clear();
            input.value = '';
        }

        document.querySelector('form').addEventListener('submit', function() {
            if (signaturePad.isEmpty()) {
                alert('Please provide a signature');
                return false;
            }
            input.value = signaturePad.toDataURL();
        });

        // Resize canvas on load
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear(); // Re-draw if needed
        }
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        // Group Booking Logic
        let memberCount = 1;
        document.getElementById('is_group').addEventListener('change', function() {
            document.getElementById('group-section').style.display = this.checked ? 'block' : 'none';
        });

        function addGroupRow() {
            const container = document.getElementById('group-members-container');
            const row = container.children[0].cloneNode(true);
            row.querySelectorAll('input').forEach(input => input.name = input.name.replace(/\[0\]/, `[${memberCount}]`));
            row.querySelector('button').onclick = () => row.remove();
            container.appendChild(row);
            memberCount++;
        }

        function removeRow(btn) {
            btn.closest('.group-member-row').remove();
            if (container.children.length === 0) addGroupRow(); // Keep at least one
        }
    </script>
@endsection
