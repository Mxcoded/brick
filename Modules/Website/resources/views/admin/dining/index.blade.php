@extends('layouts.master')

@section('title', 'Dining Management')

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 fw-bold">On-site Restaurant</h1>
        <a href="{{ route('website.admin.dining.create') }}" class="btn btn-gold">
            <i class="fas fa-plus me-1"></i> Add Dining Option
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Cuisine</th>
                            <th>Menu</th>
                            <th>Featured</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($diningOptions as $dining)
                        <tr>
                            <td>
                                @if($dining->image_url)
                                    <img src="{{ $dining->image_url }}?t={{ time() }}" class="rounded" style="width: 60px; height: 40px; object-fit: cover;">
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $dining->name }}</td>
                            <td>{{ $dining->cuisine_type ?? '—' }}</td>
                            <td>
                                @if($dining->menu_pdf)
                                    <span class="badge bg-success"><i class="fas fa-file-pdf me-1"></i>PDF</span>
                                @endif
                                @if($dining->menu_link)
                                    <span class="badge bg-info"><i class="fas fa-link me-1"></i>Link</span>
                                @endif
                                @if(!$dining->menu_pdf && !$dining->menu_link)
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @if($dining->is_featured)
                                    <span class="badge" style="background: #C8A165;">Featured</span>
                                @else
                                    <span class="text-muted small">No</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('website.admin.dining.edit', $dining->id) }}" class="btn btn-sm btn-outline-gold me-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('website.admin.dining.qr', $dining->id) }}" class="btn btn-sm btn-outline-dark me-1" title="QR Code">
                                    <i class="fas fa-qrcode"></i>
                                </a>
                                <form action="{{ route('website.admin.dining.destroy', $dining->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this dining option?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No dining options yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($diningOptions->hasPages())
        <div class="card-footer bg-white">
            {{ $diningOptions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
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
    .btn-outline-gold {
        border-color: #C8A165;
        color: #C8A165;
    }
    .btn-outline-gold:hover {
        background-color: #C8A165;
        border-color: #C8A165;
        color: #fff;
    }
</style>
@endpush
