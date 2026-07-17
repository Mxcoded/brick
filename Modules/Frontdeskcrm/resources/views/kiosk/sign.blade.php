<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guest Signature — {{ config('app.name') }}</title>
    @vite(['resources/sass/app.scss'])
    <style>
        body { background: #f5f7fb; min-height: 100vh; display: flex; align-items: center; font-family: system-ui, -apple-system, sans-serif; }
        .kiosk-card { border: none; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); overflow: hidden; }
        .brand-bar { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 2rem; text-align: center; }
        .brand-bar h2 { color: #C8A165; font-weight: 700; letter-spacing: 2px; margin: 0; }
        .brand-bar small { color: rgba(255,255,255,0.5); letter-spacing: 3px; text-transform: uppercase; font-size: 0.7rem; }
        .result-card { border-radius: 12px; }
        .result-card.success { background: #f0fdf4; border: 2px solid #22c55e; }
        .result-card.warning { background: #fffbeb; border: 2px solid #f59e0b; }
        .result-card.error { background: #fef2f2; border: 2px solid #ef4444; }
        .icon-circle { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .icon-circle.success { background: #dcfce7; color: #16a34a; }
        .icon-circle.warning { background: #fef3c7; color: #d97706; }
        .icon-circle.error { background: #fecaca; color: #dc2626; }
        .signature-pad-container { position: relative; border: 1px solid #ddd; }
        #signature-pad { position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: crosshair; }
        .signature-placeholder { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #aaa; pointer-events: none; display: flex; align-items: center; gap: 8px; }
        .step-section { transition: opacity 0.3s ease; }
    </style>
</head>
<body>
    <div class="container" style="max-width: 600px;">
        <div class="card kiosk-card">
            <div class="brand-bar">
                <h2>BRICKSPOINT</h2>
                <small>Guest Signature Kiosk</small>
            </div>
            <div class="card-body p-4">

                {{-- Step 1: Code Entry --}}
                <div id="step-entry" class="step-section">
                    <p class="text-muted text-center mb-4 small">
                        <i class="fas fa-signature me-1"></i>
                        Enter your reservation code to sign the registration form.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Reservation Code</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light"><i class="fas fa-qrcode text-muted"></i></span>
                            <input type="text" id="reservationCode" class="form-control"
                                   placeholder="e.g. FD26A7BK" required autofocus autocomplete="off">
                            <button class="btn btn-dark px-4" type="button" id="lookupBtn">
                                <i class="fas fa-search me-1"></i> Lookup
                            </button>
                        </div>
                        <div id="lookupError" class="text-danger small mt-1 d-none"></div>
                    </div>
                </div>

                {{-- Step 2: Result Area (hidden initially) --}}
                <div id="step-result" class="step-section d-none">
                    {{-- Guest details card --}}
                    <div id="guestDetailsCard" class="result-card p-3 mb-3 text-center d-none"></div>

                    {{-- Error / Already signed / Already checked in --}}
                    <div id="messageBlock" class="d-none">
                        <div class="result-card p-4 mb-3 text-center" id="messageCard"></div>
                    </div>

                    {{-- Signature pad (shown only when eligible) --}}
                    <div id="signatureSection" class="d-none">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Sign Below</label>
                            <div class="signature-pad-container border rounded bg-white position-relative"
                                 style="width: 100%; height: 180px; margin: 0 auto;">
                                <canvas id="signature-pad"></canvas>
                                <div id="signature-placeholder" class="signature-placeholder">
                                    <i class="fas fa-pencil-alt"></i>
                                    <span>Sign Here</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="clear-signature">
                                <i class="fas fa-eraser me-1"></i> Clear
                            </button>
                            <input type="hidden" name="guest_signature" id="signature-data">
                        </div>
                        <button type="button" class="btn btn-dark btn-lg w-100 py-3" id="submitBtn">
                            <i class="fas fa-pen me-2"></i> Submit Signature
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-100 mt-2" id="resetBtn">
                            <i class="fas fa-arrow-left me-1"></i> Try Another Code
                        </button>
                    </div>
                </div>

                {{-- Step 3: Success (hidden initially) --}}
                <div id="step-success" class="step-section d-none">
                    <div id="successCard" class="result-card success p-4 mb-4 text-center"></div>
                    <div class="text-center">
                        <button type="button" class="btn btn-outline-dark" id="anotherBtn">
                            <i class="fas fa-redo me-1"></i> Sign Another
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const codeInput = document.getElementById('reservationCode');
            const lookupBtn = document.getElementById('lookupBtn');
            const lookupError = document.getElementById('lookupError');

            const stepEntry = document.getElementById('step-entry');
            const stepResult = document.getElementById('step-result');
            const stepSuccess = document.getElementById('step-success');

            const guestDetailsCard = document.getElementById('guestDetailsCard');
            const messageBlock = document.getElementById('messageBlock');
            const messageCard = document.getElementById('messageCard');
            const signatureSection = document.getElementById('signatureSection');

            const successCard = document.getElementById('successCard');
            const resetBtn = document.getElementById('resetBtn');
            const anotherBtn = document.getElementById('anotherBtn');

            const canvas = document.getElementById('signature-pad');
            const placeholder = document.getElementById('signature-placeholder');
            const signatureData = document.getElementById('signature-data');
            const clearBtn = document.getElementById('clear-signature');
            const submitBtn = document.getElementById('submitBtn');

            let pad = null;
            let currentCode = '';

            function initPad() {
                if (!canvas) return;
                if (pad) pad.off();
                const container = canvas.parentElement;
                if (container.offsetWidth === 0) {
                    setTimeout(initPad, 100);
                    return;
                }
                canvas.width = container.offsetWidth;
                canvas.height = container.offsetHeight;
                pad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(51, 51, 51)',
                    minWidth: 1,
                    maxWidth: 3,
                });
                pad.addEventListener('beginStroke', () => { placeholder.style.display = 'none'; });
                pad.addEventListener('endStroke', () => {
                    signatureData.value = pad.toDataURL();
                });
                if (clearBtn) {
                    clearBtn.addEventListener('click', () => {
                        pad.clear();
                        placeholder.style.display = 'flex';
                        signatureData.value = '';
                    });
                }
            }

            function resetPad() {
                if (pad) {
                    pad.clear();
                    placeholder.style.display = 'flex';
                    signatureData.value = '';
                }
            }

            function showEntry() {
                stepEntry.classList.remove('d-none');
                stepResult.classList.add('d-none');
                stepSuccess.classList.add('d-none');
                codeInput.value = '';
                lookupError.classList.add('d-none');
                codeInput.focus();
                currentCode = '';
            }

            function lookupCode() {
                const code = codeInput.value.trim().toUpperCase();
                if (code.length < 4) {
                    lookupError.textContent = 'Please enter a valid reservation code.';
                    lookupError.classList.remove('d-none');
                    return;
                }
                lookupError.classList.add('d-none');
                lookupBtn.disabled = true;
                lookupBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Looking up...';

                fetch('{{ route('frontdesk.kiosk.sign.lookup') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ reservation_code: code }),
                })
                .then(r => r.json())
                .then(data => {
                    lookupBtn.disabled = false;
                    lookupBtn.innerHTML = '<i class="fas fa-search me-1"></i> Lookup';

                    if (!data.found) {
                        lookupError.textContent = data.message;
                        lookupError.classList.remove('d-none');
                        return;
                    }

                    currentCode = code;
                    guestDetailsCard.innerHTML = `
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                            <div class="icon-circle success" style="width:48px;height:48px;font-size:1.2rem;">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="text-start">
                                <h5 class="fw-bold mb-0">${data.guest.name}</h5>
                                <small class="text-muted">${data.guest.room || 'Room TBA'}</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center gap-4">
                            <div><small class="text-muted d-block">Code</small><strong>${data.guest.code}</strong></div>
                            <div><small class="text-muted d-block">Check In</small><strong>${data.guest.check_in}</strong></div>
                            <div><small class="text-muted d-block">Check Out</small><strong>${data.guest.check_out}</strong></div>
                        </div>
                    `;
                    guestDetailsCard.classList.remove('d-none');

                    if (data.already_signed) {
                        messageCard.className = 'result-card p-4 mb-3 text-center warning';
                        messageCard.innerHTML = `
                            <div class="icon-circle warning mx-auto mb-3">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h5 class="fw-bold mb-1">${data.message}</h5>
                        `;
                        messageBlock.classList.remove('d-none');
                        signatureSection.classList.add('d-none');
                    } else {
                        messageBlock.classList.add('d-none');
                        signatureSection.classList.remove('d-none');
                        resetPad();
                        setTimeout(initPad, 50);
                    }

                    stepEntry.classList.add('d-none');
                    stepResult.classList.remove('d-none');
                })
                .catch(() => {
                    lookupBtn.disabled = false;
                    lookupBtn.innerHTML = '<i class="fas fa-search me-1"></i> Lookup';
                    lookupError.textContent = 'Lookup failed. Please try again.';
                    lookupError.classList.remove('d-none');
                });
            }

            function submitSignature() {
                if (!pad || pad.isEmpty()) {
                    alert('Please sign the form before submitting.');
                    return;
                }
                signatureData.value = pad.toDataURL();

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';

                fetch('{{ route('frontdesk.kiosk.sign.submit') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ reservation_code: currentCode, guest_signature: signatureData.value }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        successCard.innerHTML = `
                            <div class="icon-circle success mx-auto mb-3">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h4 class="fw-bold mb-1">Signature Captured!</h4>
                            <p class="text-muted small mb-3">Thank you, <strong>${data.guest.name}</strong></p>
                            <div class="d-flex justify-content-center gap-4 mb-3">
                                <div><small class="text-muted d-block">Reservation</small><strong>${data.guest.code}</strong></div>
                                <div><small class="text-muted d-block">Room</small><strong>${data.guest.room || 'TBA'}</strong></div>
                            </div>
                            <div class="text-muted small">${data.guest.check_in} — ${data.guest.check_out}</div>
                        `;
                        stepResult.classList.add('d-none');
                        stepSuccess.classList.remove('d-none');
                    } else {
                        alert(data.message);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-pen me-2"></i> Submit Signature';
                    }
                })
                .catch(() => {
                    alert('Submission failed. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-pen me-2"></i> Submit Signature';
                });
            }

            // Event listeners
            lookupBtn.addEventListener('click', lookupCode);
            codeInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') lookupCode(); });
            submitBtn.addEventListener('click', submitSignature);
            resetBtn.addEventListener('click', showEntry);
            anotherBtn.addEventListener('click', showEntry);
        });
    </script>
</body>
</html>
