@extends('website::layouts.admin')

@section('title', 'Manage Settings')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Manage Settings</h1>
            <a href="{{ route('website.admin.settings.create') }}" class="btn btn-primary">Add New Setting</a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($settings->isEmpty())
                <p>No settings found.</p>
            @else
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Key</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($settings as $setting)
                            <tr>
                                <td>{{ $setting->key }}</td>
                                <td>{{ $setting->type }}</td>
                                <td>
                                    @if ($setting->type === 'image')
                                        <img src="{{ Storage::url($setting->value) }}" alt="{{ $setting->key }}" width="50">
                                    @elseif ($setting->type === 'video')
                                        <a href="{{ Storage::url($setting->value) }}" target="_blank">View Video</a>
                                    @else
                                        {{ Str::limit($setting->value, 50) }}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('website.admin.settings.show', $setting) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('website.admin.settings.edit', $setting) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('website.admin.settings.destroy', $setting) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection