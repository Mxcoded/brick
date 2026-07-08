@extends('layouts.master')

@section('title', 'Facilities Page Editor')

@section('page-content')
<style>
    .btn-gold {
        background-color: #C8A165;
        border-color: #C8A165;
        color: #fff;
    }
    .btn-gold:hover {
        background-color: #b08d55;
        border-color: #b08d55;
        color: #fff;
    }
    .btn-gold:active,
    .btn-gold:focus {
        background-color: #9e7a48 !important;
        border-color: #9e7a48 !important;
        box-shadow: 0 0 0 0.2rem rgba(200, 161, 101, 0.35) !important;
        color: #fff !important;
    }
    .btn-outline-gold {
        border-color: #C8A165;
        color: #C8A165;
    }
    .btn-outline-gold:hover {
        background-color: #C8A165;
        border-color: #C8A165;
        color: #fff;
    }
    .section-card {
        border-radius: 12px;
        overflow: hidden;
        transition: box-shadow 0.2s ease;
    }
    .section-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .section-card .card-header {
        border-bottom: 2px solid #C8A165;
        background: linear-gradient(135deg, #fefcf8 0%, #f8f4ee 100%);
    }
    .card-header .gold-accent {
        width: 4px;
        height: 24px;
        background: #C8A165;
        border-radius: 2px;
        display: inline-block;
        margin-right: 10px;
        vertical-align: middle;
    }
    .form-control:focus {
        border-color: #C8A165;
        box-shadow: 0 0 0 0.2rem rgba(200, 161, 101, 0.2);
    }
    .section-divider {
        border-top: 1px dashed #e8ddd0;
        margin: 1.5rem 0;
    }
    .gold-badge {
        background-color: #f7f0e6;
        color: #8b6f4a;
        font-size: 0.75rem;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        border: 1px solid #dcc9b0;
    }
    .btn-add {
        background-color: #C8A165;
        border-color: #C8A165;
        color: #fff;
        border-radius: 8px;
        font-size: 0.85rem;
        padding: 0.3rem 0.9rem;
    }
    .btn-add:hover {
        background-color: #b08d55;
        border-color: #b08d55;
        color: #fff;
    }
    .facility-card-img {
        width: 100%;
        height: 140px;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid #e8ddd0;
    }
</style>

<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom" style="border-color: #C8A165 !important;">
        <div>
            <h1 class="h3 mb-1 fw-bold" style="color: #3d3229;">Facilities Page Editor</h1>
            <p class="text-muted mb-0 small">Manage the public Facilities landing page content.</p>
        </div>
        <a href="{{ route('website.facilities') }}" target="_blank" class="btn btn-outline-gold px-3">
            <i class="fas fa-eye me-1"></i> Preview Page
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-left: 4px solid #C8A165 !important;">
            <i class="fas fa-check-circle me-2" style="color: #C8A165;"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm" style="border-left: 4px solid #dc3545 !important;">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ═══ HERO & INTRO ═══ --}}
    <div class="card shadow-sm border-0 mb-4 section-card">
        <div class="card-header bg-white py-3 d-flex align-items-center">
            <span class="gold-accent"></span>
            <i class="fas fa-heading me-2" style="color: #C8A165;"></i>
            <h5 class="mb-0 fw-bold flex-grow-1">Hero & Intro</h5>
            <span class="gold-badge">Header</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('website.admin.facilities.update-hero') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Title</label>
                        <input type="text" name="hero_title" class="form-control form-control-lg" value="{{ old('hero_title', $page->hero_title) }}" placeholder="e.g. Our Facilities">
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Subtitle</label>
                        <input type="text" name="hero_subtitle" class="form-control form-control-lg" value="{{ old('hero_subtitle', $page->hero_subtitle) }}" placeholder="e.g. Experience Luxury & Comfort">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-uppercase text-secondary">Hero Image</label>
                    @if($page->hero_image)
                        <div class="mb-2">
                            <img src="{{ $page->hero_image }}?t={{ time() }}" class="rounded" style="max-height: 160px; border: 2px solid #e8ddd0;">
                            <small class="d-block text-muted mt-1">Current image</small>
                        </div>
                    @endif
                    <input type="file" name="hero_image" class="form-control" accept="image/*">
                    <div class="form-text">Recommended 1920 × 800 px. Leave empty to keep current.</div>
                </div>

                <div class="section-divider"></div>
                <h6 class="fw-bold mb-3" style="color: #C8A165;">
                    <i class="fas fa-align-left me-1"></i> Introduction
                </h6>
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Intro Heading</label>
                        <input type="text" name="intro_heading" class="form-control" value="{{ old('intro_heading', $page->intro_heading) }}" placeholder="e.g. Amenities & Services">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-uppercase text-secondary">Intro Description</label>
                    <textarea name="intro_description" class="form-control" rows="3" placeholder="Describe your facilities...">{{ old('intro_description', $page->intro_description) }}</textarea>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-gold px-4">
                        <i class="fas fa-save me-1"></i> Save Hero & Intro
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ FACILITY ITEMS ═══ --}}
    <div class="card shadow-sm border-0 mb-4 section-card">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <span class="gold-accent"></span>
                <i class="fas fa-list me-2" style="color: #C8A165;"></i>
                <h5 class="mb-0 fw-bold">Facility Items</h5>
            </div>
            <button class="btn btn-add" data-bs-toggle="collapse" data-bs-target="#addItemForm">
                <i class="fas fa-plus me-1"></i> Add Facility
            </button>
        </div>
        <div class="card-body p-4">

            {{-- Add form --}}
            <div class="collapse mb-4" id="addItemForm">
                <form action="{{ route('website.admin.facilities.items.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card card-body bg-light border-0 rounded-3">
                        <h6 class="fw-bold mb-3" style="color: #C8A165;"><i class="fas fa-plus-circle me-1"></i> New Facility</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" required placeholder="e.g. Swimming Pool">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Icon (Font Awesome)</label>
                                <input type="text" name="icon" class="form-control" placeholder="fas fa-swimmer">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small fw-semibold">Sort</label>
                                <input type="number" name="sort_order" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-md-2 d-flex align-items-end gap-1">
                                <button type="submit" class="btn btn-sm btn-gold"><i class="fas fa-check me-1"></i> Add</button>
                                <button type="button" class="btn btn-sm btn-light" data-bs-toggle="collapse" data-bs-target="#addItemForm">Cancel</button>
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Brief description..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Features (one per line)</label>
                                <textarea name="features" class="form-control" rows="2" placeholder="24-hour security&#10;Free parking&#10;Air conditioning"></textarea>
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Link Text</label>
                                <input type="text" name="link_text" class="form-control" placeholder="e.g. View Details">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Link URL</label>
                                <input type="text" name="link_url" class="form-control" placeholder="e.g. /dining">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Items list --}}
            @if($page->items->count())
            <div class="row g-3">
                @foreach($page->items as $item)
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden">
                        @if($item->image)
                        <div style="height: 160px; overflow: hidden;">
                            <img src="{{ $item->image }}?t={{ time() }}" class="w-100 h-100" style="object-fit: cover;">
                        </div>
                        @endif
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <h6 class="fw-bold mb-0">{{ $item->title }}</h6>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-gold" data-bs-toggle="collapse" data-bs-target="#editItem{{ $item->id }}"><i class="fas fa-edit"></i></button>
                                    <form action="{{ route('website.admin.facilities.items.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this facility?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                            @if($item->description)
                            <p class="text-muted small mb-2">{{ Str::limit($item->description, 100) }}</p>
                            @endif
                            <div class="d-flex gap-2 align-items-center">
                                @if($item->icon)
                                <span class="badge bg-gold-soft text-dark"><i class="{{ $item->icon }} me-1"></i> {{ $item->icon }}</span>
                                @endif
                                @if($item->link_text)
                                <span class="badge bg-light text-muted border">{{ $item->link_text }}</span>
                                @endif
                                <span class="badge bg-secondary">#{{ $item->sort_order }}</span>
                            </div>
                        </div>

                        {{-- Edit inline --}}
                        <div class="collapse" id="editItem{{ $item->id }}">
                            <div class="card-footer bg-light p-3 border-top">
                                <form action="{{ route('website.admin.facilities.items.update', $item) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row g-2">
                                        <div class="col-md-6"><input type="text" name="title" class="form-control form-control-sm" value="{{ $item->title }}" required></div>
                                        <div class="col-md-6"><input type="text" name="slug" class="form-control form-control-sm" value="{{ $item->slug }}" placeholder="Auto-generated slug"></div>
                                        <div class="col-md-6"><input type="text" name="icon" class="form-control form-control-sm" value="{{ $item->icon }}" placeholder="Icon"></div>
                                        <div class="col-md-3"><input type="number" name="sort_order" class="form-control form-control-sm" value="{{ $item->sort_order }}" min="0" placeholder="Sort"></div>
                                        <div class="col-md-3">
                                            <div class="form-check form-switch mt-2">
                                                <input type="checkbox" name="is_active" class="form-check-input" role="switch" id="active{{ $item->id }}" value="1" @checked($item->is_active)>
                                                <label class="form-check-label small" for="active{{ $item->id }}">Active</label>
                                            </div>
                                        </div>
                                        <div class="col-12"><textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Short description (card)">{{ $item->description }}</textarea></div>
                                        <div class="col-12"><textarea name="content" class="form-control form-control-sm" rows="4" placeholder="Full detail page content (HTML allowed)">{{ $item->content }}</textarea></div>
                                        <div class="col-12"><textarea name="features" class="form-control form-control-sm" rows="3" placeholder="Features (one per line)">{{ $item->features ? implode("\n", $item->features) : '' }}</textarea></div>
                                        <div class="col-md-6"><input type="text" name="link_text" class="form-control form-control-sm" value="{{ $item->link_text }}" placeholder="Link text"></div>
                                        <div class="col-md-6"><input type="text" name="link_url" class="form-control form-control-sm" value="{{ $item->link_url }}" placeholder="Link URL"></div>
                                        <div class="col-12"><input type="file" name="image" class="form-control form-control-sm" accept="image/*"></div>
                                        <div class="col-12"><button class="btn btn-sm btn-gold w-100"><i class="fas fa-check me-1"></i> Update</button></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-cubes text-muted" style="font-size: 2.5rem; opacity: 0.3;"></i>
                <p class="text-muted mt-2 mb-0">No facility items yet. Click <strong>"Add Facility"</strong> above to add one.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
