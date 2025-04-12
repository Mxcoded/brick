@extends('website::layouts.guest')

@section('content')
    <h1>Manage Profile</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('website.guest.profile.update') }}">
        @csrf
        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $profile->phone) }}">
            @error('phone')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea name="address" id="address" class="form-control">{{ old('address', $profile->address) }}</textarea>
            @error('address')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="room_type" class="form-label">Preferred Room Type</label>
            <input type="text" name="preferences[room_type]" id="room_type" class="form-control" 
                   value="{{ old('preferences.room_type', $profile->preferences['room_type'] ?? '') }}">
            @error('preferences.room_type')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
@endsection