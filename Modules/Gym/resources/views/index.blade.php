@extends('gym::layouts.master')

@section('content')
    <div class="container">
        <h1>Gym Memberships</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <a href="{{ route('gym.memberships.create') }}" class="btn btn-primary mb-3"><span class="fas fa-plus-circle"></span> Add New Membership</a>
        <a href="{{ route('gym.trainers.create') }}" class="btn btn-primary mb-3"><span class="fas fa-plus-circle"></span> Add New Trainer</a>
        <a href="{{ route('gym.subscription-config.edit') }}" class="btn btn-success mb-3"><span class="fas fa-cog"></span> Set Package Fee</a>

        @if ($memberships->isEmpty())
            <p>No memberships found.</p>
        @else
        <div class="table-responsive">
            <table id="gymMemberTable" class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Member's name</th>
                        <th>Package Type</th>
                        <th>Subscription Plan</th>
                        <th>Personal Trainer</th>
                        <th>Sessions</th>
                        <th>Start Date</th>
                        <th>Next Billing</th>
                        <th>Payment summary</th>
                        <th>Registered By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($memberships as $membership)
                        <tr>
                            <td>{{ $membership->id }}</td>
                            <td>
                                @foreach ($membership->members as $member)
                                    {{ $member->full_name }}<br>
                                @endforeach
                            </td>
                            <td>{{ ucfirst($membership->package_type) }}</td>
                            <td>{{ ucfirst($membership->subscription_plan) }}</td>
                            <td>{{ ucfirst($membership->personal_trainer) }}</td>
                            <td>{{ $membership->sessions ?? 'N/A' }}</td>
                            <td>{{ $membership->start_date->format('Y-m-d') }}</td>
                            <td>{{ $membership->next_billing_date->format('Y-m-d') }}</td>
                            <td>
                                @foreach ($membership->payments as $payment)
                                    {{ ucfirst($payment->payment_status) }} ({{ $payment->payment_amount }} on {{ $payment->payment_date->format('Y-m-d') }} via {{ ucfirst($payment->payment_mode) }})<br>
                                    @if ($payment->payment_type === 'partial')
                                        <small>Remaining Balance: {{ $payment->remaining_balance }}</small><br>
                                    @endif
                                @endforeach
                            </td>
                            <td>{{ $membership->createdBy->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('gym.memberships.show', $membership->id) }}" class="btn btn-sm btn-info">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <script>
        $(document).ready(function() {
            $('#gymMemberTable').DataTable({
                responsive: true,
                columnDefs: [
                    { orderable: false, targets: [4, 7, 10] },
                    { searchable: false, targets: [4, 7, 10] }
                ]
            });
        });
    </script>
@endsection