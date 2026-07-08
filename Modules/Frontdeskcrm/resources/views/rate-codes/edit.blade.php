@extends('layouts.master')

@section('page-content')
<div class="card shadow-sm">
    <div class="card-header">
        <h4>Edit Rate Code: {{ $rateCode->name }}</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('frontdesk.rate-codes.update', $rateCode) }}" method="POST">
            @csrf @method('PUT')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $rateCode->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code', $rateCode->code) }}" required maxlength="20" style="text-transform:uppercase">
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sort Order</label>
                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" name="sort_order" value="{{ old('sort_order', $rateCode->sort_order) }}" min="0">
                    @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $rateCode->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="2">{{ old('description', $rateCode->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Base Prices per Room Type</label>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Room Type</th>
                                <th>Price (&#8358;)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roomTypes as $roomType)
                            @php
                                $existingPrice = $rateCode->prices->firstWhere('room_type_id', $roomType->id);
                            @endphp
                            <tr>
                                <td>{{ $roomType->name }}</td>
                                <td style="width: 200px;">
                                    <input type="hidden" name="prices[{{ $loop->index }}][room_type_id]" value="{{ $roomType->id }}">
                                    <input type="number" step="0.01" class="form-control form-control-sm"
                                        name="prices[{{ $loop->index }}][price]"
                                        value="{{ old('prices.' . $loop->index . '.price', $existingPrice?->price ?? $roomType->price) }}"
                                        min="0" placeholder="0.00">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('frontdesk.rate-codes.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
