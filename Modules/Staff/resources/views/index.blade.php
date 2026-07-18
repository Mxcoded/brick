@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Staff List</li>
@endsection

@section('page-content')
    <div class="container-fluid my-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <h1 class="mb-0">Staff Management</h1>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('staff.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add New Staff
                </a>
                <a href="{{ route('staff.approvals.index') }}" class="btn btn-success">
                    <i class="fas fa-check me-1"></i> Approve Staff
                </a>
                
                {{-- Export Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-excel me-1"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                        <li><h6 class="dropdown-header">Export Options</h6></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('staff.export') }}">
                                <i class="fas fa-users me-2 text-primary"></i> All Staff
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">By Branch</h6></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('staff.export', ['branch' => 'Asokoro']) }}">
                                <i class="fas fa-building me-2 text-success"></i> Asokoro Branch
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('staff.export', ['branch' => 'Wuse']) }}">
                                <i class="fas fa-building me-2 text-purple"></i> Wuse Branch
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">By Status</h6></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('staff.export', ['status' => 'approved']) }}">
                                <i class="fas fa-check-circle me-2 text-success"></i> Approved Staff Only
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('staff.export', ['status' => 'rejected']) }}">
                                <i class="fas fa-times-circle me-2 text-danger"></i> Exited Staff Only
                            </a>
                        </li>
                        @if($branchFilter)
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('staff.export', ['branch' => $branchFilter]) }}">
                                <i class="fas fa-filter me-2 text-info"></i> Current Filter ({{ ucfirst($branchFilter) }})
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Main Stats Row --}}
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
                <button type="button" class="card-link w-100 border-0 bg-transparent p-0" data-bs-toggle="modal" data-bs-target="#onLeaveModal">
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
                                <small>View Details <i class="fas fa-arrow-right"></i></small>
                            </div>
                        </div>
                    </div>
                </button>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card bg-danger text-white shadow-sm h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="card-title mb-3">Exited Staff</h5>
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

        {{-- Branch Stats Row --}}
        <div class="mb-4 row">
            <div class="col-md-6 mb-4">
                <a href="{{ route('staff.index', ['branch' => 'Asokoro']) }}" class="card-link">
                    <div class="card border-0 shadow-sm h-100 hover-scale {{ $branchFilter === 'Asokoro' ? 'ring-active' : '' }}" style="background: linear-gradient(135deg, #1a472a 0%, #2d5a3f 100%);">
                        <div class="card-body p-4 d-flex flex-column text-white">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-uppercase mb-2" style="letter-spacing: 1px; opacity: 0.9;">
                                        <i class="fas fa-building me-1"></i> Asokoro Branch
                                    </h6>
                                    <h2 class="mb-0 fw-bold">{{ $asokoroStaffCount }}</h2>
                                    <small class="opacity-75">Approved Staff</small>
                                </div>
                                <div class="icon-circle" style="background-color: rgba(255,255,255,0.15);">
                                    <i class="fas fa-filter fa-lg text-white"></i>
                                </div>
                            </div>
                            <div class="mt-auto pt-2 text-end">
                                <small><i class="fas fa-mouse-pointer me-1"></i> Click to filter</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 mb-4">
                <a href="{{ route('staff.index', ['branch' => 'Wuse']) }}" class="card-link">
                    <div class="card border-0 shadow-sm h-100 hover-scale {{ $branchFilter === 'Wuse' ? 'ring-active' : '' }}" style="background: linear-gradient(135deg, #4a1a6b 0%, #6b2d8a 100%);">
                        <div class="card-body p-4 d-flex flex-column text-white">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-uppercase mb-2" style="letter-spacing: 1px; opacity: 0.9;">
                                        <i class="fas fa-building me-1"></i> Wuse Branch
                                    </h6>
                                    <h2 class="mb-0 fw-bold">{{ $wuseStaffCount }}</h2>
                                    <small class="opacity-75">Approved Staff</small>
                                </div>
                                <div class="icon-circle" style="background-color: rgba(255,255,255,0.15);">
                                    <i class="fas fa-filter fa-lg text-white"></i>
                                </div>
                            </div>
                            <div class="mt-auto pt-2 text-end">
                                <small><i class="fas fa-mouse-pointer me-1"></i> Click to filter</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Active Filter Indicator --}}
        @if($branchFilter)
            <div class="alert alert-info d-flex align-items-center justify-content-between mb-4" role="alert">
                <div>
                    <i class="fas fa-filter me-2"></i>
                    Showing staff from <strong>{{ ucfirst($branchFilter) }} Branch</strong>
                    <span class="badge bg-primary ms-2">{{ $employees->count() }} records</span>
                </div>
                <a href="{{ route('staff.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-times me-1"></i> Clear Filter
                </a>
            </div>
        @endif

        {{-- On Leave Modal --}}
        <div class="modal fade" id="onLeaveModal" tabindex="-1" aria-labelledby="onLeaveModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="onLeaveModalLabel">
                            <i class="fas fa-calendar-check me-2"></i>Staff Currently On Leave
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        @if($staffOnLeave->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Staff Name</th>
                                            <th>Department</th>
                                            <th>Branch</th>
                                            <th>Leave Type</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Days</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($staffOnLeave as $leave)
                                            <tr>
                                                <td>{{ Str::upper($leave->employee?->name ?? 'N/A') }}</td>
                                                <td>{{ $leave->employee?->department ?? 'N/A' }}</td>
                                                <td>{{ $leave->employee?->branch_name ?? 'N/A' }}</td>
                                                <td><span class="badge bg-info">{{ $leave->leave_type }}</span></td>
                                                <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                                                <td class="text-center fw-bold">{{ $leave->days_count ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                                <h5>No staff currently on leave</h5>
                                <p class="text-muted mb-0">All approved staff are active at work.</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('staff.leaves.admin.history') }}" class="btn btn-outline-primary">
                            <i class="fas fa-history me-1"></i> View Full Leave History
                        </a>
                        <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="staffTable" class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Name <i>(Surname first)</i></th>
                        <th>Department</th>
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
                            <td>{{ Str::upper($employee->department) }}</td>
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
                                    @else bg-dark @endif">
                                    @if($employee->status == 'rejected')
                                        Exited
                                    @else
                                        {{ ucfirst($employee->status) }}
                                    @endif
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
        /* Active filter ring for branch cards */
        .ring-active {
            box-shadow: 0 0 0 4px #fff, 0 0 0 6px #0d6efd !important;
            transform: scale(1.02);
        }
        .ring-active::after {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            top: 10px;
            right: 10px;
            background: #0d6efd;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        .card.ring-active {
            position: relative;
        }
        /* Custom purple color for Wuse branch */
        .text-purple {
            color: #6b2d8a !important;
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