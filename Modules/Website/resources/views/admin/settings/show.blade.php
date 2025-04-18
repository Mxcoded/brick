@extends('website::layouts.admin')

@section('title', 'View Setting')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-0">View Setting</h1>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Key</dt>
                <dd class="col-sm-9">{{ $setting->key }}</dd>
                <dt class="col-sm-3">Type</dt>
                <dd class="col-sm-9">{{ $setting->type }}</dd>
                <dt class="col-sm-3">Value</dt>
                <dd class="col-sm-9">
                    @if ($setting->type === 'image')
                        <img src="{{ Storage::url($setting->value) }}" alt="{{ $setting->key }}" width="200">
                    @elseif ($setting->type === 'video')
                        <video width="320" controls>
                            <source src="{{ Storage::url($setting->value) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    @else
                        {{ $setting->value }}
                    @endif
                </dd>
            </dl>
            <a href="{{ route('website.admin.settings.edit', $setting) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('website.admin.settings.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
@endsection