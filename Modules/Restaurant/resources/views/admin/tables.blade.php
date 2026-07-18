@extends('restaurant::layouts.adminMaster')
@section('title', 'Manage Tables')
@section('admin-content')
<div class="container-fluid py-3">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-light py-3 d-flex align-items-center">
                    <i class="bi bi-plus-circle me-2 fs-5"></i>
                    <h5 class="fw-bold mb-0">Add Table</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('restaurant.admin.tables.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="number" class="form-label fw-medium">Table Number / Name</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light"><i class="bi bi-grid-3x3-gap"></i></span>
                                <input type="text" name="number" id="number" class="form-control" placeholder="e.g. A1, VIP2, Patio3" required>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="capacity" class="form-label fw-medium">Capacity <span class="text-muted fw-normal">(optional)</span></label>
                                <input type="number" name="capacity" id="capacity" class="form-control form-control-lg" min="1" placeholder="e.g. 4">
                            </div>
                            <div class="col-md-6">
                                <label for="section" class="form-label fw-medium">Section <span class="text-muted fw-normal">(optional)</span></label>
                                <select name="section" id="section" class="form-select form-select-lg">
                                    <option value="">Select section</option>
                                    <option value="Window">Window</option>
                                    <option value="Center">Center</option>
                                    <option value="Patio">Patio</option>
                                    <option value="VIP">VIP</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn button btn-lg w-100 fw-bold">
                            <i class="bi bi-plus-circle me-1"></i> Add Table
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-light py-3 d-flex align-items-center">
                    <i class="bi bi-table me-2 fs-5"></i>
                    <h5 class="fw-bold mb-0">Tables</h5>
                    <span class="badge bg-primary rounded-pill ms-2">{{ $tables->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($tables->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-2">No tables yet. Add one above!</p>
                        </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Number</th>
                                    <th>Section</th>
                                    <th>Capacity</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tables as $table)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $table->id }}</td>
                                    <td>{{ $table->number }}</td>
                                    <td>
                                        @if($table->section)
                                            <span class="badge bg-dark rounded-pill">{{ $table->section }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $table->capacity ?? '—' }}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#editModal{{ $table->id }}">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $table->id }}">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modals --}}
@foreach ($tables as $table)
<div class="modal fade" id="editModal{{ $table->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('restaurant.admin.tables.update', $table->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="fw-bold mb-0"><i class="bi bi-pencil me-2"></i>Edit Table {{ $table->number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_number_{{ $table->id }}" class="form-label fw-medium">Table Number / Name</label>
                        <input type="text" name="number" id="edit_number_{{ $table->id }}" class="form-control form-control-lg" value="{{ $table->number }}" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="edit_capacity_{{ $table->id }}" class="form-label fw-medium">Capacity</label>
                            <input type="number" name="capacity" id="edit_capacity_{{ $table->id }}" class="form-control form-control-lg" min="1" value="{{ $table->capacity }}">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_section_{{ $table->id }}" class="form-label fw-medium">Section</label>
                            <select name="section" id="edit_section_{{ $table->id }}" class="form-select form-select-lg">
                                <option value="">None</option>
                                <option value="Window" {{ $table->section === 'Window' ? 'selected' : '' }}>Window</option>
                                <option value="Center" {{ $table->section === 'Center' ? 'selected' : '' }}>Center</option>
                                <option value="Patio" {{ $table->section === 'Patio' ? 'selected' : '' }}>Patio</option>
                                <option value="VIP" {{ $table->section === 'VIP' ? 'selected' : '' }}>VIP</option>
                                <option value="Other" {{ $table->section === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn button rounded-pill px-4 fw-bold"><i class="bi bi-check-lg me-1"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal{{ $table->id }}" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('restaurant.admin.tables.delete', $table->id) }}" method="POST">
                @csrf
                <div class="modal-body text-center p-4">
                    <i class="bi bi-exclamation-triangle text-danger fs-1 mb-3 d-block"></i>
                    <p class="fw-bold mb-1">Delete Table {{ $table->number }}?</p>
                    <p class="text-muted small mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4"><i class="bi bi-trash me-1"></i>Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection