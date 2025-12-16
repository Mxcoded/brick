<form action="{{ $order ? route('banquet.orders.update', $order->order_id) : route('banquet.orders.store') }}"
    method="POST" class="needs-validation banquet-form-theme" novalidate>
    @csrf
    @if ($order)
        @method('PUT')
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-gold text-white">
            <h5 class="card-title mb-0"><i class="fas fa-clipboard-list me-2"></i>Order Details</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="date" name="preparation_date" id="preparation_date"
                            class="form-control bg-light"
                            value="{{ old('preparation_date', $order ? $order->preparation_date->toDateString() : now()->toDateString()) }}"
                            required>
                        <label for="preparation_date" class="text-muted">Preparation Date</label>
                    </div>
                    @error('preparation_date')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="status" id="status" class="form-select" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}"
                                    {{ old('status', $order ? $order->status : 'Pending') === $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        <label for="status" class="text-muted">Event Status</label>
                        @error('status')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-gold text-white">
            <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i>Customer Details</h5>
        </div>
        <div class="card-body">
            
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <div class="form-floating">
                        <select name="customer_id" id="customer_id" class="form-select">
                            <option value="">{{ $order ? 'Switch to New/Other Customer...' : 'Create New Customer' }}</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" 
                                    data-name="{{ $customer->name }}"
                                    data-email="{{ $customer->email }}" 
                                    data-phone="{{ $customer->phone }}"
                                    data-org="{{ $customer->organization }}"
                                    {{ old('customer_id', $order->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->organization ?? 'Private' }})
                                </option>
                            @endforeach
                        </select>
                        <label for="customer_id" class="text-muted">
                            {{ $order ? 'Change Attached Customer' : 'Select Existing Customer' }}
                        </label>
                    </div>
                    @if($order)
                        <div class="form-text text-muted">
                            <i class="fas fa-info-circle me-1"></i> 
                            Changing this will link the order to a different client profile.
                        </div>
                    @endif
                </div>
            </div>

            <div id="customer-form-container">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-4 h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-charcoal"><i class="fas fa-user-tie me-2"></i>Primary Contact</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-floating mb-3">
                                    <input type="text" name="contact_person_name" id="contact_person_name"
                                        class="form-control"
                                        value="{{ old('contact_person_name', $order->contact_person_name ?? '') }}"
                                        required>
                                    <label for="contact_person_name" class="text-muted">Full Name</label>
                                    @error('contact_person_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="text" name="contact_person_phone" id="contact_person_phone"
                                        class="form-control"
                                        value="{{ old('contact_person_phone', $order->contact_person_phone ?? '') }}"
                                        required>
                                    <label for="contact_person_phone" class="text-muted">Phone Number</label>
                                    @error('contact_person_phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-floating">
                                    <input type="email" name="contact_person_email" id="contact_person_email"
                                        class="form-control"
                                        value="{{ old('contact_person_email', $order->contact_person_email ?? '') }}"
                                        required>
                                    <label for="contact_person_email" class="text-muted">Email Address</label>
                                    @error('contact_person_email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-4 h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-charcoal"><i class="fas fa-building me-2"></i>Organization Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-floating mb-3">
                                    <input type="text" name="organization" id="organization" class="form-control"
                                        value="{{ old('organization', $order->customer->organization ?? '') }}">
                                    <label for="organization" class="text-muted">Organization Name</label>
                                    @error('organization')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="text" name="department" id="department" class="form-control"
                                        value="{{ old('department', $order->department ?? '') }}">
                                    <label for="department" class="text-muted">Department</label>
                                    @error('department')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-floating">
                                    <input type="text" name="referred_by" id="referred_by" class="form-control"
                                        value="{{ old('referred_by', $order->referred_by ?? '') }}">
                                    <label for="referred_by" class="text-muted">Referred By</label>
                                    @error('referred_by')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-charcoal"><i class="fas fa-user-friends me-2"></i>Secondary Contact
                                    (Optional)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="text" name="contact_person_name_ii"
                                                id="contact_person_name_ii" class="form-control"
                                                value="{{ old('contact_person_name_ii', $order->contact_person_name_ii ?? '') }}">
                                            <label for="contact_person_name_ii" class="text-muted">Full Name</label>
                                            @error('contact_person_name_ii')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="text" name="contact_person_phone_ii"
                                                id="contact_person_phone_ii" class="form-control"
                                                value="{{ old('contact_person_phone_ii', $order->contact_person_phone_ii ?? '') }}">
                                            <label for="contact_person_phone_ii" class="text-muted">Phone
                                                Number</label>
                                            @error('contact_person_phone_ii')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="email" name="contact_person_email_ii"
                                                id="contact_person_email_ii" class="form-control"
                                                value="{{ old('contact_person_email_ii', $order->contact_person_email_ii ?? '') }}">
                                            <label for="contact_person_email_ii" class="text-muted">Email
                                                Address</label>
                                            @error('contact_person_email_ii')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-gold text-white">
            <h5 class="card-title mb-0"><i class="fas fa-coins me-2"></i>Financial Details</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" name="expenses" id="expenses" class="form-control"
                            value="{{ old('expenses', $order->expenses ?? 0) }}" step="0.01" min="0">
                        <label for="expenses" class="text-muted">Estimated Expenses (₦)</label>
                        @error('expenses')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" name="hall_rental_fees" id="hall_rental_fees" class="form-control"
                            value="{{ old('hall_rental_fees', $order->hall_rental_fees ?? 0) }}" step="0.01" min="0">
                        <label for="hall_rental_fees" class="text-muted">Hall Rental Fees (₦)</label>
                        @error('hall_rental_fees')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="alert alert-info mb-0 bg-light border-0 text-charcoal">
                        <i class="fas fa-info-circle me-2 text-gold"></i>
                        Total Revenue and Profit Margin will be calculated automatically based on menu items + hall fees.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ url()->previous() }}" class="btn btn-outline-charcoal btn-lg">Cancel</a>
        <button type="submit" class="btn btn-gold btn-lg shadow">
            <i class="fas fa-save me-2"></i>{{ $order ? 'Update Order Details' : 'Create Order' }}
        </button>
    </div>
</form>

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            function handleCustomerSelection() {
                const selected = $('#customer_id').find(':selected');
                const isNewCustomer = selected.val() === '';
                $('#contact_person_name, #contact_person_phone, #contact_person_email').prop('required', true);

                if (!isNewCustomer) {
                    $('#contact_person_name').val(selected.data('name') || '');
                    $('#contact_person_phone').val(selected.data('phone') || '');
                    $('#contact_person_email').val(selected.data('email') || '');
                    $('#organization').val(selected.data('org') || '');
                } else {
                    if(document.activeElement.id === 'customer_id') {
                        $('#contact_person_name, #contact_person_phone, #contact_person_email, #organization').val('');
                    }
                }
            }
            $('#customer_id').on('change', handleCustomerSelection);
        });
    </script>
@endsection

<style>
    .banquet-form-theme {
        font-family: 'Proxima Nova', Arial, Helvetica, sans-serif;
    }
    .bg-gold { background-color: #C8A165 !important; }
    .text-charcoal { color: #333333 !important; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: white; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: white; }
    .btn-outline-charcoal { color: #333333; border-color: #333333; }
    .btn-outline-charcoal:hover { background-color: #333333; color: white; }
    
    .card {
        border-radius: 0.75rem;
        transition: transform 0.2s;
    }
    .form-floating>label {
        color: #6c757d;
    }
    .form-control:focus, .form-select:focus {
        border-color: #C8A165;
        box-shadow: 0 0 0 0.25rem rgba(200, 161, 101, 0.25);
    }
</style>