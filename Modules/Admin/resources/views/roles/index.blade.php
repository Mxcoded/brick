@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Roles</li>
@endsection

@section('page-content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Manage Roles</h1>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.roles.store') }}" class="d-flex align-items-center gap-3">
                    @csrf
                    <div class="flex-grow-1">
                        <input type="text" name="name" class="form-control" placeholder="New Role Name (e.g. Supervisor)" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i> Create Role
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.roles.mass_destroy') }}" method="POST" id="bulkDeleteForm">
                    @csrf
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title m-0">Roles List</h5>
                        
                        {{-- Bulk Delete Button (Hidden by default or disabled) --}}
                        <button type="submit" 
                                class="btn btn-danger btn-sm" 
                                id="bulkDeleteBtn" 
                                style="display: none;"
                                onclick="return confirm('Are you sure you want to delete the selected roles? This action cannot be undone.')">
                            <i class="fas fa-trash me-1"></i> Delete Selected
                        </button>
                    </div>

                    <ul class="list-group">
                        {{-- Select All Row --}}
                        <li class="list-group-item bg-light">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label fw-bold" for="selectAll">Select All</label>
                            </div>
                        </li>

                        @foreach ($roles as $role)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    {{-- Individual Checkbox --}}
                                    <div class="form-check">
                                        <input class="form-check-input role-checkbox" 
                                               type="checkbox" 
                                               name="ids[]" 
                                               value="{{ $role->id }}" 
                                               id="role_{{ $role->id }}">
                                    </div>
                                    
                                    <label for="role_{{ $role->id }}" class="mb-0">
                                        {{ $role->name }}
                                        <span class="badge bg-secondary rounded-pill ms-2" style="font-size: 0.7em;">
                                            {{ $role->permissions->count() }} permissions
                                        </span>
                                    </label>
                                </div>

                                <div>
                                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-warning me-2">
                                        <i class="fas fa-edit"></i> Configure
                                    </a>
                                    
                                    {{-- Single Delete Form (Keep this for quick single deletes) --}}
                                    {{-- Note: We use a button form here outside the main form context or just rely on bulk --}}
                                    {{-- To avoid nesting forms, distinct delete buttons usually need their own forms. 
                                         However, since we wrapped the whole list, standard practice is to use the bulk form for everything 
                                         OR put the single delete forms *outside* the list item if possible. 
                                         
                                         Easier fix: Just use a link that submits a hidden form via JS, or keep the single delete button logic simple.
                                    --}}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </form>
            </div>
        </div>
    </div>

    {{-- Script to handle Select All and Button Visibility --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.role-checkbox');
            const deleteBtn = document.getElementById('bulkDeleteBtn');

            function toggleButton() {
                const anyChecked = Array.from(checkboxes).some(c => c.checked);
                deleteBtn.style.display = anyChecked ? 'block' : 'none';
            }

            selectAll.addEventListener('change', function() {
                checkboxes.forEach(c => c.checked = selectAll.checked);
                toggleButton();
            });

            checkboxes.forEach(c => {
                c.addEventListener('change', toggleButton);
            });
        });
    </script>
@endsection