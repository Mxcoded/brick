<?php

namespace Modules\Contracts\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Contracts\Enums\AgreementType;
use Modules\Contracts\Models\AgreementTemplate;
use Yajra\DataTables\DataTables;

class TemplateController extends Controller
{
    public function index()
    {
        return view('contracts::templates.index');
    }

    public function datatable(DataTables $dataTables)
    {
        return $dataTables->eloquent(AgreementTemplate::query())
            ->addColumn('type_label', fn (AgreementTemplate $t) => AgreementType::from($t->type)->label())
            ->addColumn('clause_count', fn (AgreementTemplate $t) => $t->clauses()->count())
            ->addColumn('status_badge', function (AgreementTemplate $t) {
                return $t->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('actions', fn (AgreementTemplate $t) => view('contracts::templates.row-actions', ['template' => $t])->render())
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function create()
    {
        $types = AgreementType::cases();

        return view('contracts::templates.create', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $this->validateTemplate($request);

        DB::transaction(function () use ($data) {
            $template = AgreementTemplate::create([
                ...$data['template'],
                'created_by' => auth()->id(),
            ]);

            foreach ($data['clauses'] as $index => $clause) {
                $template->clauses()->create([
                    'title' => $clause['title'],
                    'content' => $clause['content'],
                    'sort_order' => $clause['sort_order'] ?? $index,
                ]);
            }
        });

        return redirect()->route('contracts.templates.index')
            ->with('success', 'Agreement template created.');
    }

    public function show(AgreementTemplate $template)
    {
        $template->load('clauses');

        return view('contracts::templates.show', compact('template'));
    }

    public function edit(AgreementTemplate $template)
    {
        $types = AgreementType::cases();
        $template->load('clauses');

        return view('contracts::templates.edit', compact('template', 'types'));
    }

    public function update(Request $request, AgreementTemplate $template)
    {
        $data = $this->validateTemplate($request);

        DB::transaction(function () use ($data, $template) {
            $template->update([...$data['template'], 'updated_by' => auth()->id()]);

            $template->clauses()->delete();
            foreach ($data['clauses'] as $index => $clause) {
                $template->clauses()->create([
                    'title' => $clause['title'],
                    'content' => $clause['content'],
                    'sort_order' => $clause['sort_order'] ?? $index,
                ]);
            }
        });

        return redirect()->route('contracts.templates.show', $template)
            ->with('success', 'Template updated.');
    }

    public function destroy(AgreementTemplate $template)
    {
        if ($template->agreements()->exists()) {
            return back()->with('error', 'Cannot delete a template that is already used by agreements.');
        }

        $template->delete();

        return redirect()->route('contracts.templates.index')
            ->with('success', 'Template deleted.');
    }

    protected function validateTemplate(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:'.implode(',', array_column(AgreementType::cases(), 'value')),
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'clauses' => 'nullable|array',
            'clauses.*.title' => 'required|string|max:255',
            'clauses.*.content' => 'required|string',
            'clauses.*.sort_order' => 'nullable|integer|min:0',
        ]);

        return [
            'template' => [
                'name' => $validated['name'],
                'type' => $validated['type'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ],
            'clauses' => collect($validated['clauses'] ?? [])
                ->map(fn (array $clause, int $index) => [
                    'title' => $clause['title'],
                    'content' => $clause['content'],
                    'sort_order' => $clause['sort_order'] ?? $index,
                ])
                ->values()
                ->all(),
        ];
    }
}
