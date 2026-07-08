@extends('website::layouts.master')

@section('title', 'Pre-Arrival — Sign Registration Card')

@section('content')
<div class="min-vh-100 py-5" style="background: linear-gradient(135deg, #f8f6f1 0%, #efece4 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                @include('website::guest.pre-arrival._steps', ['steps' => $steps, 'current' => 'signature'])

                <div class="card border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 px-lg-5">
                        <h4 class="fw-bold mb-1">Sign Registration Card</h4>
                        <p class="text-muted small mb-0">Please sign below to agree to the hotel policies and terms of stay.</p>
                    </div>
                    <div class="card-body p-4 p-lg-5">

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            By signing, you agree to abide by the hotel's policies, including no-smoking in rooms, damages responsibility, and checkout time.
                        </div>

                        <form method="POST" action="{{ route('guest.pre-arrival.submit-signature', $registration) }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name (as on ID)</label>
                                <input type="text" class="form-control" value="{{ $registration->guest->full_name }}" readonly>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Digital Signature <span class="text-danger">*</span></label>
                                <div class="signature-wrapper border rounded p-2" style="background: #fff;">
                                    <canvas id="signatureCanvas" width="700" height="200"
                                            style="width: 100%; height: 180px; touch-action: none; cursor: crosshair;"></canvas>
                                </div>
                                <input type="hidden" name="signature" id="signatureData">
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" id="clearSignature" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-undo me-1"></i> Clear
                                    </button>
                                </div>
                                @error('signature')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                <a href="{{ route('guest.pre-arrival.documents', $registration) }}" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Back
                                </a>
                                <button type="submit" class="btn btn-primary px-5 py-2 fw-bold" id="completeBtn">
                                    <span class="spinner-border spinner-border-sm d-none me-2" id="completeSpinner"></span>
                                    <span id="completeText">Complete Check-In <i class="fas fa-check ms-2"></i></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signatureCanvas');
        const wrapper = canvas.parentElement;
        const hiddenInput = document.getElementById('signatureData');

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
        }

        resizeCanvas();
        const signaturePad = new SignaturePad(canvas, {
            penColor: '#1a1a1a',
            backgroundColor: '#ffffff',
        });

        document.getElementById('clearSignature').addEventListener('click', function() {
            signaturePad.clear();
            hiddenInput.value = '';
        });

        window.addEventListener('resize', resizeCanvas);

        document.querySelector('form').addEventListener('submit', function(e) {
            if (signaturePad.isEmpty()) {
                e.preventDefault();
                alert('Please provide your signature before continuing.');
                return;
            }
            hiddenInput.value = signaturePad.toDataURL();
            const btn = document.getElementById('completeBtn');
            btn.disabled = true;
            document.getElementById('completeSpinner').classList.remove('d-none');
            document.getElementById('completeText').innerHTML = 'Submitting...';
        });
    });
</script>
@endpush
