<!-- Modules/Admin/Resources/views/roles/index.blade.php -->
@extends('admin::layouts.master')
@section('content')
    <h1>Manage Roles</h1>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('admin.roles.store') }}">
        @csrf
        <input type="text" name="name" placeholder="Role Name" required>
        <button type="submit">Create Role</button>
    </form>
    <ul>
        @foreach ($roles as $role)
            <li>{{ $role->name }}</li>
        @endforeach
    </ul>
@endsection