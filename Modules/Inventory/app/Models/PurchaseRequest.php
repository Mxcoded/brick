<?php

namespace Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'pr_number', 'requester_id', 'department', 'urgency', 'justification',
        'status', 'current_role', 'gl_code', 'cost_center', 'supplier_id',
        'invoice_path', 'pricing_details', 'procurement_notes',
    ];

    protected function casts(): array
    {
        return [
            'pricing_details' => 'array',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PurchaseRequestApproval::class);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'draft' => 'Draft',
            'pending_purchaser' => 'With Purchaser',
            'pending_gm' => 'With GM',
            'pending_finance' => 'With Finance',
            'pending_auditor' => 'With Auditor',
            'pending_ggm' => 'With GGM',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'flagged' => 'Flagged',
            default => ucfirst($status),
        };
    }

    public static function statusBadge(string $status): string
    {
        return match ($status) {
            'draft' => 'bg-secondary',
            'pending_purchaser', 'pending_gm', 'pending_finance', 'pending_auditor', 'pending_ggm' => 'bg-warning text-dark',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'flagged' => 'bg-info text-dark',
            default => 'bg-secondary',
        };
    }

    public static function urgencyBadge(string $urgency): string
    {
        return match ($urgency) {
            'urgent' => 'bg-danger',
            'emergency' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }
}
