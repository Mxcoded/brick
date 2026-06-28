@extends('layouts.master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Rate Codes</h4>
    <a href="{{ route('frontdesk.rate-codes.create') }}" class="btn btn-primary">Add Rate Code</a>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card shadow-sm" style="background: var(--glass-effect); border: 1px solid var(--glass-border);">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Room Type Prices</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rateCodes as $rateCode)
                    <tr>
                        <td><code>{{ $rateCode->code }}</code></td>
                        <td>{{ $rateCode->name }}</td>
                        <td>{{ Str::limit($rateCode->description, 60) }}</td>
                        <td>
                            @foreach($rateCode->prices as $price)
                                <span class="badge bg-info me-1">{{ $price->roomType->name }}: &#8358;{{ number_format($price->price, 2) }}</span>
                            @endforeach
                        </td>
                        <td>{!! $rateCode->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>' !!}</td>
                        <td>
                            <a href="{{ route('frontdesk.rate-codes.show', $rateCode) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('frontdesk.rate-codes.edit', $rateCode) }}" class="btn btn-sm btn-warning">Edit</a>
                            <a href="{{ route('frontdesk.rate-calendar.index', ['rate_code_id' => $rateCode->id]) }}" class="btn btn-sm btn-secondary">Calendar</a>
                            <form action="{{ route('frontdesk.rate-codes.destroy', $rateCode) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete rate code {{ $rateCode->name }}? This cannot be undone.')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center">No rate codes yet. <a href="{{ route('frontdesk.rate-codes.create') }}">Create one</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $rateCodes->links() }}
    </div>
</div>
@endsection
