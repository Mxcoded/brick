@extends('gym::layouts.master')

@section('content')
    <div class="container">
        <h1>Membership Details: #{{ $membership->id }}</h1>

        <!-- Membership Information -->
        <div class="card mb-3">
            <div class="card-header">Membership Information</div>
            <div class="card-body">
                <p><strong>Package Type:</strong> {{ ucfirst($membership->package_type) }}</p>
                <p><strong>Subscription Plan:</strong> {{ ucfirst($membership->subscription_plan) }}</p>
                <p><strong>Personal Trainer:</strong> {{ ucfirst($membership->personal_trainer) }}</p>
                @if ($membership->personal_trainer === 'yes')
                    <p><strong>Sessions:</strong> {{ $membership->sessions }}</p>
                @endif
                <p><strong>Start Date:</strong> {{ $membership->start_date->format('Y-m-d') }}</p>
                <p><strong>Next Billing Date:</strong> {{ $membership->next_billing_date->format('Y-m-d') }}
                    <span class="badge {{ $membership->next_billing_date->isFuture() ? 'bg-success' : 'bg-danger' }}">
                        {{ $membership->next_billing_date->isFuture() ? 'Upcoming' : 'Due' }}
                    </span>
                </p>
                <p><strong>Registered By:</strong> {{ $membership->createdBy->name ?? 'N/A' }}</p>
                <p><strong>Total Cost:</strong> {{ $membership->total_cost }}</p>
                <p><strong>Total Paid:</strong> {{ $totalPaid }}</p>
                <p><strong>Remaining Balance:</strong> {{ $remainingBalance }}</p>
                <p><strong>Status:</strong> {{ $status }}</p>
            </div>
        </div>

        <!-- Member Information -->
        <div class="card mb-3">
            <div class="card-header">Member Information</div>
            <div class="card-body">
                @foreach ($membership->members as $member)
                    {{-- <div class="mb-3">
                        <h4>{{ $member->full_name }}</h4>
                        <p><strong>Date of Birth:</strong> {{ $member->date_of_birth->format('Y-m-d') }}</p>
                        <p><strong>Gender:</strong> {{ ucfirst($member->gender) }}</p>
                        <p><strong>Phone Number:</strong> {{ $member->phone_number }}</p>
                        <p><strong>Email Address:</strong> {{ $member->email_address ?? 'N/A' }}</p>
                        <p><strong>Home Address:</strong> {{ $member->home_address }}</p>
                        <p><strong>Emergency Contact:</strong> {{ $member->emergency_contact_name }} ({{ $member->emergency_contact_relationship }}) - {{ $member->emergency_contact_number }}</p>
                    </div> --}}
                    <div class="mb-3">
                        <h4>{{ $member->full_name }}</h4>
                        <p><strong>Date of Birth:</strong> {{ $member->date_of_birth->format('Y-m-d') }}</p>
                        <p><strong>Gender:</strong> {{ ucfirst($member->gender) }}</p>
                        <p><strong>Phone Number:</strong> {{ $member->phone_number }}</p>
                        <p><strong>Email Address:</strong> {{ $member->email_address ?? 'N/A' }}</p>
                        <p><strong>Home Address:</strong> {{ $member->home_address }}</p>
                        <p><strong>Emergency Contact:</strong> {{ $member->emergency_contact_name }} ({{ $member->emergency_contact_relationship }}) - {{ $member->emergency_contact_number }}</p>
                        <p><strong>Medical Conditions:</strong> {{ $member->medical_conditions ?? 'None' }}</p>
                        <p><strong>Fitness Goals:</strong> {{ implode(', ', json_decode($member->fitness_goals, true) ?? []) }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Payment History -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                Payment History
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                    Add Payment
                </button>
            </div>
            <div class="card-body">
                @if ($membership->payments->isEmpty())
                    <p>No payments made yet.</p>
                @else
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                      <thead class="table-dark">
                  
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Mode</th>
                                <th>Remaining Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($membership->payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                                    <td>{{ $payment->payment_amount }}</td>
                                    <td>{{ ucfirst($payment->payment_status) }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_mode)) }}</td>
                                    <td>{{ $payment->remaining_balance }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        <!-- Trainer Payments -->
        @if ($membership->personal_trainer === 'yes')
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    Trainer Payments
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTrainerPaymentModal">
                        Add Trainer Payment
                    </button>
                </div>
                <div class="card-body">
                    @if ($membership->trainerPayments->isEmpty())
                        <p>No trainer payments made yet.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Mode</th>
                                    <th>Remaining Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($membership->trainerPayments as $trainerPayment)
                                    <tr>
                                        <td>{{ $trainerPayment->payment_date->format('Y-m-d') }}</td>
                                        <td>{{ $trainerPayment->payment_amount }}</td>
                                        <td>{{ ucfirst($trainerPayment->payment_type) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $trainerPayment->payment_mode)) }}</td>
                                        <td>{{ $trainerPayment->remaining_balance }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="mt-3">
            <a href="{{ route('gym.memberships.edit', $membership->id) }}" class="btn btn-primary">Edit</a>
            <form action="{{ route('gym.memberships.delete', $membership->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this membership?')">Delete</button>
            </form>
            <a href="{{ route('gym.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <!-- Add Payment Modal -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPaymentModalLabel">Add Membership Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('gym.memberships.payments.store', $membership->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="payment_amount" class="form-label">Payment Amount</label>
                            <input type="number" class="form-control" id="payment_amount" name="payment_amount" step="0.01" min="0" required>
                            @error('payment_amount')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        {{-- <div class="mb-3"> --}}
                            {{-- <label for="payment_date" class="form-label">Payment Date</label> --}}
                            <input type="datetime-local" hidden class="form-control" name="payment_date" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                            @error('payment_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        {{-- </div> --}}
                        <div class="mb-3">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select class="form-select" id="payment_status" name="payment_status" required>
                                <option value="paid">Paid</option>
                                <option value="partial">Partial</option>
                                <option value="pending">Pending</option>
                                <option value="overdue">Overdue</option>
                            </select>
                            @error('payment_status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="payment_mode" class="form-label">Payment Mode</label>
                            <select class="form-select" id="payment_mode" name="payment_mode" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="pos">POS</option>
                                <option value="crypto">Crypto</option>
                            </select>
                            @error('payment_mode')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Add Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Trainer Payment Modal -->
    @if ($membership->personal_trainer === 'yes')
        <div class="modal fade" id="addTrainerPaymentModal" tabindex="-1" aria-labelledby="addTrainerPaymentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTrainerPaymentModalLabel">Add Trainer Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('gym.memberships.trainer-payments.store', $membership->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="trainer_id" class="form-label">Trainer</label>
                                <select class="form-select" id="trainer_id" name="trainer_id" required>
                                    <option value="">Select Trainer</option>
                                    @foreach ($trainers as $trainer)
                                        <option value="{{ $trainer->id }}">{{ $trainer->full_name }}</option>
                                    @endforeach
                                </select>
                                @error('trainer_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="payment_amount" class="form-label">Payment Amount</label>
                                <input type="number" class="form-control" id="payment_amount" name="payment_amount" step="0.01" min="0" required>
                                @error('payment_amount')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            {{-- <div class="mb-3">
                                <label for="payment_date" class="form-label">Payment Date</label> --}}
                                <input type="datetime-local" hidden class="form-control" name="payment_date" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                @error('payment_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            {{-- </div> --}}
                            <div class="mb-3">
                                <label for="payment_type" class="form-label">Payment Type</label>
                                <select class="form-select" id="payment_type" name="payment_type" required>
                                    <option value="full">Full</option>
                                    <option value="partial">Partial</option>
                                </select>
                                @error('payment_type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="payment_mode" class="form-label">Payment Mode</label>
                                <select class="form-select" id="payment_mode" name="payment_mode" required>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="pos">POS</option>
                                    <option value="crypto">Crypto</option>
                                </select>
                                @error('payment_mode')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Add Trainer Payment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection