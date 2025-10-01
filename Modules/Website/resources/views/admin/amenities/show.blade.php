@extends('website::layouts.admin')

@section('title', 'View Amenity')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-0">View Amenity</h1>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{ $amenity->name }}</dd>
                <dt class="col-sm-3">Icon</dt>
                <dd class="col-sm-9"><i class="{{ $amenity->icon ?? 'fas fa-question' }}"></i> {{ $amenity->icon ?? 'None' }}</dd>
                <dt class="col-sm-3">Associated Rooms</dt>
                <dd class="col-sm-9">
                    @if ($amenity->rooms->isEmpty())
                        None
                    @else
                        <ul>
                            @foreach ($amenity->rooms as $room)
                                <li>{{ $room->name }}</li>
                            @endforeach
                        </ul>
                    @endif
                </dd>
            </dl>
            <a href="{{ route('website.admin.amenities.edit', $amenity) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('website.admin.amenities.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
@endsection