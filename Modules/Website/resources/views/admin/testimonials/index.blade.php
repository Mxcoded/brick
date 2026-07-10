@extends('layouts.master')

@section('title', 'Manage Testimonials')

@section('styles')
<style>
    .type-tab { border-radius: 50px; padding: 0.4rem 1.2rem; font-size: 0.85rem; font-weight: 500; border: 1px solid var(--theme-border); color: var(--theme-text-muted); text-decoration: none; transition: all 0.2s; background: transparent; }
    .type-tab:hover { border-color: var(--theme-primary); color: var(--theme-primary-dark); }
    .type-tab.active { background: var(--theme-primary); border-color: var(--theme-primary); color: #fff; }
</style>
@endsection

@section('page-content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3 rounded-top-4">
            <h1 class="h4 mb-0 fw-bold" style="color: var(--theme-heading);">
                <i class="fas fa-star me-2" style="color: var(--theme-primary);"></i>Testimonials
            </h1>
            <a href="{{ route('website.admin.testimonials.create') }}" class="btn btn-themed rounded-pill px-4">
                <i class="fas fa-plus me-1"></i> New Testimonial
            </a>
        </div>
        <div class="card-body p-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm d-flex align-items-center" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Type Tabs --}}
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="{{ route('website.admin.testimonials.index') }}" class="type-tab {{ $type === 'all' ? 'active' : '' }}">All</a>
                @foreach (\Modules\Website\Models\Testimonial::TYPES as $t)
                    <a href="{{ route('website.admin.testimonials.index', ['type' => $t]) }}"
                       class="type-tab {{ $type === $t ? 'active' : '' }}">
                        {{ ucfirst($t) }}
                    </a>
                @endforeach
            </div>

            @if ($testimonials->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-comment-slash fa-3x mb-3 opacity-25"></i>
                    <p class="text-muted mb-0">No testimonials found.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Guest</th>
                                <th>Review</th>
                                <th>Rating</th>
                                <th>Type</th>
                                <th>Context</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($testimonials as $testimonial)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if ($testimonial->guest_image)
                                                <img src="{{ $testimonial->guest_image }}" class="rounded-circle me-2" width="32" height="32" alt="">
                                            @else
                                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width:32px;height:32px;background:rgba(200,161,101,0.15);color:var(--theme-primary-dark);font-size:0.8rem;">{{ substr($testimonial->guest_name, 0, 1) }}</div>
                                            @endif
                                            <span class="fw-semibold small">{{ $testimonial->guest_name }}</span>
                                        </div>
                                    </td>
                                    <td><span class="small">{{ \Illuminate\Support\Str::limit($testimonial->text, 70) }}</span></td>
                                    <td class="text-nowrap">
                                        @for ($i = 0; $i < 5; $i++)
                                            <i class="fa{{ $i < $testimonial->rating ? 's' : 'r' }} fa-star text-warning" style="font-size:0.75rem"></i>
                                        @endfor
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill px-3"
                                              style="background: {{ $testimonial->type === 'restaurant' ? 'rgba(40,167,69,0.12)' : ($testimonial->type === 'event' ? 'rgba(0,123,255,0.12)' : 'rgba(200,161,101,0.12)') }};
                                                     color: {{ $testimonial->type === 'restaurant' ? '#28a745' : ($testimonial->type === 'event' ? '#007bff' : 'var(--theme-primary-dark)') }};">
                                            {{ $testimonial->typeLabel() }}
                                        </span>
                                    </td>
                                    <td><small class="text-muted">{{ $testimonial->contextLabel() }}</small></td>
                                    <td>
                                        @if ($testimonial->approved)
                                            <span class="badge bg-success rounded-pill px-3 py-2 fw-normal"><i class="fas fa-check-circle me-1"></i> Approved</span>
                                        @else
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-normal"><i class="fas fa-clock me-1"></i> Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('website.admin.testimonials.show', $testimonial) }}" class="btn btn-sm btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('website.admin.testimonials.toggle-approve', $testimonial) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $testimonial->approved ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $testimonial->approved ? 'Unapprove' : 'Approve' }}">
                                                    <i class="fas {{ $testimonial->approved ? 'fa-times-circle' : 'fa-check-circle' }}"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('website.admin.testimonials.edit', $testimonial) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('website.admin.testimonials.destroy', $testimonial) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this testimonial?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
