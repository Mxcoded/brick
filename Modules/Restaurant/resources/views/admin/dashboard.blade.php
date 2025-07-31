@extends('restaurant::layouts.adminMaster')

@section('content')
    <div class="container my-4">
        <h1 class="mb-4">Add Menu Category</h1>
        <div class="card shadow-sm">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                <form method="POST" action="{{ route('staff.leaves.balance-submit') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="parent_category" class="form-label">Menu Category</label>
                            <select name="parent_category" id="parent_category" class="form-select" required>
                                <option value="" disabled selected>Select Menu Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{$category->id}}">{{$category->name}}</option>
                                @endforeach
                            </select>
                            @error('parent_category')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="total_days" class="form-label">Total Days</label>
                            <input type="number" name="total_days" id="total_days" class="form-control" required>
                        </div>
                        
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Submit Balance</button>
                        <a href="{{ route('staff.leaves.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
