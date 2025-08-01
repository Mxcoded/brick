@extends('restaurant::layouts.master')
@section('title', 'Welcome')
@section('content')
    <div class="landing-container">
        <div class="card text-center">
            <h1>Welcome to Taste Restaurant</h1>
            <p class="lead">Select your table to view our delicious menu!</p>
            <form action="{{ route('restaurant.select-table') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="table_id" class="form-label">Table Number</label>
                    <select name="table_id" id="table_id" class="form-select" required>
                        <option value="">Choose a table</option>
                        @foreach ($tables as $table)
                            <option value="{{ $table->id }}">{{ $table->number }}</option>
                        @endforeach
                    </select>
                    @error('table_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary btn-lg">View Menu</button>
            </form>
        </div>
    </div>
   @endsection