@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('contracts.dashboard') }}">Contracts</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contracts.agreements.index') }}">Agreements</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $agreement->agreement_number }}</li>
@endsection

@php
    $status = $agreement->statusEnum();
    $type = $agreement->typeEnum();
    $hotel = $agreement->hotelParty();
    $client = $agreement->primaryClient();
    $ct = $agreement->commercial_terms ?? [];
    $currentIndex = array_search($agreement->status, $workflow, true);
    $currentIndex = $currentIndex === false ? count($workflow) : $currentIndex;
    $canUpdate = Gate::allows('contracts.update');
    $canApprove = Gate::allows('contracts.approve');
    $canSign = Gate::allows('contracts.sign');
    $lockReason = in_array($agreement->status, ['executed', 'active', 'expired', 'cancelled'], true);
@endphp

@section('page-content')
<div class="container-fluid py-4 contracts-theme">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0">{{ $agreement->agreement_number }}</h4>
                @include('contracts::partials.status-badge', ['agreement' => $agreement])
            </div>
            <span class="text-muted small">{{ $type->label() }} · v{{ $agreement->current_version }}</span>
        </div>
        <div class="d-flex gap-2">
            @if (! $lockReason && $canUpdate)
                <a href="{{ route('contracts.agreements.edit', $agreement) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
            @endif
            <a href="{{ route('contracts.agreements.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-list me-1"></i>All agreements</a>
        </div>
    </div>

    {{-- WORKFLOW STEPPER --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-stretch justify-content-between workflow-steps">
                @foreach ($workflow as $index => $step)
                    @php($stepStatus = \Modules\Contracts\Enums\AgreementStatus::from($step))
                    <div class="workflow-step flex-fill {{ $index < $currentIndex ? 'done' : ($index === $currentIndex ? 'current' : '') }}">
                        <div class="workflow-dot"><i class="fas {{ $index < $currentIndex ? 'fa-check' : 'fa-circle' }}"></i></div>
                        <div class="workflow-label small text-nowrap">{{ $stepStatus->label() }}</div>
                    </div>
                    @if (! $loop->last)
                        <div class="workflow-connector align-self-center {{ $index < $currentIndex ? 'done' : '' }}"></div>
                    @endif
                @endforeach
            </div>

            @if (! empty($allowedTransitions) && $canUpdate)
                <hr>
                <form method="POST" action="{{ route('contracts.agreements.status', $agreement) }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Comments / reason (optional)</label>
                        <input type="text" name="comments" class="form-control form-control-sm" placeholder="Optional note for this transition">
                    </div>
                    <div class="col-md-8 d-flex flex-wrap gap-2 align-items-center">
                        <span class="small text-muted me-1">Move to:</span>
                        @foreach ($allowedTransitions as $to)
                            @php
                                $target = \Modules\Contracts\Enums\AgreementStatus::from($to);
                                $needsApproval = in_array($to, ['approved', 'executed', 'active'], true);
                                $needsSignature = in_array($to, ['client_signed', 'hotel_signed'], true);
                                $allowed = ($needsApproval && ! $canApprove) || ($needsSignature && ! $canSign) ? false : true;
                            @endphp
                            @if ($allowed)
                                <button type="submit" name="to" value="{{ $to }}" class="btn btn-sm {{ $to === 'cancelled' ? 'btn-outline-danger' : 'btn-gold-outline' }}">
                                    {{ $target->label() }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-4">
        {{-- LEFT COLUMN --}}
        <div class="col-lg-8">
            {{-- OVERVIEW & COMMERCIAL TERMS --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">{{ $agreement->title }}</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="small text-muted">Agreement value</div>
                            <div class="fw-bold fs-5">{{ $agreement->currency }} {{ number_format((float) $agreement->value_amount, 2) }}</div>
                        </div>
                        <div class="col-md-2">
                            <div class="small text-muted">Deposit</div>
                            <div class="fw-semibold">{{ $agreement->currency }} {{ number_format((float) $agreement->deposit_amount, 2) }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Effective</div>
                            <div class="fw-semibold">{{ $agreement->effective_date?->format('d M Y') ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Expiry</div>
                            <div class="fw-semibold">{{ $agreement->expiry_label }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Auto-renewal</div>
                            <div class="fw-semibold">{{ $agreement->auto_renew ? 'Eligible (task generated)' : 'No' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Department</div>
                            <div class="fw-semibold">{{ $agreement->department ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Property</div>
                            <div class="fw-semibold">{{ $agreement->location ?? '—' }}</div>
                        </div>

                        <div class="col-12">
                            <hr class="my-1">
                            <h6 class="fw-semibold text-muted text-uppercase small mb-3 mt-2">Commercial Terms</h6>
                            <div class="row g-2">
                                <div class="col-md-3 small"><span class="text-muted">Room type:</span> <strong>{{ $ct['room_type'] ?? '—' }}</strong></div>
                                <div class="col-md-2 small"><span class="text-muted">Rooms:</span> <strong>{{ $ct['number_of_rooms'] ?? '—' }}</strong></div>
                                <div class="col-md-3 small"><span class="text-muted">Rate/night:</span> <strong>{{ ($ct['rate_per_night'] ?? '') !== '' ? $agreement->currency.' '.number_format((float) $ct['rate_per_night'], 2) : '—' }}</strong></div>
                                <div class="col-md-2 small"><span class="text-muted">Discount:</span> <strong>{{ ($ct['discount_percent'] ?? '') !== '' ? $ct['discount_percent'].'%' : '—' }}</strong></div>
                                <div class="col-md-2 small"><span class="text-muted">Tax:</span> <strong>{{ ! empty($ct['tax_applicable']) ? 'Yes' : 'No' }}</strong></div>
                                <div class="col-md-3 small"><span class="text-muted">Check-in:</span> <strong>{{ $ct['check_in_date'] ?? '—' }}</strong></div>
                                <div class="col-md-3 small"><span class="text-muted">Check-out:</span> <strong>{{ $ct['check_out_date'] ?? '—' }}</strong></div>
                                <div class="col-md-3 small"><span class="text-muted">Payment terms:</span> <strong>{{ $ct['payment_terms'] ?? '—' }}</strong></div>
                                <div class="col-md-3 small"><span class="text-muted">Cancellation:</span> <strong>{{ $ct['cancellation_policy'] ?? '—' }}</strong></div>
                            </div>
                        </div>

                        @if ($agreement->template)
                            <div class="col-12 small">
                                <span class="text-muted">Based on template:</span> <strong>{{ $agreement->template->name }}</strong>
                            </div>
                        @endif
                        @if ($agreement->notes)
                            <div class="col-12 small">
                                <div class="text-muted mb-1">Internal notes</div>
                                <div>{{ $agreement->notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- CLAUSES --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Clauses ({{ $agreement->clauses->count() }})</div>
                <div class="card-body">
                    @forelse ($agreement->clauses as $clause)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="fw-semibold mb-1">{{ $loop->iteration }}. {{ $clause->title }}</h6>
                                <span class="badge bg-light text-muted">v{{ $clause->version }}</span>
                            </div>
                            <div class="text-muted small" style="white-space: pre-wrap;">{{ $clause->content }}</div>
                        </div>
                    @empty
                        <div class="text-muted small">No clauses defined for this agreement.</div>
                    @endforelse
                </div>
            </div>

            {{-- OBLIGATIONS --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Obligations &amp; Commitments ({{ $agreement->obligations->count() }})</div>
                <div class="card-body">
                    @if ($agreement->obligations->isEmpty())
                        <div class="text-muted small mb-3">No tracked obligations.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-3">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Title</th>
                                        <th>Party</th>
                                        <th>Amount</th>
                                        <th>Due</th>
                                        <th>Status</th>
                                        @if ($canUpdate)<th class="text-end">Action</th>@endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($agreement->obligations as $obligation)
                                        <tr>
                                            <td><span class="badge bg-light text-dark text-capitalize">{{ str_replace('_', ' ', $obligation->obligation_type) }}</span></td>
                                            <td>
                                                <div class="fw-semibold">{{ $obligation->title }}</div>
                                                @if ($obligation->description)<div class="small text-muted">{{ $obligation->description }}</div>@endif
                                            </td>
                                            <td class="text-capitalize">{{ $obligation->responsible_party }}</td>
                                            <td>{{ $obligation->amount !== null ? $agreement->currency.' '.number_format((float) $obligation->amount, 2) : '—' }}</td>
                                            <td>{{ $obligation->due_date?->format('d M Y') ?? '—' }}</td>
                                            <td><span class="badge {{ $obligation->status_class }}">{{ ucfirst($obligation->status) }}</span></td>
                                            @if ($canUpdate)
                                                <td class="text-end">
                                                    <form method="POST" action="{{ route('contracts.agreements.obligations.update', [$agreement, $obligation]) }}" class="d-inline-flex gap-1">
                                                        @csrf @method('PATCH')
                                                        <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                                                            @foreach (['pending', 'in_progress', 'completed', 'overdue', 'cancelled'] as $s)
                                                                <option value="{{ $s }}" @selected($obligation->status === $s)>{{ ucfirst($s) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </form>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if ($canUpdate)
                        <details>
                            <summary class="small fw-semibold text-primary">+ Add an obligation</summary>
                            <form method="POST" action="{{ route('contracts.agreements.obligations.store', $agreement) }}" class="row g-2 mt-2">
                                @csrf
                                <div class="col-md-3">
                                    <select name="obligation_type" class="form-select form-select-sm" required>
                                        @foreach (['payment', 'service', 'delivery', 'entitlement', 'renewal', 'other'] as $o)
                                            <option value="{{ $o }}">{{ ucfirst($o) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="title" class="form-control form-control-sm" placeholder="Obligation title" required>
                                </div>
                                <div class="col-md-2">
                                    <select name="responsible_party" class="form-select form-select-sm">
                                        <option value="client">Client</option>
                                        <option value="hotel">Hotel</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" step="0.01" min="0" name="amount" class="form-control form-control-sm" placeholder="Amount">
                                </div>
                                <div class="col-md-1">
                                    <button class="btn btn-sm btn-gold w-100" title="Add"><i class="fas fa-plus"></i></button>
                                </div>
                                <div class="col-12">
                                    <textarea name="description" class="form-control form-control-sm" rows="1" placeholder="Description (optional)"></textarea>
                                </div>
                            </form>
                        </details>
                        <div class="small text-muted mt-2">Due date can be added after creation; the agreement engine lights up payments/entitlements/renewals here.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-lg-4">
            {{-- APPROVAL & VERSION --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Approval &amp; Execution</div>
                <div class="card-body small">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Created by</span><span class="fw-semibold">{{ $agreement->creator?->name ?? '—' }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Created at</span><span class="fw-semibold">{{ $agreement->created_at?->format('d M Y H:i') }}</span></div>
                    @if ($agreement->approved_by)
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Approved by</span><span class="fw-semibold">{{ $agreement->approver?->name ?? '—' }}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Approved at</span><span class="fw-semibold">{{ $agreement->approved_at?->format('d M Y H:i') }}</span></div>
                    @endif
                    <div class="d-flex justify-content-between"><span class="text-muted">Current version</span><span class="fw-semibold">v{{ $agreement->current_version }}</span></div>
                </div>
            </div>

            {{-- VERSIONS --}}
            @if ($agreement->versions->count() > 1)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold">Version History</div>
                    <div class="card-body small">
                        <ul class="list-unstyled mb-0">
                            @foreach ($agreement->versions->sortByDesc('version')->take(8) as $version)
                                <li class="d-flex justify-content-between py-1 border-bottom">
                                    <span>
                                        <strong>v{{ $version->version }}</strong>
                                        <span class="badge bg-light text-muted text-capitalize">{{ str_replace('_', ' ', $version->status) }}</span>
                                        <div class="text-muted">{{ $version->changes_summary }}</div>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- SIGNATURES --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Signatures ({{ $agreement->signatures->count() }})</div>
                <div class="card-body">
                    @forelse ($agreement->signatures as $signature)
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                            <div>
                                <i class="fas {{ $signature->party_role === 'hotel' ? 'fa-hotel text-primary' : 'fa-user text-success' }} me-1"></i>
                                <span class="fw-semibold">{{ $signature->party_name }}</span>
                                <span class="badge bg-light text-muted text-capitalize">{{ $signature->party_role }}</span>
                                @if ($signature->position)<span class="small text-muted"> · {{ $signature->position }}</span>@endif
                            </div>
                            <span class="small text-muted">{{ $signature->signed_at?->format('d M Y H:i') }}</span>
                        </div>
                        <div class="small text-muted mb-3">
                            <i class="fas fa-mouse-pointer me-1"></i> {{ $signature->signature_type }}
                            @if ($signature->verification) · {{ $signature->verification }}@endif
                        </div>
                    @empty
                        <div class="text-muted small mb-3">No signatures recorded yet.</div>
                    @endforelse

                    @if ($canSign)
                        <details>
                            <summary class="small fw-semibold text-primary">+ Record a signature</summary>
                            <form method="POST" action="{{ route('contracts.agreements.signatures.store', $agreement) }}" class="row g-2 mt-2">
                                @csrf
                                <div class="col-6">
                                    <select name="party_role" class="form-select form-select-sm">
                                        <option value="client">Client</option>
                                        <option value="hotel">Hotel</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select name="signature_type" class="form-select form-select-sm">
                                        <option value="click">Click to sign</option>
                                        <option value="typed">Typed</option>
                                        <option value="draw">Drawn</option>
                                        <option value="upload">Uploaded</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <input type="text" name="party_name" class="form-control form-control-sm" placeholder="Signatory full name" required>
                                </div>
                                <div class="col-8">
                                    <input type="text" name="position" class="form-control form-control-sm" placeholder="Position / title">
                                </div>
                                <div class="col-4">
                                    <input type="text" name="verification" class="form-control form-control-sm" placeholder="OTP/ID (optional)">
                                </div>
                                <div class="col-12">
                                    <textarea name="signature_data" class="form-control form-control-sm" rows="2" placeholder="Signature data / notes (optional)"></textarea>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-sm btn-gold w-100"><i class="fas fa-signature me-1"></i>Record signature</button>
                                </div>
                            </form>
                        </details>
                    @endif
                </div>
            </div>

            {{-- AMENDMENTS --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Amendments ({{ $agreement->amendments->count() }})</div>
                <div class="card-body">
                    @forelse ($agreement->amendments as $amendment)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold small">{{ $amendment->amendment_no }}</span>
                                <span class="badge bg-warning text-dark text-capitalize">{{ $amendment->status }}</span>
                            </div>
                            <div class="fw-semibold">{{ $amendment->title }}</div>
                            <div class="small text-muted">{{ $amendment->description }}</div>
                            <div class="small text-muted">Effective {{ $amendment->effective_date?->format('d M Y') ?? 'upon signing' }} · by {{ $amendment->creator?->name ?? '—' }}</div>
                        </div>
                    @empty
                        <div class="text-muted small mb-3">No amendments.</div>
                    @endforelse

                    @if (in_array($agreement->status, ['active', 'executed'], true) && $canUpdate)
                        <details>
                            <summary class="small fw-semibold text-primary">+ Create an amendment</summary>
                            <form method="POST" action="{{ route('contracts.agreements.amendments.store', $agreement) }}" class="row g-2 mt-2">
                                @csrf
                                <div class="col-12">
                                    <input type="text" name="title" class="form-control form-control-sm" placeholder="Amendment title" required>
                                </div>
                                <div class="col-12">
                                    <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="What changes?" required></textarea>
                                </div>
                                <div class="col-12">
                                    <input type="date" name="effective_date" class="form-control form-control-sm">
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-sm btn-gold w-100"><i class="fas fa-file-contract me-1"></i>Create amendment</button>
                                </div>
                            </form>
                        </details>
                    @endif
                </div>
            </div>

            {{-- DOCUMENTS --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Documents ({{ $agreement->documents->count() }})</div>
                <div class="card-body small">
                    @forelse ($agreement->documents as $document)
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <a href="{{ route('contracts.agreements.documents.download', [$agreement, $document]) }}" class="text-decoration-none">
                                <i class="fas {{ $document->mime_type && str_contains($document->mime_type, 'pdf') ? 'fa-file-pdf text-danger' : 'fa-file text-muted' }} me-1"></i>
                                {{ $document->name }}
                            </a>
                            <span class="text-muted">{{ round($document->size / 1024) }} KB</span>
                        </div>
                        <div class="text-muted mb-2">by {{ $document->uploader?->name ?? '—' }} · {{ $document->created_at?->format('d M Y') }}</div>
                    @empty
                        <div class="text-muted mb-3">No attachments.</div>
                    @endforelse

                    @if ($canUpdate)
                        <details>
                            <summary class="small fw-semibold text-primary">+ Attach a document</summary>
                            <form method="POST" action="{{ route('contracts.agreements.documents.store', $agreement) }}" enctype="multipart/form-data" class="mt-2">
                                @csrf
                                <div class="input-group input-group-sm">
                                    <input type="file" name="document" class="form-control" required>
                                    <button class="btn btn-gold btn-sm"><i class="fas fa-upload"></i></button>
                                </div>
                                <div class="form-text">PDF, Word, spreadsheets or images up to 10 MB.</div>
                            </form>
                        </details>
                    @endif
                </div>
            </div>

            {{-- AUDIT TRAIL --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Audit Trail ({{ $audits->count() }})</div>
                <div class="card-body small">
                    <ul class="timeline list-unstyled mb-0">
                        @forelse ($audits as $audit)
                            <li class="mb-3">
                                <div class="fw-semibold">{{ ucfirst($audit->event) }}
                                    <span class="text-capitalize text-muted">{{ str_replace('_', ' ', $audit->auditable_type) }}</span>
                                </div>
                                <div class="text-muted">
                                    {{ $audit->user?->name ?? 'System' }} · {{ $audit->created_at?->format('d M Y H:i') }}
                                </div>
                                @if ($audit->getModified() && count($audit->getModified()) > 0)
                                    <div class="mt-1">
                                        @foreach ($audit->getModified() as $field => $values)
                                            <span class="badge bg-light text-muted border me-1 mb-1">
                                                {{ $field }}
                                                @if (isset($values['old'])) {{ $values['old'] }} → @endif
                                                {{ $values['new'] ?? '' }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </li>
                        @empty
                            <li class="text-muted">No audit entries.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<style>
    .contracts-theme { font-family: 'Proxima Nova', Arial, Helvetica, sans-serif; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #FFFFFF; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #FFFFFF; }
    .btn-gold-outline { background-color: #FFFFFF; border: 1px solid #C8A165; color: #C8A165; }
    .btn-gold-outline:hover { background-color: #C8A165; color: #FFFFFF; }
    .workflow-steps { flex-wrap: wrap; }
    .workflow-step { text-align: center; }
    .workflow-dot { width: 30px; height: 30px; margin: 0 auto 6px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #e9ecef; color: #6c757d; }
    .workflow-step.current .workflow-dot { background: #C8A165; color: #fff; box-shadow: 0 0 0 4px rgba(200,161,101,.25); }
    .workflow-step.done .workflow-dot { background: #198754; color: #fff; }
    .workflow-step.done .workflow-label { color: #198754; }
    .workflow-connector { height: 3px; flex: 0 0 16px; background: #e9ecef; border-radius: 2px; margin-bottom: 22px; }
    .workflow-connector.done { background: #198754; }
</style>
@endsection