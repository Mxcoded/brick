@extends('layouts.master')

@section('title', 'Chart of Accounts')

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Chart of Accounts</h2>
        @can('finance.manage_coa')
            <a href="{{ route('finance.coa.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Account
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger py-2">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>GL Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Normal Bal.</th>
                            <th>Active</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accounts as $account)
                            <tr>
                                <td class="font-monospace">{{ $account->code }}</td>
                                <td>{{ $account->name }}</td>
                                <td>
                                    <span class="badge bg-info text-dark text-capitalize">{{ $account->type }}</span>
                                </td>
                                <td class="text-capitalize">{{ $account->normal_balance }}</td>
                                <td>
                                    @if ($account->active)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @can('finance.manage_coa')
                                        <a href="{{ route('finance.coa.edit', $account) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <form action="{{ route('finance.coa.destroy', $account) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this account?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No accounts found. Run the Finance seeder to create the default chart.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($accounts->hasPages())
            <div class="card-footer bg-transparent">
                {{ $accounts->links() }}
            </div>
        @endif
    </div>
@endsection
