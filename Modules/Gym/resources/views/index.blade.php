{{-- @extends('gym::layouts.master')

@section('content')
    <div class="container">
        <h1>Gym Memberships</h1>
        
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <a href="{{ route('gym.memberships.create') }}" class="btn btn-primary mb-3"><span
                    class="fas fa-plus-circle"></span> Add New Membership</a>
            <a href="{{ route('gym.trainers.create') }}" class="btn btn-primary mb-3"><span class="fas fa-plus-circle"></span>
                Add New Trainer</a>
            <a href="{{ route('gym.subscription-config.edit') }}" class="btn btn-success mb-3"><span
                    class="fas fa-cog"></span> Set Package Fee</a>

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
                                    {{ ucfirst($payment->payment_status) }} ({{ $payment->payment_amount }} on
                                    {{ $payment->payment_date->format('Y-m-d') }} via
                                    {{ ucfirst($payment->payment_mode) }})<br>
                                    @if ($payment->payment_type === 'partial')
                                        <small>Remaining Balance: {{ $payment->remaining_balance }}</small><br>
                                    @endif
                                @endforeach
                            </td>
                            <td>{{ $membership->createdBy->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('gym.memberships.show', $membership->id) }}"
                                    class="btn btn-sm btn-info">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
@endsection --}}
@extends('gym::layouts.master')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2 mb-0"><i class="fas fa-id-card-alt me-2"></i>Gym Memberships</h1>
            <div class="btn-group">
                <a href="{{ route('gym.memberships.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i> Add Member(s)
                </a>
                <a href="{{ route('gym.trainers.create') }}" class="btn btn-outline-primary">
                    <i class="fas fa-user-plus me-1"></i> Add Trainer
                </a>
                <a href="{{ route('gym.subscription-config.edit') }}" class="btn btn-success">
                    <i class="fas fa-cog me-1"></i> Package Fee
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($memberships->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                    <h3>No Memberships Found</h3>
                    <p class="text-muted">Get started by adding a new membership</p>
                    <a href="{{ route('gym.memberships.create') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus-circle me-1"></i> Create Membership
                    </a>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <h5 class="mb-0">Membership Records</h5>
                    <div class="d-flex mt-2 mt-md-0">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive ">
                        <table id="gymMemberTable" class="table table-hover align-middle mb-0" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Member's Name</th>
                                    <th>Package</th>
                                    <th>Plan</th>
                                    <th>Trainer</th>
                                    <th class="text-center">Sessions</th>
                                    <th>Start Date</th>
                                    <th>Next Billing</th>
                                    <th>Payment Summary</th>
                                    <th>Registered By</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($memberships as $membership)
                                <tr>
                                    <td class="fw-bold">{{ $membership->id }}</td>
                                    <td>
                                        @foreach ($membership->members as $member)
                                            <div class="badge bg-info bg-opacity-10 text-dark">
                                                {{ strtoupper($member->full_name) }}
                                            </div>
                                        @endforeach
                                    </td>
                                    <td><span class="badge bg-primary">{{ ucfirst($membership->package_type) }}</span></td>
                                    <td>{{ ucfirst($membership->subscription_plan) }}</td>
                                    <td>
                                        @if($membership->personal_trainer)
                                            <span class="badge bg-success">{{ ucfirst($membership->personal_trainer) }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $membership->sessions ?? '0' }}</td>
                                    <td>
                                        <div class="text-nowrap">{{ $membership->start_date->format('Y-m-d') }}</div>
                                        <small class="text-muted">{{ $membership->start_date->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="text-nowrap">{{ $membership->next_billing_date->format('Y-m-d') }}</div>
                                        <small class="text-muted">{{ $membership->next_billing_date->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        @foreach ($membership->payments as $payment)
                                            <div class="mb-1">
                                                <span class="badge {{ $payment->payment_status === 'paid' ? 'bg-success' : 'bg-warning' }}">
                                                    {{ ucfirst($payment->payment_status) }}
                                                </span>
                                                <div class="fw-bold">&#8358;{{ number_format($payment->payment_amount,2) }}</div>
                                                <small>
                                                    {{ $payment->payment_date->format('M d') }} via 
                                                    <span class="text-capitalize">{{ $payment->payment_mode }}</span>
                                                </small>
                                                @if ($payment->payment_type === 'partial')
                                                    <div class="text-danger small">
                                                        Balance: &#8358;{{number_format($payment->remaining_balance,2) }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>{{ $membership->createdBy->name ?? 'System' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('gym.memberships.show', $membership->id) }}" 
                                           class="btn btn-sm btn-primary btn-outline-dark"
                                           data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <!-- Include DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#gymMemberTable').DataTable({
                responsive: true,
                language: {
                    searchPlaceholder: "Search records...",
                    search: "",
                },
                columnDefs: [
                    { responsivePriority: 1, targets: 1 }, // Member name
                    { responsivePriority: 2, targets: -1 }, // Actions
                    { orderable: false, targets: [5, 8, 10] } // Disable sorting on sessions, payments, actions
                ],
                initComplete: function() {
                    // Add custom search
                    $('#searchInput').on('keyup', function() {
                        $('#gymMemberTable').DataTable().search(this.value).draw();
                    });
                }
            });

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
@endpush

@push('styles')
    <style>
        .card {
            border-radius: 0.75rem;
        }
        .table thead th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        .badge {
            padding: 0.35em 0.5em;
            font-weight: 500;
        }
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 0.375rem 0.75rem;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
        }
    </style>
@endpush