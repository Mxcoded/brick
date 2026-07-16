@extends('layouts.master')

@section('title', 'Edit Account')

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Account</h2>
        <a href="{{ route('finance.coa.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('finance.coa.update', $chartOfAccount) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">GL Code</label>
                        <input type="text" name="code" value="{{ old('code', $chartOfAccount->code) }}" class="form-control @error('code') is-invalid @enderror" required>
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" value="{{ old('name', $chartOfAccount->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                            @foreach (['asset','liability','equity','income','expense'] as $type)
                                <option value="{{ $type }}" {{ old('type', $chartOfAccount->type) === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Normal Balance</label>
                        <select name="normal_balance" id="normal_balance" class="form-select @error('normal_balance') is-invalid @enderror" required>
                            <option value="debit" {{ old('normal_balance', $chartOfAccount->normal_balance) === 'debit' ? 'selected' : '' }}>Debit</option>
                            <option value="credit" {{ old('normal_balance', $chartOfAccount->normal_balance) === 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                        @error('normal_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Parent Account</label>
                        <select name="parent_id" class="form-select">
                            <option value="">None</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" {{ old('parent_id', $chartOfAccount->parent_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->code }} — {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $chartOfAccount->description) }}</textarea>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="is_contra" id="is_contra" value="1" {{ old('is_contra', $chartOfAccount->is_contra) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_contra">Contra account</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="active" id="active" value="1" {{ old('active', $chartOfAccount->active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Account</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('type').addEventListener('change', function () {
            const credit = ['liability', 'equity', 'income'].includes(this.value);
            document.getElementById('normal_balance').value = credit ? 'credit' : 'debit';
        });
    </script>
@endsection
