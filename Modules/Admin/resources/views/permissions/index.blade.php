<!-- Modules/Admin/Resources/views/permissions/index.blade.php -->
@extends('admin::layouts.master')
@section('content')
    <h1>Manage Permissions</h1>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('admin.permissions.store') }}">
        @csrf
        <input type="text" name="name" placeholder="Permission Name" required>
        <button type="submit">Create Permission</button>
    </form>
    <ul>
        @foreach ($permissions as $permission)
            <li>{{ $permission->name }}</li>
        @endforeach
    </ul>
@endsection