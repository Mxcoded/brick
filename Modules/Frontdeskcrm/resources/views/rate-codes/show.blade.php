@extends('layouts.master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Rate Code: {{ $rateCode->name }} <code>({{ $rateCode->code }})</code></h4>
    <div>
        <a href="{{ route('frontdesk.rate-codes.edit', $rateCode) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('frontdesk.rate-codes.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h5>Details</h5></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8">{{ $rateCode->name }}</dd>
                    <dt class="col-sm-4">Code</dt>
                    <dd class="col-sm-8"><code>{{ $rateCode->code }}</code></dd>
                    <dt class="col-sm-4">Active</dt>
                    <dd class="col-sm-8">{!! $rateCode->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>' !!}</dd>
                    <dt class="col-sm-4">Sort</dt>
                    <dd class="col-sm-8">{{ $rateCode->sort_order }}</dd>
                    @if($rateCode->description)
                    <dt class="col-sm-4">Description</dt>
                    <dd class="col-sm-8">{{ $rateCode->description }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Base Prices</h5>
                <a href="{{ route('frontdesk.rate-calendar.index', ['rate_code_id' => $rateCode->id]) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-calendar-alt"></i> Rate Calendar
                </a>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Room Type</th>
                            <th class="text-end">Price (&#8358;)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rateCode->prices as $price)
                        <tr>
                            <td>{{ $price->roomType->name }}</td>
                            <td class="text-end">{{ number_format($price->price, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted">No prices configured.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
