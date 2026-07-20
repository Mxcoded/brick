@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Audit Trails</li>
@endsection

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-charcoal mb-0"><i class="fas fa-clipboard-list me-2"></i>Field-Level Audit Trails</h3>
        <span class="text-muted small">{{ $audits->total() }} total entries</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search event, model, tags, user..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Event</label>
                    <select name="event" class="form-select">
                        <option value="">All Events</option>
                        @foreach ($events as $e)
                            <option value="{{ $e }}" {{ request('event') === $e ? 'selected' : '' }}>{{ ucfirst($e) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Model Type</label>
                    <select name="auditable_type" class="form-select">
                        <option value="">All Models</option>
                        @foreach ($auditableTypes as $t)
                            <option value="{{ $t }}" {{ request('auditable_type') === $t ? 'selected' : '' }}>{{ class_basename($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-gold w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="{{ route('admin.audit-trails.index') }}" class="btn btn-outline-danger"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- Audit Trails Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small text-muted">
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Event</th>
                            <th>Model</th>
                            <th>Changes</th>
                            <th>IP</th>
                            <th>URL</th>
                            <th>User Agent</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($audits as $audit)
                            @php
                                $color = match ($audit->event) {
                                    'created' => 'success',
                                    'updated' => 'primary',
                                    'deleted' => 'danger',
                                    'restored' => 'info',
                                    'role-assigned' => 'success',
                                    'role-detached' => 'warning',
                                    'attached' => 'success',
                                    'detached' => 'warning',
                                    'sync' => 'warning',
                                    default => 'dark',
                                };
                                $old = $audit->old_values ?? [];
                                $new = $audit->new_values ?? [];
                                $changedFields = array_keys(array_merge($old, $new));
                                $eventLabel = ucwords(str_replace(['_', '-'], ' ', $audit->event));
                            @endphp
                            <tr class="border-start border-3 border-{{ $color }}">
                                <td class="small text-nowrap">{{ $audit->created_at->format('M d, H:i') }}</td>
                                <td class="fw-semibold">
                                    @if($audit->user)
                                        <a href="{{ route('admin.audit-trails.index', ['user_id' => $audit->user->getKey()]) }}" class="text-decoration-none">{{ $audit->user->name }}</a>
                                    @else
                                        @php
                                            $guestTag = collect(explode(',', $audit->tags ?? ''))
                                                ->first(fn ($t) => str_starts_with($t, 'guest:'));
                                        @endphp
                                        @if($guestTag)
                                            <span class="text-muted" title="Guest booking (unregistered)"><i class="fas fa-user-clock me-1"></i>{{ Str::after($guestTag, 'guest:') }}</span>
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $color }} rounded-pill">{{ $eventLabel }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border" title="{{ $audit->auditable_type }}">
                                        {{ class_basename($audit->auditable_type) }} #{{ $audit->auditable_id }}
                                    </span>
                                </td>
                                <td style="min-width: 240px;">
                                    @if(empty($changedFields))
                                        <span class="text-muted small">—</span>
                                    @else
                                        <div class="small">
                                            @foreach($changedFields as $field)
                                                @if($field === 'updated_at' || $field === 'created_at') @continue @endif
                                                @php
                                                    $format = function ($val) use ($field) {
                                                        // Array-cast columns are captured as raw JSON strings; decode for display.
                                                        if (is_string($val)) {
                                                            $trimmed = trim($val);
                                                            if (in_array(substr($trimmed, 0, 1), ['[', '{'], true)) {
                                                                $decoded = json_decode($trimmed, true);
                                                                if (json_last_error() === JSON_ERROR_NONE) {
                                                                    $val = $decoded;
                                                                }
                                                            }
                                                        }
                                                        if (! is_array($val)) {
                                                            return $val ?? '—';
                                                        }
                                                        if ($field === 'roles') {
                                                            return collect($val)
                                                                ->map(fn ($r) => is_array($r) ? ($r['name'] ?? '') : $r)
                                                                ->filter()
                                                                ->implode(', ');
                                                        }
                                                        return json_encode($val);
                                                    };
                                                    $oldVal = $format($old[$field] ?? null);
                                                    $newVal = $format($new[$field] ?? null);
                                                @endphp
                                                <div class="mb-1">
                                                    <span class="fw-semibold text-capitalize">{{ str_replace('_', ' ', $field) }}:</span>
                                                    @if($audit->event === 'created')
                                                        <span class="text-success">{{ $newVal ?? '—' }}</span>
                                                    @elseif($audit->event === 'deleted')
                                                        <span class="text-danger">{{ $oldVal ?? '—' }}</span>
                                                    @elseif($oldVal !== $newVal)
                                                        <span class="text-danger text-decoration-line-through">{{ $oldVal ?? '—' }}</span>
                                                        <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                                        <span class="text-success">{{ $newVal ?? '—' }}</span>
                                                    @else
                                                        <span class="text-muted">{{ $newVal ?? '—' }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="small text-muted font-monospace">{{ $audit->ip_address }}</td>
                                <td class="small text-muted" title="{{ $audit->url }}">
                                    @if($audit->url)
                                        <span class="d-inline-block text-truncate" style="max-width: 200px;">{{ $audit->url }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="small text-muted" title="{{ $audit->user_agent }}">
                                    @if($audit->user_agent)
                                        <span class="d-inline-block text-truncate" style="max-width: 170px;">{{ Str::limit($audit->user_agent, 48) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(in_array($audit->event, ['updated', 'deleted']))
                                        <form method="POST" action="{{ route('admin.audit-trails.restore', $audit->id) }}" onsubmit="return confirm('Restore this record to its previous state?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-gold" title="Restore to previous state">
                                                <i class="fas fa-undo me-1"></i> Restore
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center py-5 text-muted">No audit trails found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $audits->withQueryString()->links() }}
        </div>
    </div>
@endsection

@section('styles')
<style>
    .bg-gold { background-color: #C8A165 !important; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #fff; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #fff; }
    .text-decoration-line-through { text-decoration: line-through; }
</style>
@endsection
