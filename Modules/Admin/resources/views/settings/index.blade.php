@extends('layouts.master')

@section('title', 'Property Settings')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Settings</li>
@endsection

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-charcoal"><i class="fas fa-cog me-2"></i>Property Settings</h4>
            <p class="text-muted mb-0">
                <i class="fas fa-building me-1"></i> {{ $propertyName }}
                @if($isViewingAll)
                    <span class="badge bg-warning text-dark ms-2">Viewing All — settings are per-property</span>
                @endif
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @if($isViewingAll)
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            You are viewing all properties. Please select a specific property to manage its settings.
        </div>
    @endif

    <div class="row">
        {{-- Tab Navigation --}}
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-2">
                    <div class="list-group list-group-flush">
                        @foreach($settingGroups as $groupKey => $group)
                            <a href="{{ route('admin.settings.index', ['group' => $groupKey]) }}"
                               class="list-group-item list-group-item-action d-flex align-items-center {{ $currentGroup === $groupKey ? 'active' : '' }}">
                                <i class="{{ $group['icon'] }} fa-fw me-2"></i>
                                {{ $group['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Settings Form --}}
        <div class="col-md-9">
            @if(isset($settingGroups[$currentGroup]))
                @php $group = $settingGroups[$currentGroup]; @endphp
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold text-charcoal">
                            <i class="{{ $group['icon'] }} me-2"></i>{{ $group['label'] }} Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.settings.update', $currentGroup) }}">
                            @csrf
                            @method('PUT')

                            @foreach($group['settings'] as $setting)
                                <div class="mb-4">
                                    <label for="{{ $setting['key'] }}" class="form-label fw-semibold">
                                        {{ $setting['label'] }}
                                    </label>

                                    @if($setting['type'] === 'text' || $setting['type'] === 'email')
                                        <input type="{{ $setting['type'] }}"
                                               id="{{ $setting['key'] }}"
                                               name="{{ $setting['key'] }}"
                                               class="form-control @error($setting['key']) is-invalid @enderror"
                                               value="{{ old($setting['key'], $settings[$setting['key']] ?? $setting['default']) }}"
                                               placeholder="{{ $setting['label'] }}">
                                        @error($setting['key'])
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                    @elseif($setting['type'] === 'number')
                                        <input type="number"
                                               id="{{ $setting['key'] }}"
                                               name="{{ $setting['key'] }}"
                                               class="form-control @error($setting['key']) is-invalid @enderror"
                                               value="{{ old($setting['key'], $settings[$setting['key']] ?? $setting['default']) }}"
                                               step="0.01"
                                               min="0">
                                        @error($setting['key'])
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                    @elseif($setting['type'] === 'time')
                                        <input type="time"
                                               id="{{ $setting['key'] }}"
                                               name="{{ $setting['key'] }}"
                                               class="form-control @error($setting['key']) is-invalid @enderror"
                                               value="{{ old($setting['key'], $settings[$setting['key']] ?? $setting['default']) }}">
                                        @error($setting['key'])
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                    @elseif($setting['type'] === 'textarea')
                                        <textarea id="{{ $setting['key'] }}"
                                                  name="{{ $setting['key'] }}"
                                                  class="form-control @error($setting['key']) is-invalid @enderror"
                                                  rows="3"
                                                  placeholder="{{ $setting['label'] }}">{{ old($setting['key'], $settings[$setting['key']] ?? $setting['default']) }}</textarea>
                                        @error($setting['key'])
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                    @elseif($setting['type'] === 'toggle')
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="{{ $setting['key'] }}" value="0">
                                            <input type="checkbox"
                                                   id="{{ $setting['key'] }}"
                                                   name="{{ $setting['key'] }}"
                                                   class="form-check-input"
                                                   value="1"
                                                   {{ ($settings[$setting['key']] ?? $setting['default']) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $setting['key'] }}">
                                                {{ ($settings[$setting['key']] ?? $setting['default']) ? 'Enabled' : 'Disabled' }}
                                            </label>
                                        </div>

                                    @elseif($setting['type'] === 'select')
                                        <select id="{{ $setting['key'] }}"
                                                name="{{ $setting['key'] }}"
                                                class="form-select @error($setting['key']) is-invalid @enderror">
                                            @foreach($setting['options'] as $optionValue => $optionLabel)
                                                <option value="{{ $optionValue }}"
                                                    {{ ($settings[$setting['key']] ?? $setting['default']) === $optionValue ? 'selected' : '' }}>
                                                    {{ $optionLabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error($setting['key'])
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                    @endif

                                    @if(isset($setting['help']))
                                        <div class="form-text">{{ $setting['help'] }}</div>
                                    @endif
                                </div>
                            @endforeach

                            <div class="border-top pt-3 mt-2">
                                <button type="submit" class="btn btn-gold">
                                    <i class="fas fa-save me-1"></i> Save {{ $group['label'] }} Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="fas fa-cog fa-3x mb-3"></i>
                        <p>Select a settings group from the left panel.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('styles')
<style>
    .bg-gold { background-color: #C8A165 !important; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #fff; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #fff; }
    .list-group-item.active { background-color: #C8A165; border-color: #C8A165; }
    .form-check-input:checked { background-color: #C8A165; border-color: #C8A165; }
</style>
@endsection
