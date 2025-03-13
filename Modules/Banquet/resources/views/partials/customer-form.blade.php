<form action="{{ $order ? route('banquet.orders.update', $order->order_id) : route('banquet.orders.store') }}"
    method="POST" class="needs-validation" novalidate>
    @csrf
    @if ($order)
        @method('PUT')
    @endif

    <!-- Order Details Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0"><i class="fas fa-clipboard-list me-2"></i>Order Details</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="date" name="preparation_date" id="preparation_date"
                            class="form-control bg-light"
                            value="{{ old('preparation_date', $order->preparation_date ?? now()->toDateString()) }}"
                            readonly required>
                        {{-- value="{{ old('preparation_date', $order->preparation_date ?? now()->toDateString()) }}" 
                               {{ $order ? '' : 'readonly' }} required> --}}
                        <label for="preparation_date" class="text-muted">Preparation Date</label>
                    </div>
                    @error('preparation_date')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="status" id="event_status" class="form-select" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" {{ old('event_status', 'Pending') === $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        <label for="event_status" class="text-muted">Event Status</label>
                        @error('event_status') 
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                  </div>
            </div>
        </div>
    </div>

    <!-- Customer Details Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i>Customer Details</h5>
        </div>
        <div class="card-body">
            @if (!$order)
                <!-- Show customer selection only in create mode -->
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="form-floating">
                            <select name="customer_id" id="customer_id" class="form-select">
                                <option value="">New Customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" data-name="{{ $customer->name }}"
                                        data-email="{{ $customer->email }}" data-phone="{{ $customer->phone }}"
                                        data-org="{{ $customer->organization }}"
                                        {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->organization }})
                                    </option>
                                @endforeach
                            </select>
                            <label for="customer_id" class="text-muted">Select Existing Customer</label>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Customer form: always visible in edit mode, toggleable in create mode -->
            <div id="customer-form"
                class="collapse {{ $order ? 'show' : (old('customer_id', $order->customer_id ?? '') === '' ? 'show' : '') }}">
                <div class="row g-3">
                    <!-- Primary Contact -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-user-tie me-2"></i>Primary Contact</h6>
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

                    <!-- Organization Details -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-building me-2"></i>Organization Details</h6>
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

                    <!-- Secondary Contact -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-user-friends me-2"></i>Secondary Contact
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

    <!-- Financial Details Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0"><i class="fas fa-coins me-2"></i>Financial Details</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" name="expenses" id="expenses" class="form-control"
                            value="{{ old('expenses', $order->expenses ?? 0) }}" step="0.01" min="0">
                        <label for="expenses" class="text-muted">Estimated Expenses (&#8358;)</label>
                        @error('expenses')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                  
                <div class="col-md-12">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Total Revenue and Profit Margin will be calculated automatically after adding menu items.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg shadow">
            <i class="fas fa-check-circle me-2"></i>{{ $order ? 'Update Order' : 'Create Order' }}
        </button>
    </div>
</form>

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            const customerForm = new bootstrap.Collapse(document.getElementById('customer-form'), {
                toggle: false
            });

            @if (!$order)
                // Handle customer selection only in create mode
                $('#customer_id').on('change', function() {
                    const selected = $(this).find(':selected');
                    const isNewCustomer = selected.val() === '';

                    // Toggle required attributes
                    $('#contact_person_name, #contact_person_phone, #contact_person_email')
                        .prop('required', isNewCustomer);

                    if (isNewCustomer) {
                        customerForm.show();
                        clearCustomerFields();
                    } else {
                        customerForm.hide();
                        populateCustomerFields(selected);
                    }
                });

                function populateCustomerFields(selected) {
                    $('#contact_person_name').val(selected.data('name') || '');
                    $('#contact_person_phone').val(selected.data('phone') || '');
                    $('#contact_person_email').val(selected.data('email') || '');
                }

                function clearCustomerFields() {
                    $('#contact_person_name, #contact_person_phone, #contact_person_email').val('');
                }
            @endif

            // Ensure customer form is shown in edit mode
            @if ($order)
                customerForm.show();
            @endif
        });
    </script>
@endsection

<style>
    .card {
        border-radius: 0.75rem;
        transition: transform 0.2s;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    .form-floating>label {
        color: #6c757d;
        padding: 0.5rem 1rem;
    }

    .form-control,
    .form-select {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        border: 1px solid #dee2e6;
    }

    .form-control:focus,
    .form-select:focus {
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
    }

    .invalid-feedback {
        font-size: 0.85rem;
    }

    .alert {
        border-radius: 0.5rem;
    }

    .btn-primary {
        transition: all 0.2s;
    }
</style>
