@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Staff List</li>
@endsection

@section('page-content')
    <div class="container-fluid my-4">
        <h1>Staff Management</h1>
        <a href="{{ route('staff.create') }}" class="mb-3 btn btn-primary"><i class="fas fa-plus me-1"></i> Add New Staff</a>
        <a href="{{ route('staff.approvals.index') }}" class="mb-3 btn btn-success"><i class="fas fa-check me-1"></i> Approve Staff Update</a>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4 row">
            <div class="col-md-3 mb-4">
                <div class="card bg-primary text-white shadow-sm h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="card-title mb-3">Total Approved Staff</h5>
                                <h3 class="mb-0">{{ $totalApprovedStaff }}</h3>
                            </div>
                            <div class="icon-circle bg-white-transparent">
                                <i class="fas fa-users fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card bg-info text-white shadow-sm h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="card-title mb-3">Active at Work</h5>
                                <h3 class="mb-0">{{ $activeStaffCount }}</h3>
                            </div>
                            <div class="icon-circle bg-white-transparent">
                                <i class="fas fa-user-check fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <a href="{{ route('staff.leaves.admin.history') }}" class="card-link">
                    <div class="card bg-warning text-dark shadow-sm h-100 hover-scale">
                        <div class="card-body p-4 d-flex flex-column">
                            <div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="card-title mb-3">Currently On Leave</h5>
                                        <h3 class="mb-0">{{ $staffOnLeaveCount }}</h3>
                                    </div>
                                    <div class="icon-circle bg-dark-transparent">
                                        <i class="fas fa-calendar-check fa-2x text-white"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-auto pt-2 text-end">
                                <small>View Report <i class="fas fa-arrow-right"></i></small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card bg-danger text-white shadow-sm h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="card-title mb-3">Inactive Staff</h5>
                                <h3 class="mb-0">{{ $employees->where('status', 'rejected')->count() }}</h3>
                            </div>
                            <div class="icon-circle bg-white-transparent">
                                <i class="fas fa-user-times fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="staffTable" class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Name <i>(Surname first)</i></th>
                        <th>Position</th>
                        <th>Phone Number</th>
                        <th>Email</th>
                        <th>Photo</th>
                        <th>Staff Code</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $employee)
                        <tr>
                            <td>{{ Str::upper($employee->name) }}</td>
                            <td>{{ Str::upper($employee->position) }}</td>
                            <td>{{ $employee->phone_number }}</td>
                            <td>{{ $employee->email }}</td>
                            <td>
                                @if ($employee->profile_image)
                                    <img src="{{ Storage::url($employee->profile_image) }}"
                                        alt="{{ $employee->name }}'s Profile Photo" class="staff-profile-image"
                                        loading="lazy">
                                @else
                                    <div class="no-photo-placeholder">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $employee->staff_code }}</td>
                            <td>
                                <span class="badge
                                    @if ($employee->status == 'approved') bg-success
                                    @elseif($employee->status == 'rejected') bg-danger
                                    @elseif($employee->status == 'pending') bg-warning
                                    @else bg-secondary @endif">
                                    {{ ucfirst($employee->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="gap-2 d-flex">
                                    <a href="{{ route('staff.show', $employee->id) }}" class="btn btn-sm btn-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('staff.edit', $employee->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .staff-profile-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
        .no-photo-placeholder {
            width: 50px;
            height: 50px;
            background: #f0f0f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .no-photo-placeholder i {
            font-size: 24px;
            color: #666;
        }

        /* ### NEW CSS FOR CLICKABLE CARDS ### */
        .card-link {
            text-decoration: none;
            color: inherit; /* Inherit text color from the card */
        }
        .hover-scale {
            transition: transform 0.2s ease-in-out;
        }
        .hover-scale:hover {
            transform: scale(1.03); /* Slightly enlarge the card on hover */
        }
        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bg-white-transparent {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .bg-dark-transparent {
            background-color: rgba(0, 0, 0, 0.2);
        }
    </style>
@endsection

@section('page-scripts')
    <script>
        $(document).ready(function() {
            $('#staffTable').DataTable({
                responsive: true,
                columnDefs: [
                    { orderable: false, targets: [4, 7] },
                    { searchable: false, targets: [4, 7] }
                ]
            });
        });
    </script>
@endsection