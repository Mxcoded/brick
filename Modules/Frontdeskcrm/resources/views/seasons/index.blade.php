@extends('layouts.master')

@section('title', 'Seasons')
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Seasons</h4>
        <a href="{{ route('frontdesk.seasons.create') }}" class="btn btn-gold">
            <i class="fas fa-plus me-2"></i>New Season
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Valid From</th>
                        <th>Valid To</th>
                        <th>Multiplier</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($seasons as $season)
                    <tr>
                        <td><strong>{{ $season->code }}</strong></td>
                        <td>{{ $season->name }}</td>
                        <td>{{ $season->valid_from->format('M d, Y') }}</td>
                        <td>{{ $season->valid_to->format('M d, Y') }}</td>
                        <td>{{ number_format($season->rate_multiplier, 4) }}×</td>
                        <td>
                            @if($season->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('frontdesk.seasons.edit', $season) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('frontdesk.seasons.destroy', $season) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete season {{ $season->code }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">No seasons defined.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $seasons->links() }}</div>
    </div>
</div>
@endsection
