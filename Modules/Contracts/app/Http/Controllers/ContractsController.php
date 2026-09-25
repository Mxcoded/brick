<?php

namespace Modules\Contracts\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\Contracts\Enums\AgreementStatus;
use Modules\Contracts\Enums\AgreementType;
use Modules\Contracts\Models\Agreement;
use Modules\Contracts\Models\AgreementDocument;
use Modules\Contracts\Models\AgreementObligation;
use Modules\Contracts\Models\AgreementTemplate;
use Modules\Contracts\Services\AgreementNumberGenerator;
use Modules\Contracts\Services\AgreementPdfService;
use Modules\Contracts\Services\AgreementStatusService;
use Yajra\DataTables\DataTables;

class ContractsController extends Controller
{
    public function __construct(
        protected AgreementNumberGenerator $numberGenerator,
        protected AgreementStatusService $statusService,
    ) {}

    public function index()
    {
        $stats = [
            'total' => Agreement::count(),
            'draft' => Agreement::where('status', AgreementStatus::DRAFT->value)->count(),
            'awaiting_approval' => Agreement::where('status', AgreementStatus::PENDING_APPROVAL->value)->count(),
            'awaiting_signature' => Agreement::whereIn('status', [
                AgreementStatus::SENT->value,
                AgreementStatus::CLIENT_REVIEW->value,
                AgreementStatus::CLIENT_SIGNED->value,
            ])->count(),
            'active' => Agreement::active()->count(),
            'expiring_30' => Agreement::active()
                ->whereBetween('expiry_date', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
                ->count(),
            'expired' => Agreement::where('status', AgreementStatus::EXPIRED->value)
                ->orWhere(function ($q) {
                    $q->active()->where('expiry_date', '<', now()->startOfDay());
                })
                ->count(),
        ];

        $expiringSoon = Agreement::active()
            ->with(['creator'])
            ->whereBetween('expiry_date', [now()->startOfDay(), now()->addDays(90)->endOfDay()])
            ->orderBy('expiry_date')
            ->take(6)
            ->get();

        $recent = Agreement::with(['creator'])
            ->latest()
            ->take(6)
            ->get();

        return view('contracts::index', compact('stats', 'expiringSoon', 'recent'));
    }

    public function agreementsIndex()
    {
        return view('contracts::agreements.index');
    }

    public function datatable(DataTables $dataTables)
    {
        $query = Agreement::query()
            ->with(['template'])
            ->withCount(['obligations']);

        return $dataTables->eloquent($query)
            ->addColumn('client', fn (Agreement $a) => $a->client_name)
            ->addColumn('type_label', fn (Agreement $a) => $a->typeEnum()->label())
            ->addColumn('status_badge', fn (Agreement $a) => view('contracts::partials.status-badge', ['agreement' => $a])->render())
            ->addColumn('actions', fn (Agreement $a) => view('contracts::partials.row-actions', ['agreement' => $a])->render())
            ->addColumn('actions_url', fn (Agreement $a) => route('contracts.agreements.show', $a))
            ->addColumn('effective_date_formatted', fn (Agreement $a) => $a->effective_date?->format('d M Y') ?? '—')
            ->editColumn('value_amount', fn (Agreement $a) => $a->value_amount !== null
                ? number_format((float) $a->value_amount, 2)
                : '—')
            ->editColumn('expiry_date', fn (Agreement $a) => $a->expiry_label)
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function create(Request $request)
    {
        $templates = AgreementTemplate::where('is_active', true)->orderBy('name')->get();
        $types = AgreementType::cases();
        $selectedTemplate = $request->integer('template') ? AgreementTemplate::with('clauses')->find($request->integer('template')) : null;

        return view('contracts::agreements.create', compact('templates', 'types', 'selectedTemplate'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $agreement = Agreement::create([
                'agreement_number' => $this->numberGenerator->next(),
                ...$data['agreement'],
                'status' => AgreementStatus::DRAFT->value,
                'current_version' => 1,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['parties'] as $party) {
                $agreement->parties()->create($party);
            }

            foreach ($data['clauses'] as $index => $clause) {
                $agreement->clauses()->create([
                    'title' => $clause['title'],
                    'content' => $clause['content'],
                    'sort_order' => $clause['sort_order'] ?? $index,
                    'version' => 1,
                ]);
            }

            foreach ($data['obligations'] as $obligation) {
                $agreement->obligations()->create($obligation);
            }

            $agreement->versions()->create([
                'version' => 1,
                'status' => AgreementStatus::DRAFT->value,
                'changes_summary' => 'Agreement created',
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()->route('contracts.agreements.index')
            ->with('success', 'Agreement created as a draft. Review it, then move it through the workflow.');
    }

    public function show(Agreement $agreement)
    {
        $agreement->load([
            'template',
            'creator',
            'approver',
            'parties',
            'signatures',
            'approvals.approver',
            'amendments.creator',
            'obligations',
            'documents.uploader',
            'clauses',
            'versions.creator',
        ]);

        $audits = $agreement->audits()
            ->with('user')
            ->latest()
            ->get();

        $workflow = $this->statusService->workflowSteps();
        $allowedTransitions = $this->statusService->allowedTargets($agreement);

        return view('contracts::agreements.show', compact('agreement', 'audits', 'workflow', 'allowedTransitions'));
    }

    public function edit(Agreement $agreement)
    {
        if (in_array($agreement->status, [AgreementStatus::EXECUTED->value, AgreementStatus::ACTIVE->value, AgreementStatus::EXPIRED->value, AgreementStatus::CANCELLED->value])) {
            return redirect()->route('contracts.agreements.show', $agreement)
                ->with('error', 'Executed or terminal agreements are locked. Use an amendment to change terms.');
        }

        $templates = AgreementTemplate::where('is_active', true)->orderBy('name')->get();
        $types = AgreementType::cases();

        $agreement->load(['parties', 'clauses']);

        return view('contracts::agreements.edit', compact('agreement', 'templates', 'types'));
    }

    public function update(Request $request, Agreement $agreement)
    {
        if (in_array($agreement->status, [AgreementStatus::EXECUTED->value, AgreementStatus::ACTIVE->value, AgreementStatus::EXPIRED->value, AgreementStatus::CANCELLED->value])) {
            return redirect()->route('contracts.agreements.show', $agreement)
                ->with('error', 'Executed or terminal agreements are locked.');
        }

        $data = $this->validated($request);

        DB::transaction(function () use ($data, $agreement) {
            $agreement->update($data['agreement']);

            $agreement->parties()->delete();
            foreach ($data['parties'] as $party) {
                $agreement->parties()->create($party);
            }

            $agreement->clauses()->delete();
            foreach ($data['clauses'] as $index => $clause) {
                $agreement->clauses()->create([
                    'title' => $clause['title'],
                    'content' => $clause['content'],
                    'sort_order' => $clause['sort_order'] ?? $index,
                    'version' => $agreement->current_version,
                ]);
            }

            $agreement->obligations()->delete();
            foreach ($data['obligations'] as $obligation) {
                $agreement->obligations()->create($obligation);
            }
        });

        return redirect()->route('contracts.agreements.show', $agreement)
            ->with('success', 'Agreement updated.');
    }

    public function destroy(Agreement $agreement)
    {
        if ($agreement->status !== AgreementStatus::DRAFT->value) {
            return back()->with('error', 'Only draft agreements can be deleted.');
        }

        $agreement->delete();

        return redirect()->route('contracts.agreements.index')
            ->with('success', 'Draft agreement deleted.');
    }

    public function transition(Request $request, Agreement $agreement)
    {
        $to = $request->string('to')->value();

        if (! in_array($to, $this->statusService->allowedTargets($agreement), true)) {
            return back()->with('error', "Transition from '".AgreementStatus::from($agreement->status)->label()."' to '".($to ?: 'unknown')."' is not allowed.");
        }

        if (in_array($to, [AgreementStatus::APPROVED->value, AgreementStatus::EXECUTED->value, AgreementStatus::ACTIVE->value], true)) {
            Gate::authorize('contracts.approve');
        } elseif (in_array($to, [AgreementStatus::CLIENT_SIGNED->value, AgreementStatus::HOTEL_SIGNED->value], true)) {
            Gate::authorize('contracts.sign');
        }

        $this->statusService->transition(
            $agreement,
            $to,
            $request->string('comments')->toString() ?: null
        );

        return back()->with('success', sprintf('Agreement %s moved to %s.', $agreement->agreement_number, AgreementStatus::from($to)->label()));
    }

    public function storeSignature(Request $request, Agreement $agreement)
    {
        Gate::authorize('contracts.sign');

        $validated = $request->validate([
            'party_role' => 'required|in:hotel,client',
            'party_name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'signature_type' => 'required|in:click,typed,draw,upload',
            'signature_data' => 'nullable|string',
            'verification' => 'nullable|string|max:255',
        ]);

        $signature = $agreement->signatures()->create([
            ...$validated,
            'signed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'hash' => hash('sha256', implode('|', [
                $agreement->agreement_number,
                $agreement->current_version,
                $validated['party_role'],
                $validated['party_name'],
                now()->toIso8601String(),
            ])),
        ]);

        $remaining = match ($validated['party_role']) {
            'client' => AgreementStatus::CLIENT_SIGNED->value,
            'hotel' => AgreementStatus::HOTEL_SIGNED->value,
        };

        if ($this->statusService->canTransition($agreement, $remaining)) {
            $this->statusService->transition($agreement, $remaining);
        }

        return back()->with('success', "{$validated['party_name']} signature recorded.");
    }

    public function storeObligation(Request $request, Agreement $agreement)
    {
        $validated = $request->validate([
            'obligation_type' => 'required|in:payment,service,delivery,entitlement,renewal,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'responsible_party' => 'required|in:hotel,client,other',
            'amount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
        ]);

        $agreement->obligations()->create($validated);

        return back()->with('success', 'Obligation added to the agreement.');
    }

    public function updateObligation(Request $request, Agreement $agreement, AgreementObligation $obligation)
    {
        if ($obligation->agreement_id !== $agreement->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,overdue,cancelled',
        ]);

        $obligation->update([
            ...$validated,
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
        ]);

        return back()->with('success', 'Obligation updated.');
    }

    public function storeAmendment(Request $request, Agreement $agreement)
    {
        if (! in_array($agreement->status, [AgreementStatus::ACTIVE->value, AgreementStatus::EXECUTED->value], true)) {
            return back()->with('error', 'Amendments can only be created on executed or active agreements.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'effective_date' => 'nullable|date',
        ]);

        $sequence = $agreement->amendments()->count() + 1;

        $agreement->amendments()->create([
            ...$validated,
            'amendment_no' => sprintf('%s-AMD-%03d', $agreement->agreement_number, $sequence),
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        if ($this->statusService->canTransition($agreement, AgreementStatus::AMENDED->value)) {
            $this->statusService->transition($agreement, AgreementStatus::AMENDED->value);
        }

        return back()->with('success', 'Amendment created. Once both parties sign, the agreement is changed.');
    }

    public function storeDocument(Request $request, Agreement $agreement)
    {
        $validated = $request->validate([
            'document' => 'required|file|max:10240',
        ]);

        $file = $validated['document'];
        $path = $file->store('agreements/'.$agreement->agreement_number, 'public');

        $agreement->documents()->create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Document attached to agreement.');
    }

    public function downloadDocument(Agreement $agreement, AgreementDocument $document)
    {
        if ($document->agreement_id !== $agreement->id) {
            abort(404);
        }

        return response()->download(storage_path('app/public/'.$document->path), $document->name);
    }

    public function downloadPdf(Agreement $agreement, AgreementPdfService $pdfService)
    {
        Gate::authorize('contracts.read');

        $agreement->audits()->create([
            'user_id' => auth()->id(),
            'event' => 'pdf_generated',
            'old_values' => [],
            'new_values' => ['document' => $pdfService->filename($agreement)],
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $filename = $pdfService->filename($agreement);

        return response($pdfService->render($agreement), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    protected function validated(Request $request): array
    {
        return $this->assemblePayload($request->validate([
            'template_select' => 'nullable|integer|exists:agreement_templates,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:'.implode(',', array_column(AgreementType::cases(), 'value')),
            'currency' => 'required|string|max:10',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:effective_date',
            'auto_renew' => 'nullable|boolean',
            'value_amount' => 'nullable|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'room_type' => 'nullable|string|max:255',
            'number_of_rooms' => 'nullable|integer|min:0',
            'rate_per_night' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'check_in_date' => 'nullable|date',
            'check_out_date' => 'nullable|date',
            'payment_terms' => 'nullable|string|max:255',
            'cancellation_policy' => 'nullable|string|max:255',
            'tax_applicable' => 'nullable|boolean',

            'hotel_legal_name' => 'required|string|max:255',
            'hotel_entity_type' => 'nullable|string|max:64',
            'hotel_registration_no' => 'nullable|string|max:255',
            'hotel_contact_person' => 'nullable|string|max:255',
            'hotel_position' => 'nullable|string|max:255',
            'hotel_address' => 'nullable|string|max:255',
            'hotel_email' => 'nullable|email|max:255',
            'hotel_phone' => 'nullable|string|max:64',

            'client_legal_name' => 'required|string|max:255',
            'client_entity_type' => 'nullable|string|max:64',
            'client_registration_no' => 'nullable|string|max:255',
            'client_contact_person' => 'nullable|string|max:255',
            'client_position' => 'nullable|string|max:255',
            'client_address' => 'nullable|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:64',

            'clauses' => 'nullable|array',
            'clauses.*.title' => 'required|string|max:255',
            'clauses.*.content' => 'required|string',
            'clauses.*.sort_order' => 'nullable|integer|min:0',

            'obligations' => 'nullable|array',
            'obligations.*.obligation_type' => 'required|in:payment,service,delivery,entitlement,renewal,other',
            'obligations.*.title' => 'required|string|max:255',
            'obligations.*.description' => 'nullable|string',
            'obligations.*.responsible_party' => 'required|in:hotel,client,other',
            'obligations.*.amount' => 'nullable|numeric|min:0',
            'obligations.*.due_date' => 'nullable|date',
        ], [
            'client_legal_name.required' => 'Enter the client (Party B) legal name.',
            'hotel_legal_name.required' => 'Enter the hotel (Party A) legal name.',
        ], [
            'clauses.*.title' => 'clause title',
            'clauses.*.content' => 'clause content',
            'obligations.*.title' => 'obligation title',
        ]));
    }

    protected function assemblePayload(array $validated): array
    {
        $agreement = [
            'template_id' => $validated['template_select'] ?? null,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'currency' => $validated['currency'],
            'effective_date' => $validated['effective_date'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'auto_renew' => $validated['auto_renew'] ?? false,
            'value_amount' => $validated['value_amount'] ?? null,
            'deposit_amount' => $validated['deposit_amount'] ?? null,
            'location' => $validated['location'] ?? null,
            'department' => $validated['department'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'commercial_terms' => [
                'room_type' => $validated['room_type'] ?? null,
                'number_of_rooms' => $validated['number_of_rooms'] ?? null,
                'rate_per_night' => $validated['rate_per_night'] ?? null,
                'discount_percent' => $validated['discount_percent'] ?? null,
                'check_in_date' => $validated['check_in_date'] ?? null,
                'check_out_date' => $validated['check_out_date'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'cancellation_policy' => $validated['cancellation_policy'] ?? null,
                'tax_applicable' => $validated['tax_applicable'] ?? false,
            ],
        ];

        $parties = [
            [
                'party_role' => 'hotel',
                'legal_name' => $validated['hotel_legal_name'],
                'entity_type' => $validated['hotel_entity_type'] ?? 'company',
                'registration_no' => $validated['hotel_registration_no'] ?? null,
                'contact_person' => $validated['hotel_contact_person'] ?? null,
                'position' => $validated['hotel_position'] ?? null,
                'address' => $validated['hotel_address'] ?? null,
                'email' => $validated['hotel_email'] ?? null,
                'phone' => $validated['hotel_phone'] ?? null,
            ],
            [
                'party_role' => 'client',
                'legal_name' => $validated['client_legal_name'],
                'entity_type' => $validated['client_entity_type'] ?? 'company',
                'registration_no' => $validated['client_registration_no'] ?? null,
                'contact_person' => $validated['client_contact_person'] ?? null,
                'position' => $validated['client_position'] ?? null,
                'address' => $validated['client_address'] ?? null,
                'email' => $validated['client_email'] ?? null,
                'phone' => $validated['client_phone'] ?? null,
                'is_primary' => true,
            ],
        ];

        $clauses = collect($validated['clauses'] ?? [])
            ->map(fn (array $clause, int $index) => [
                'title' => $clause['title'],
                'content' => $clause['content'],
                'sort_order' => $clause['sort_order'] ?? $index,
            ])
            ->values()
            ->all();

        $obligations = collect($validated['obligations'] ?? [])
            ->map(fn (array $obligation) => [
                'obligation_type' => $obligation['obligation_type'],
                'title' => $obligation['title'],
                'description' => $obligation['description'] ?? null,
                'responsible_party' => $obligation['responsible_party'],
                'amount' => $obligation['amount'] ?? null,
                'due_date' => $obligation['due_date'] ?? null,
            ])
            ->values()
            ->all();

        return compact('agreement', 'parties', 'clauses', 'obligations');
    }
}
