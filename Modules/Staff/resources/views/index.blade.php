@extends('staff::layouts.master')

@section('content')
    <div class="container">
        <h1>Staff Management</h1>
        <a href="{{ route('staff.create') }}" class="mb-3 btn btn-primary">Add New Staff</a>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <!-- Status Summary Cards -->
        <div class="mb-4 row">
            <div class="col-md-4">
                <div class="p-3 text-center text-white card bg-secondary">
                    <h5>Total Registered Staff (Draft)</h5>
                    <h3>{{ $employees->where('status', 'draft')->count() }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 text-center text-white card bg-danger">
                    <h5>Rejected</h5>
                    <h3>{{ $employees->where('status', 'rejected')->count() }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 text-center text-white card bg-success">
                    <h5>Approved</h5>
                    <h3>{{ $employees->where('status', 'approved')->count() }}</h3>
                </div>
            </div>
        </div>

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
                                    alt="{{ $employee->name }}'s Profile Photo" class="staff-profile-image" width="100"
                                    loading="lazy">
                            @else
                                <div class="no-photo-placeholder">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                            @endif
                        </td>
                        <td>{{ $employee->staff_code }}</td>
                        <td>
                            <span
                                class="badge
                            @if ($employee->status == 'approved') bg-success
                            @elseif($employee->status == 'rejected') bg-danger
                            @elseif($employee->status == 'pending') bg-warning
                            @else bg-secondary @endif">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="gap-2 d-flex">
                                <a href="{{ route('staff.show', $employee->id) }}" class="btn btn-sm btn-primary"
                                    title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('staff.edit', $employee->id) }}" class="btn btn-sm btn-warning"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                {{-- <form action="{{ route('staff.destroy', $employee->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this staff member?')"
                                        title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form> --}}
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
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

@section('scripts')
    <!-- Required Libraries -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#staffTable').DataTable({
                responsive: true,
                columnDefs: [{
                        orderable: false,
                        targets: [4, 7]
                    }, // Disable sorting for photo and actions columns
                    {
                        searchable: false,
                        targets: [4, 7]
                    } // Disable search for these columns
                ]
            });
        });
    </script>
@endsection
