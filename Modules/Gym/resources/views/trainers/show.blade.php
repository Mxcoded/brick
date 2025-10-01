@extends('layouts.master')

@section('page-content')
    <div class="container-fluid my-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h2 class="mb-0">Trainer Details</h2>
                    </div>
                    <div class="card-body">
                        <p><strong>Full Name:</strong> {{ $trainer->full_name }}</p>
                        <p><strong>Email Address:</strong> {{ $trainer->email_address }}</p>
                        <p><strong>Phone Number:</strong> {{ $trainer->phone_number }}</p>
                        <p><strong>Specialization:</strong> {{ $trainer->specialization }}</p>
                        <a href="{{ route('gym.trainers.index') }}" class="btn btn-secondary">Back to List</a>
                        <a href="{{ route('gym.trainers.edit', $trainer->id) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('gym.trainers.delete', $trainer->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this trainer?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection