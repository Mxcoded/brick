<!-- Modules/Admin/Resources/views/employees/create-user.blade.php -->
@extends('admin::layouts.master')
@section('content')
    <h1>Create User Account for Employee</h1>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.employees.store-user') }}">
        @csrf
        <div>
            <label for="employee_id">Select Employee:</label>
            <select name="employee_id" id="employee_id" required>
                <option value="">-- Select Employee --</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->position ?? 'No Position' }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div>
            <label for="password_confirmation">Confirm Password:</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required>
        </div>
        <div>
            <label for="role">Assign Role:</label>
            <select name="role" id="role" required>
                <option value="">-- Select Role --</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit">Create User</button>
    </form>
    <a href="{{ route('admin.users.index') }}">Back to Users</a>
@endsection