@extends('gym::layouts.master')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <a href="{{ route('gym.index') }}" class="btn btn-primary btn-sm mb-1">Show All Members</a>
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Trainers</h2>
                        <a href="{{ route('gym.trainers.create') }}" class="btn btn-light btn-sm"><span class="fas fa-plus"></span> New Trainer</a>
                        
                    </div>
                    <div class="card-body">
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
                        @if ($trainers->isEmpty())
                            <p>No trainers registered yet.</p>
                        @else
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Email Address</th>
                                        <th>Phone Number</th>
                                        <th>Specialization</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($trainers as $trainer)
                                        <tr>
                                            <td>{{ $trainer->full_name }}</td>
                                            <td>{{ $trainer->email_address }}</td>
                                            <td>{{ $trainer->phone_number }}</td>
                                            <td>{{ $trainer->specialization }}</td>
                                            <td>
                                                <a href="{{ route('gym.trainers.show', $trainer->id) }}" class="btn btn-primary btn-sm">View Details</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection