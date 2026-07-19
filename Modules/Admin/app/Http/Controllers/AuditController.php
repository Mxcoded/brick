<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Auditing\Models\Audit;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Audit::with('user')->latest();

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('model')) {
            $query->where('auditable_type', $request->model);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date.' 23:59:59');
        }

        $audits = $query->paginate(25)->withQueryString();

        $auditableModels = Audit::select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type');

        return view('admin::audit.index', compact('audits', 'auditableModels'));
    }

    public function show($id)
    {
        $audit = Audit::with('user')->findOrFail($id);

        $oldValues = $audit->old_values ?? [];
        $newValues = $audit->new_values ?? [];

        $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
        sort($allKeys);

        $changes = [];
        foreach ($allKeys as $key) {
            $changes[$key] = [
                'old' => $oldValues[$key] ?? null,
                'new' => $newValues[$key] ?? null,
            ];
        }

        $modelName = class_basename($audit->auditable_type);

        return view('admin::audit.show', compact('audit', 'changes', 'modelName'));
    }

    public function modelHistory(Request $request, string $model, int $id)
    {
        $fullModel = $this->resolveModel($model);

        $query = Audit::with('user')
            ->where('auditable_type', $fullModel)
            ->where('auditable_id', $id)
            ->latest();

        $audits = $query->paginate(25)->withQueryString();

        $modelName = class_basename($fullModel);

        return view('admin::audit.model-history', compact('audits', 'modelName', 'id'));
    }

    private function resolveModel(string $shortName): string
    {
        $map = [
            'registration' => 'Modules\Frontdeskcrm\Models\Registration',
            'guest' => 'Modules\Frontdeskcrm\Models\Guest',
            'order' => 'Modules\Restaurant\Models\Order',
            'order-item' => 'Modules\Restaurant\Models\OrderItem',
            'menu-item' => 'Modules\Restaurant\Models\MenuItem',
            'table' => 'Modules\Restaurant\Models\Table',
            'payment' => 'Modules\Finance\Models\Payment',
            'employee' => 'Modules\Staff\Models\Employee',
            'room' => 'App\Models\Room',
            'room-unit' => 'App\Models\RoomUnit',
            'property' => 'App\Models\Property',
            'folio-charge' => 'Modules\Frontdeskcrm\Models\FolioCharge',
        ];

        return $map[$shortName] ?? $shortName;
    }
}
