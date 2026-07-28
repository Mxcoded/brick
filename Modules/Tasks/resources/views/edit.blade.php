@extends('layouts.master')

@section('title', 'Edit Task')
@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tasks.show', $task->id) }}">Tasks</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Task</li>
@endsection

@section('styles')
<style>
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #fff; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #fff; }
    .form-check-label { cursor: pointer; }
    .recurrence-section { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 16px; }
    .recurrence-section.disabled { opacity: 0.5; pointer-events: none; }
</style>
@endsection

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-charcoal mb-0"><i class="fas fa-edit me-2"></i>Edit Task</h3>
        <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-outline-dark btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="4" required>{{ old('description', $task->description) }}</textarea>
                    @error('description')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="priority" class="form-label">Priority</label>
                        <select name="priority" id="priority" class="form-select" required>
                            <option value="high" {{ $task->priority === 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ $task->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ $task->priority === 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="deadline" class="form-label">Deadline</label>
                        <input type="date" name="deadline" id="deadline" class="form-control" value="{{ old('deadline', $task->deadline->format('Y-m-d')) }}" required>
                    </div>
                </div>

                {{-- Recurrence Section --}}
                <div class="mb-3">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="isRecurring" name="is_recurring" value="1" {{ old('is_recurring', $task->is_recurring) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="isRecurring">
                            <i class="fas fa-redo me-1" style="color: #C8A165;"></i>Make this a recurring task
                        </label>
                    </div>
                    <div class="recurrence-section {{ old('is_recurring', $task->is_recurring) ? '' : 'disabled' }}" id="recurrenceFields">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="recurrence_type" class="form-label">Repeat Every</label>
                                <select name="recurrence_type" id="recurrence_type" class="form-select" {{ old('is_recurring', $task->is_recurring) ? '' : 'disabled' }}>
                                    <option value="">Select frequency...</option>
                                    <option value="daily" {{ old('recurrence_type', $task->recurrence_type) === 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ old('recurrence_type', $task->recurrence_type) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="biweekly" {{ old('recurrence_type', $task->recurrence_type) === 'biweekly' ? 'selected' : '' }}>Every 2 Weeks</option>
                                    <option value="monthly" {{ old('recurrence_type', $task->recurrence_type) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                                @error('recurrence_type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="recurrence_end_date" class="form-label">End Date <small class="text-muted">(optional)</small></label>
                                <input type="date" name="recurrence_end_date" id="recurrence_end_date" class="form-control" value="{{ old('recurrence_end_date', $task->recurrence_end_date?->format('Y-m-d')) }}" {{ old('is_recurring', $task->is_recurring) ? '' : 'disabled' }}>
                                <div class="form-text">Leave empty to recur indefinitely.</div>
                                @error('recurrence_end_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                @if ($canAssign)
                    <div class="mb-3">
                        <label class="form-label">Assign to Staff</label>
                        <div class="row">
                            @foreach ($employees as $employee)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="assignees[]" id="assignee_{{ $employee->id }}" value="{{ $employee->id }}" class="form-check-input"
                                            {{ $task->employees->pluck('id')->contains($employee->id) ? 'checked' : '' }}>
                                        <label for="assignee_{{ $employee->id }}" class="form-check-label">{{ $employee->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text">Deselect all to make this a personal task.</div>
                    </div>
                @endif

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-gold">
                        <i class="fas fa-save me-1"></i>Update Task
                    </button>
                    <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-outline-dark">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('isRecurring');
        const fields = document.getElementById('recurrenceFields');
        const typeSelect = document.getElementById('recurrence_type');
        const endDate = document.getElementById('recurrence_end_date');

        function updateRecurrenceUI() {
            const enabled = toggle.checked;
            fields.classList.toggle('disabled', !enabled);
            typeSelect.disabled = !enabled;
            endDate.disabled = !enabled;
        }

        toggle.addEventListener('change', updateRecurrenceUI);
    });
</script>
@endsection
