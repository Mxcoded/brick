@extends('staff::layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Staff List</li>
@endsection

@section('page-content')
    <div class="container">
        <h1>Staff Management</h1>
        <a href="{{ route('staff.create') }}" class="mb-3 btn btn-primary"><i class="fas fa-plus me-1"></i> Add New Staff</a>
        <a href="{{ route('staff.dashboard') }}" class="mb-3 btn btn-primary"><i class="fas fa-plus me-1"></i> Dashboard</a>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Status Summary Cards -->
        <div class="mb-4 row">
            <!-- Total Approved Staff Card -->
            <div class="col-md-3 mb-4">
                <div class="card bg-primary text-white shadow-sm h-100 hover-scale">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="card-title mb-3">Total Approved Staff</h5>
                                <h3 class="mb-0">{{ $totalApprovedStaff }}</h3>
                            </div>
                            <div class="icon-circle bg-success-light">
                                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Staff Icon" class="img-fluid" style="width: 40px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Staff Card (New) -->
            <div class="col-md-3 mb-4">
                <div class="card bg-info text-white shadow-sm h-100 hover-scale">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="card-title mb-3">Active Staff</h5>
                                <h3 class="mb-0">{{ $activeStaffCount }}</h3>
                            </div>
                            <div class="icon-circle bg-primary-light">
                                <img src="https://cdn-icons-png.flaticon.com/512/1484/1484579.png" alt="Active Icon" class="img-fluid" style="width: 40px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff On Leave Card -->
            <div class="col-md-3 mb-4">
                <div class="card bg-success text-white shadow-sm h-100 hover-scale">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="card-title mb-3">Staff On Leave</h5>
                                <h3 class="mb-0">{{ $staffOnLeaveCount }}</h3>
                            </div>
                            <div class="icon-circle bg-primary-light">
                                <img src="https://cdn-icons-png.flaticon.com/512/1046/1046857.png" alt="Leave Icon" class="img-fluid" style="width: 40px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inactive Staff Card -->
            <div class="col-md-3 mb-4">
                <div class="card bg-danger text-white shadow-sm h-100 hover-scale">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="card-title mb-3">Inactive Staff</h5>
                                <h3 class="mb-0">{{ $employees->where('status', 'rejected')->count() }}</h3>
                            </div>
                            <div class="icon-circle bg-danger-light">
                                <img src="https://cdn-icons-png.flaticon.com/512/1828/1828843.png" alt="Inactive Icon" class="img-fluid" style="width: 40px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Table -->
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
                                        alt="{{ $employee->name }}'s Profile Photo" class="staff-profile-image" width="50"
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