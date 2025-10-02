@extends('layouts.master')

@section('page-content')
    <h1>Edit Store</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.stores.update', $store->id) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Store Name:</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $store->name) }}" required>
            @error('name')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="address">Address:</label>
            <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $store->address) }}">
            @error('address')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Update Store</button>
        <a href="{{ route('inventory.stores.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection