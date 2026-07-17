@extends('layouts.master')

@section('title', 'Meetings Page Editor')

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
    .section-card .card-header .section-icon {
        color: #C8A165;
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
    .form-check-input:focus {
        border-color: #C8A165;
        box-shadow: 0 0 0 0.2rem rgba(200, 161, 101, 0.25);
    }
    .form-check-input:checked {
        background-color: #C8A165;
        border-color: #C8A165;
    }
    .btn-sm-gold {
        background-color: #C8A165;
        border-color: #C8A165;
        color: #fff;
        padding: 0.25rem 0.6rem;
        font-size: 0.8rem;
        border-radius: 6px;
    }
    .btn-sm-gold:hover {
        background-color: #b08d55;
        border-color: #b08d55;
        color: #fff;
    }
    .table thead th {
        border-bottom: 2px solid #C8A165;
        color: #5a4a3a;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
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
    .card-header .btn-add {
        background-color: #C8A165;
        border-color: #C8A165;
        color: #fff;
        border-radius: 8px;
        font-size: 0.85rem;
        padding: 0.3rem 0.9rem;
    }
    .card-header .btn-add:hover {
        background-color: #b08d55;
        border-color: #b08d55;
        color: #fff;
    }
    img.room-thumb {
        border: 2px solid #e8ddd0;
        border-radius: 8px;
    }
    .bg-gold-soft {
        background-color: #fdfaf5;
    }
</style>

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom" style="border-color: #C8A165 !important;">
        <div>
            <h1 class="h3 mb-1 fw-bold" style="color: #3d3229;">Meetings Page Editor</h1>
            <p class="text-muted mb-0 small">Manage all content on the public Meetings landing page.</p>
        </div>
        <a href="{{ route('website.meetings') }}" target="_blank" class="btn btn-outline-gold px-3">
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

    {{-- ═══ HERO SECTION ═══ --}}
    <div class="card shadow-sm border-0 mb-4 section-card">
        <div class="card-header bg-white py-3 d-flex align-items-center">
            <span class="gold-accent"></span>
            <i class="fas fa-heading section-icon me-2"></i>
            <h5 class="mb-0 fw-bold flex-grow-1">Hero Section</h5>
            <span class="gold-badge">Header</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('website.admin.meeting.update-hero') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Title</label>
                        <input type="text" name="hero_title" class="form-control form-control-lg" value="{{ old('hero_title', $page->hero_title) }}" placeholder="e.g. Premier Meetings & Events">
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Subtitle</label>
                        <input type="text" name="hero_subtitle" class="form-control form-control-lg" value="{{ old('hero_subtitle', $page->hero_subtitle) }}" placeholder="e.g. Where business meets elegance">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-uppercase text-secondary">Description</label>
                    <textarea name="hero_description" class="form-control" rows="3" placeholder="Brief description of your meeting facilities...">{{ old('hero_description', $page->hero_description) }}</textarea>
                </div>

                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Hero Image</label>
                        @if($page->hero_image)
                            <div class="mb-2">
                                <img src="{{ $page->hero_image }}?t={{ time() }}" class="rounded room-thumb" style="max-height: 160px; width: auto;">
                                <small class="d-block text-muted mt-1">Current image</small>
                            </div>
                        @endif
                        <input type="file" name="hero_image" class="form-control" accept="image/*">
                        <div class="form-text">Recommended 1920 × 800 px. Leave empty to keep current.</div>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Brochure PDF</label>
                        @if($page->brochure_pdf)
                            <div class="mb-2">
                                <a href="{{ $page->brochure_pdf }}" target="_blank" class="btn btn-sm btn-outline-gold">
                                    <i class="fas fa-file-pdf me-1"></i> View Current Brochure
                                </a>
                            </div>
                        @endif
                        <input type="file" name="brochure_pdf" class="form-control" accept=".pdf">
                        <div class="form-text">Upload a PDF brochure (max 20 MB).</div>
                    </div>
                </div>

                <div class="section-divider"></div>
                <h6 class="fw-bold mb-3" style="color: #C8A165;">
                    <i class="fas fa-chart-simple me-1"></i> Stats Bar
                </h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Meeting Rooms</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-door-open text-muted"></i></span>
                            <input type="number" name="stats_meeting_rooms" class="form-control" value="{{ old('stats_meeting_rooms', $page->stats['meeting_rooms'] ?? '') }}" min="0" placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Total Sq. Metres</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-ruler-combined text-muted"></i></span>
                            <input type="number" name="stats_total_sqm" class="form-control" value="{{ old('stats_total_sqm', $page->stats['total_sqm'] ?? '') }}" min="0" placeholder="0">
                            <span class="input-group-text bg-light">m²</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Total Capacity</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-users text-muted"></i></span>
                            <input type="number" name="stats_total_capacity" class="form-control" value="{{ old('stats_total_capacity', $page->stats['total_capacity'] ?? '') }}" min="0" placeholder="0">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-gold px-4">
                        <i class="fas fa-save me-1"></i> Save Hero Section
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ EQUIPMENT & CATERING ═══ --}}
    <div class="card shadow-sm border-0 mb-4 section-card">
        <div class="card-header bg-white py-3 d-flex align-items-center">
            <span class="gold-accent"></span>
            <i class="fas fa-cogs section-icon me-2"></i>
            <h5 class="mb-0 fw-bold flex-grow-1">Equipment & Catering</h5>
            <span class="gold-badge">Services</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('website.admin.meeting.update-equipment') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <h6 class="fw-bold mb-3" style="color: #C8A165;">
                    <i class="fas fa-wrench me-1"></i> Equipment & Services
                </h6>
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Section Heading</label>
                        <input type="text" name="equipment_heading" class="form-control" value="{{ old('equipment_heading', $page->equipment_heading) }}" placeholder="e.g. Everything you need">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-uppercase text-secondary">Equipment Items</label>
                    <textarea name="equipment_items" class="form-control" rows="5" placeholder="One item per line — e.g.&#10;Projector & Screen&#10;High-speed WiFi&#10;Video Conferencing">{{ old('equipment_items', is_array($page->equipment_items) ? implode("\n", $page->equipment_items) : '') }}</textarea>
                    <div class="form-text">Enter each equipment or service item on a new line.</div>
                </div>

                <div class="section-divider"></div>
                <h6 class="fw-bold mb-3" style="color: #C8A165;">
                    <i class="fas fa-utensils me-1"></i> Event Catering
                </h6>
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Catering Heading</label>
                        <input type="text" name="catering_heading" class="form-control" value="{{ old('catering_heading', $page->catering_heading) }}" placeholder="e.g. Exceptional Catering">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-uppercase text-secondary">Catering Description</label>
                    <textarea name="catering_description" class="form-control" rows="4" placeholder="Describe the catering options...">{{ old('catering_description', $page->catering_description) }}</textarea>
                </div>

                <div class="row">
                    @for ($i = 1; $i <= 3; $i++)
                    @php $field = 'catering_image_'.$i; @endphp
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Catering Image {{ $i }}</label>
                        @if($page->$field)
                            <div class="mb-2">
                                <img src="{{ $page->$field }}?t={{ time() }}" class="rounded room-thumb" style="max-height: 110px; width: 100%; object-fit: cover;">
                            </div>
                        @endif
                        <input type="file" name="{{ $field }}" class="form-control form-control-sm" accept="image/*">
                    </div>
                    @endfor
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-gold px-4">
                        <i class="fas fa-save me-1"></i> Save Equipment & Catering
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ MEETING ROOMS ═══ --}}
    <div class="card shadow-sm border-0 mb-4 section-card">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <span class="gold-accent"></span>
                <i class="fas fa-chair section-icon me-2"></i>
                <h5 class="mb-0 fw-bold">Meeting Rooms / Seating Capacity</h5>
            </div>
            <button class="btn btn-add" data-bs-toggle="collapse" data-bs-target="#addRoomForm">
                <i class="fas fa-plus me-1"></i> Add Room
            </button>
        </div>
        <div class="card-body p-4">

            {{-- Add room form --}}
            <div class="collapse mb-4" id="addRoomForm">
                <form action="{{ route('website.admin.meeting.rooms.store') }}" method="POST">
                    @csrf
                    <div class="card card-body bg-gold-soft border-0 rounded-3">
                        <h6 class="fw-bold mb-3" style="color: #C8A165;"><i class="fas fa-plus-circle me-1"></i> New Meeting Room</h6>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Room Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="e.g. Ballroom A">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small fw-semibold">Sq. m</label>
                                <input type="number" name="size_sqm" class="form-control" step="0.01" min="0" placeholder="0">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small fw-semibold">Boardroom</label>
                                <input type="number" name="boardroom" class="form-control" min="0" placeholder="0">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small fw-semibold">Classroom</label>
                                <input type="number" name="classroom" class="form-control" min="0" placeholder="0">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small fw-semibold">Theatre</label>
                                <input type="number" name="theatre" class="form-control" min="0" placeholder="0">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small fw-semibold">Cocktail</label>
                                <input type="number" name="cocktail" class="form-control" min="0" placeholder="0">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small fw-semibold">Banquet</label>
                                <input type="number" name="banquet" class="form-control" min="0" placeholder="0">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small fw-semibold">Cabaret</label>
                                <input type="number" name="cabaret" class="form-control" min="0" placeholder="0">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small fw-semibold">U-shape</label>
                                <input type="number" name="ushape" class="form-control" min="0" placeholder="0">
                            </div>
                            <div class="col-md-2 d-flex gap-1">
                                <button type="submit" class="btn btn-sm-gold"><i class="fas fa-check me-1"></i> Add</button>
                                <button type="button" class="btn btn-sm btn-light" data-bs-toggle="collapse" data-bs-target="#addRoomForm">Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Rooms table --}}
            @if($page->rooms->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 120px;">Room Name</th>
                            <th>Sq. m</th>
                            <th>Boardroom</th>
                            <th>Classroom</th>
                            <th>Theatre</th>
                            <th>Cocktail</th>
                            <th>Banquet</th>
                            <th>Cabaret</th>
                            <th>U-shape</th>
                            <th>Double U</th>
                            <th>Triple U</th>
                            <th class="text-end" style="min-width: 90px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($page->rooms as $room)
                        <tr>
                            <td class="fw-semibold">{{ $room->name }}</td>
                            <td>{{ $room->size_sqm ? $room->size_sqm : '-' }}</td>
                            <td>{{ $room->boardroom ?? '-' }}</td>
                            <td>{{ $room->classroom ?? '-' }}</td>
                            <td>{{ $room->theatre ?? '-' }}</td>
                            <td>{{ $room->cocktail ?? '-' }}</td>
                            <td>{{ $room->banquet ?? '-' }}</td>
                            <td>{{ $room->cabaret ?? '-' }}</td>
                            <td>{{ $room->ushape ?? '-' }}</td>
                            <td>{{ $room->double_u ?? '-' }}</td>
                            <td>{{ $room->triple_u ?? '-' }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-gold me-1" data-bs-toggle="collapse" data-bs-target="#editRoom{{ $room->id }}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('website.admin.meeting.rooms.destroy', $room) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this room?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="editRoom{{ $room->id }}">
                            <td colspan="12" class="bg-gold-soft p-3">
                                <form action="{{ route('website.admin.meeting.rooms.update', $room) }}" method="POST">
                                    @csrf
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-2"><input type="text" name="name" class="form-control form-control-sm" value="{{ $room->name }}" required></div>
                                        <div class="col-md-1"><input type="number" name="size_sqm" class="form-control form-control-sm" step="0.01" min="0" value="{{ $room->size_sqm }}"></div>
                                        <div class="col-md-1"><input type="number" name="boardroom" class="form-control form-control-sm" min="0" value="{{ $room->boardroom }}"></div>
                                        <div class="col-md-1"><input type="number" name="classroom" class="form-control form-control-sm" min="0" value="{{ $room->classroom }}"></div>
                                        <div class="col-md-1"><input type="number" name="theatre" class="form-control form-control-sm" min="0" value="{{ $room->theatre }}"></div>
                                        <div class="col-md-1"><input type="number" name="cocktail" class="form-control form-control-sm" min="0" value="{{ $room->cocktail }}"></div>
                                        <div class="col-md-1"><input type="number" name="banquet" class="form-control form-control-sm" min="0" value="{{ $room->banquet }}"></div>
                                        <div class="col-md-1"><input type="number" name="cabaret" class="form-control form-control-sm" min="0" value="{{ $room->cabaret }}"></div>
                                        <div class="col-md-1"><input type="number" name="ushape" class="form-control form-control-sm" min="0" value="{{ $room->ushape }}"></div>
                                        <div class="col-md-1"><input type="number" name="double_u" class="form-control form-control-sm" min="0" value="{{ $room->double_u }}"></div>
                                        <div class="col-md-1"><input type="number" name="triple_u" class="form-control form-control-sm" min="0" value="{{ $room->triple_u }}"></div>
                                        <div class="col-md-1">
                                            <button class="btn btn-sm-gold w-100"><i class="fas fa-check"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-chair text-muted" style="font-size: 2.5rem; opacity: 0.3;"></i>
                <p class="text-muted mt-2 mb-0">No meeting rooms yet. Click <strong>"Add Room"</strong> above to add one.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ═══ GALLERY ═══ --}}
    <div class="card shadow-sm border-0 mb-4 section-card">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <span class="gold-accent"></span>
                <i class="fas fa-images section-icon me-2"></i>
                <h5 class="mb-0 fw-bold">Gallery</h5>
            </div>
            <button class="btn btn-add" data-bs-toggle="collapse" data-bs-target="#addGalleryForm">
                <i class="fas fa-plus me-1"></i> Add Image
            </button>
        </div>
        <div class="card-body p-4">

            {{-- Add image form --}}
            <div class="collapse mb-4" id="addGalleryForm">
                <form action="{{ route('website.admin.meeting.gallery.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card card-body bg-gold-soft border-0 rounded-3">
                        <h6 class="fw-bold mb-3" style="color: #C8A165;"><i class="fas fa-upload me-1"></i> New Gallery Image</h6>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Image File <span class="text-danger">*</span></label>
                                <input type="file" name="image" class="form-control" accept="image/*" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Alt Text</label>
                                <input type="text" name="alt_text" class="form-control" placeholder="Brief description">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm-gold w-100">
                                    <i class="fas fa-upload me-1"></i> Upload
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Gallery grid --}}
            @if($page->gallery->count())
            <div class="row g-3">
                @foreach($page->gallery as $item)
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden">
                        <div style="height: 170px; overflow: hidden; position: relative;">
                            <img src="{{ $item->image }}?t={{ time() }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $item->alt_text ?? '' }}">
                        </div>
                        <div class="card-body p-2 text-center d-flex flex-column justify-content-between">
                            <small class="text-muted text-truncate d-block">{{ $item->alt_text ?? 'No alt text' }}</small>
                            <form action="{{ route('website.admin.meeting.gallery.destroy', $item) }}" method="POST" class="mt-1" onsubmit="return confirm('Delete this image?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger w-100"><i class="fas fa-trash me-1"></i> Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-image text-muted" style="font-size: 2.5rem; opacity: 0.3;"></i>
                <p class="text-muted mt-2 mb-0">No gallery images yet. Click <strong>"Add Image"</strong> above to upload one.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ═══ CONTACT & SEO ═══ --}}
    <div class="card shadow-sm border-0 mb-4 section-card">
        <div class="card-header bg-white py-3 d-flex align-items-center">
            <span class="gold-accent"></span>
            <i class="fas fa-address-card section-icon me-2"></i>
            <h5 class="mb-0 fw-bold flex-grow-1">Contact & SEO</h5>
            <span class="gold-badge">Visibility</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('website.admin.meeting.update-contact') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Contact Phone</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-phone text-muted"></i></span>
                            <input type="tel" name="contact_phone" class="form-control phone-input" value="{{ old('contact_phone', $page->contact_phone) }}" placeholder="+234 800 000 0000">
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Contact Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $page->contact_email) }}" placeholder="meetings@example.com">
                        </div>
                    </div>
                </div>

                <div class="section-divider"></div>
                <h6 class="fw-bold mb-3" style="color: #C8A165;">
                    <i class="fas fa-search me-1"></i> SEO Settings
                </h6>
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">SEO Title</label>
                        <input type="text" name="seo_title" class="form-control" value="{{ old('seo_title', $page->seo_title) }}" placeholder="Page title for search engines">
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Page Status</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="is_published" value="1" class="form-check-input" id="is_published" {{ old('is_published', $page->is_published) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_published" style="color: #5a4a3a;">Page is live on website</label>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-uppercase text-secondary">SEO Description</label>
                    <textarea name="seo_description" class="form-control" rows="2" placeholder="Brief description for search engine results...">{{ old('seo_description', $page->seo_description) }}</textarea>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-gold px-4">
                        <i class="fas fa-save me-1"></i> Save Contact & SEO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
