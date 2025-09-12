@extends('layouts.master')

@section('page-content')
    <div class="container-fluid my-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h2 class="mb-0">Register a New Trainer</h2>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('gym.trainers.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="{{ old('full_name') }}" placeholder="Enter trainer's full name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email_address" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email_address" name="email_address" value="{{ old('email_address') }}" placeholder="Enter trainer's email" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" placeholder="Enter trainer's phone number" required>
                            </div>
                            <div class="mb-3">
                                <label for="specialization" class="form-label">Specialization</label>
                                <input type="text" class="form-control" id="specialization" name="specialization" value="{{ old('specialization') }}" placeholder="Enter trainer's specialization" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-1">Register Trainer</button>
                            <a href="{{ route('gym.trainers.index') }}" class="btn btn-danger w-100" title="Back to list">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection