@extends('website::layouts.master')

@section('title', 'Pre-Arrival — Upload ID')

@section('content')
<div class="min-vh-100 py-5" style="background: linear-gradient(135deg, #f8f6f1 0%, #efece4 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                @include('website::guest.pre-arrival._steps', ['steps' => $steps, 'current' => 'documents'])

                <div class="card border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 px-lg-5">
                        <h4 class="fw-bold mb-1">Upload Identification</h4>
                        <p class="text-muted small mb-0">Upload a clear photo or scan of your ID document. Accepted formats: JPG, PNG, PDF (max 10MB).</p>
                    </div>
                    <div class="card-body p-4 p-lg-5">

                        @if($registration->documents->count() > 0)
                            <h5 class="fw-bold mb-3">Uploaded Documents</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-borderless align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Type</th>
                                            <th>File</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($registration->documents as $doc)
                                            <tr>
                                                <td><span class="badge bg-secondary text-uppercase">{{ str_replace('_', ' ', $doc->type) }}</span></td>
                                                <td>
                                                    <i class="fas fa-file me-1"></i>
                                                    {{ $doc->original_name }}
                                                    <small class="text-muted d-block">({{ number_format($doc->file_size / 1024, 1) }} KB)</small>
                                                </td>
                                                <td>
                                                    @if($doc->status === 'approved')
                                                        <span class="badge bg-success">Approved</span>
                                                    @elseif($doc->status === 'rejected')
                                                        <span class="badge bg-danger" title="{{ $doc->rejection_reason }}">Rejected</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">Pending Review</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <form method="POST" action="{{ route('guest.pre-arrival.delete-document', [$registration, $doc]) }}"
                                                          onsubmit="return confirm('Remove this document?');">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <h5 class="fw-bold mb-3">Upload New Document</h5>
                        <form method="POST" action="{{ route('guest.pre-arrival.upload-document', $registration) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Document Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        <option value="">Select type...</option>
                                        <option value="passport">International Passport</option>
                                        <option value="driver_license">Driver's License</option>
                                        <option value="national_id">National ID</option>
                                        <option value="visa">Visa</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">File <span class="text-danger">*</span></label>
                                    <input type="file" name="document" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                    @error('document')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3 px-4">
                                <i class="fas fa-upload me-2"></i> Upload
                            </button>
                        </form>

                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <a href="{{ route('guest.pre-arrival.details', $registration) }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-arrow-left me-2"></i> Back
                            </a>
                            <a href="{{ route('guest.pre-arrival.signature', $registration) }}" class="btn btn-primary px-5 py-2 fw-bold">
                                Continue <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
