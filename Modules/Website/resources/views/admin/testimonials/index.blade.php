@extends('layouts.master')

@section('title', 'Manage Testimonials')

@section('page-content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Manage Testimonials</h1>
            <a href="{{ route('website.admin.testimonials.create') }}" class="btn btn-primary">Add New Testimonial</a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($testimonials->isEmpty())
                <p>No testimonials found.</p>
            @else
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Guest</th>
                            <th>Review</th>
                            <th>Rating</th>
                            <th>Stay Type</th>
                            <th>Approved</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($testimonials as $testimonial)
                            <tr>
                                <td>
                                    @if ($testimonial->guest_image)
                                        <img src="{{ $testimonial->guest_image }}" class="rounded-circle me-1" width="24" height="24" alt="">
                                    @endif
                                    {{ $testimonial->guest_name }}
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($testimonial->text, 60) }}</td>
                                <td>
                                    @for ($i = 0; $i < 5; $i++)
                                        <i class="fas fa-star{{ $i < $testimonial->rating ? '' : '-empty' }} text-warning" style="font-size:0.85rem"></i>
                                    @endfor
                                </td>
                                <td>{{ $testimonial->stay_type ?? '-' }}</td>
                                <td>
                                    @if ($testimonial->approved)
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('website.admin.testimonials.show', $testimonial) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('website.admin.testimonials.edit', $testimonial) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('website.admin.testimonials.destroy', $testimonial) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
