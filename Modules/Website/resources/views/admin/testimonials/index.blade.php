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
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Guest</th>
                            <th>Review</th>
                            <th>Rating</th>
                            <th>Stay Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($testimonials as $testimonial)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($testimonial->guest_image)
                                            <img src="{{ $testimonial->guest_image }}" class="rounded-circle me-2" width="28" height="28" alt="">
                                        @else
                                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width:28px;height:28px;background:rgba(200,161,101,0.2);color:#C8A165;font-size:0.75rem;">{{ substr($testimonial->guest_name, 0, 1) }}</div>
                                        @endif
                                        <span class="fw-semibold">{{ $testimonial->guest_name }}</span>
                                    </div>
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($testimonial->text, 60) }}</td>
                                <td class="text-nowrap">
                                    @for ($i = 0; $i < 5; $i++)
                                        <i class="fa{{ $i < $testimonial->rating ? 's' : 'r' }} fa-star text-warning" style="font-size:0.8rem"></i>
                                    @endfor
                                </td>
                                <td>{{ $testimonial->stay_type ?? '-' }}</td>
                                <td>
                                    @if ($testimonial->approved)
                                        <span class="badge bg-success px-3 py-2 rounded-pill fs-6 fw-normal">
                                            <i class="fas fa-check-circle me-1"></i> Approved
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fs-6 fw-normal">
                                            <i class="fas fa-clock me-1"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('website.admin.testimonials.show', $testimonial) }}" class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('website.admin.testimonials.toggle-approve', $testimonial) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $testimonial->approved ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $testimonial->approved ? 'Unapprove' : 'Approve' }}">
                                                <i class="fas {{ $testimonial->approved ? 'fa-times-circle' : 'fa-check-circle' }}"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('website.admin.testimonials.edit', $testimonial) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('website.admin.testimonials.destroy', $testimonial) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this testimonial?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
