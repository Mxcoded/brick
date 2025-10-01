@extends('layouts.master')

@section('page-content')
<div class="container-fluid my-4">
    <h1>Complete Your Registration</h1>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('staff.complete-registration.submit') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="staff_code">Staff Code</label>
            <input type="text" name="staff_code" id="staff_code" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
@endsection