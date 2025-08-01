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
                <form method="POST" action="{{ route('restaurant.admin.add-category') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="parent_category" class="form-label">Menu Category</label>
                            <select name="parent_category" id="parent_category" class="form-select" >
                                <option value="" disabled selected>Select Menu Categories</option>
                                @foreach($parent_categories as $category)
                                    <option value="{{$category->id}}">{{$category->name}}</option>
                                @endforeach
                            </select>
                            @error('parent_category')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="sub_category" class="form-label">Sub category</label>
                            <select name="sub_category" id="sub_category" class="form-select" >
                                <option value="" disabled selected>Select Sub Menu Categories</option>
                            </select>
                            @error('sub_category')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" name="name" id="name" class="form-control" required>
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

@section('scripts')
    <script >
        $(document).ready(function() {
            document.getElementById('parent_category').addEventListener('change', function () {
                const selectedValue = event.target.value; 
                console.log('Selected Parent Category ID:', selectedValue);
                var categories = @json($categories); // For numbers (without quotes)
                // Filter categories based on the selected parent category
                var sub_categories = categories.filter(category => category.parent_id == selectedValue);
                document.getElementById('sub_category').innerHTML = '<option value="" >Select Sub Menu Categories</option>';
                sub_categories.forEach(function (category) {
                    var option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    document.getElementById('sub_category').appendChild(option);
                });
            });

        });
    </script>
@endsection

