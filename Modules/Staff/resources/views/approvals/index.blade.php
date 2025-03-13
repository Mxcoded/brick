@extends('staff::layouts.master')

@section('content')
<div class="container">
    <h1>Employee Approvals</h1>

    {{-- @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif --}}

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $employee)
                    <tr>
                        <td>{{ $employee->name }}</td>
                        <td>
                            <span class="badge 
                                @if($employee->status == 'approved') badge-success
                                @elseif($employee->status == 'rejected') badge-danger
                                @elseif($employee->status == 'pending') badge-warning
                                @else badge-secondary
                                @endif">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('staff.show', $employee->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                            @if($employee->status == 'pending')
                                <form action="{{ route('staff.approve', $employee->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <form action="{{ route('staff.reject', $employee->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection