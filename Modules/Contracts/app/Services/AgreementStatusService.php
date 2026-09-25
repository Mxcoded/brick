<?php

namespace Modules\Contracts\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Contracts\Enums\AgreementStatus;
use Modules\Contracts\Models\Agreement;

class AgreementStatusService
{
    /**
     * Allowed workflow transitions keyed by current status.
     */
    public const TRANSITIONS = [
        AgreementStatus::DRAFT->value => [
            AgreementStatus::INTERNAL_REVIEW->value,
            AgreementStatus::CANCELLED->value,
        ],
        AgreementStatus::INTERNAL_REVIEW->value => [
            AgreementStatus::DRAFT->value,
            AgreementStatus::PENDING_APPROVAL->value,
            AgreementStatus::CANCELLED->value,
        ],
        AgreementStatus::PENDING_APPROVAL->value => [
            AgreementStatus::DRAFT->value,
            AgreementStatus::APPROVED->value,
            AgreementStatus::CANCELLED->value,
        ],
        AgreementStatus::APPROVED->value => [
            AgreementStatus::SENT->value,
            AgreementStatus::CANCELLED->value,
        ],
        AgreementStatus::SENT->value => [
            AgreementStatus::CLIENT_REVIEW->value,
            AgreementStatus::CANCELLED->value,
        ],
        AgreementStatus::CLIENT_REVIEW->value => [
            AgreementStatus::CLIENT_SIGNED->value,
            AgreementStatus::SENT->value,
        ],
        AgreementStatus::CLIENT_SIGNED->value => [
            AgreementStatus::HOTEL_SIGNED->value,
        ],
        AgreementStatus::HOTEL_SIGNED->value => [
            AgreementStatus::EXECUTED->value,
        ],
        AgreementStatus::EXECUTED->value => [
            AgreementStatus::ACTIVE->value,
            AgreementStatus::AMENDED->value,
        ],
        AgreementStatus::ACTIVE->value => [
            AgreementStatus::AMENDED->value,
            AgreementStatus::EXPIRED->value,
            AgreementStatus::CANCELLED->value,
        ],
        AgreementStatus::AMENDED->value => [
            AgreementStatus::ACTIVE->value,
        ],
        AgreementStatus::EXPIRED->value => [
            AgreementStatus::ACTIVE->value,
        ],
        AgreementStatus::CANCELLED->value => [
            AgreementStatus::DRAFT->value,
        ],
    ];

    /**
     * Return the full ordered workflow for display purposes.
     */
    public function workflowSteps(): array
    {
        return [
            AgreementStatus::DRAFT->value,
            AgreementStatus::INTERNAL_REVIEW->value,
            AgreementStatus::PENDING_APPROVAL->value,
            AgreementStatus::APPROVED->value,
            AgreementStatus::SENT->value,
            AgreementStatus::CLIENT_REVIEW->value,
            AgreementStatus::CLIENT_SIGNED->value,
            AgreementStatus::HOTEL_SIGNED->value,
            AgreementStatus::EXECUTED->value,
            AgreementStatus::ACTIVE->value,
        ];
    }

    public function allowedTargets(Agreement $agreement): array
    {
        return self::TRANSITIONS[$agreement->status] ?? [];
    }

    public function canTransition(Agreement $agreement, string $to): bool
    {
        return in_array($to, $this->allowedTargets($agreement), true);
    }

    /**
     * Apply a status transition inside a transaction, attaching the
     * approving user and stamping an audit log entry.
     */
    public function transition(Agreement $agreement, string $to, ?string $comments = null, ?User $actor = null): Agreement
    {
        if (! $this->canTransition($agreement, $to)) {
            throw new \DomainException(
                "Cannot move agreement [{$agreement->agreement_number}] from '{$agreement->status}' to '{$to}'."
            );
        }

        DB::transaction(function () use ($agreement, $to, $comments, $actor) {
            $previous = $agreement->status;

            $agreement->status = $to;
            $agreement->fill([
                'approved_by' => $to === AgreementStatus::APPROVED->value
                    ? ($actor->id ?? $agreement->approved_by)
                    : $agreement->approved_by,
                'approved_at' => $to === AgreementStatus::APPROVED->value
                    ? now()
                    : $agreement->approved_at,
            ]);
            $agreement->save();

            if ($to === AgreementStatus::APPROVED->value && $comments !== null) {
                $agreement->approvals()->create([
                    'status' => 'approved',
                    'comments' => $comments,
                    'approved_by' => $actor?->id,
                ]);
            }

            $this->recordVersionSnapshot($agreement, $previous, $comments, $actor);
        });

        return $agreement->fresh();
    }

    protected function recordVersionSnapshot(Agreement $agreement, string $previous, ?string $comments, ?User $actor): void
    {
        $summary = sprintf(
            'Status changed: %s → %s%s',
            $previous,
            $agreement->status,
            $comments ? " — {$comments}" : ''
        );

        $agreement->versions()->updateOrCreate(
            ['agreement_id' => $agreement->id, 'version' => $agreement->current_version],
            [
                'status' => $agreement->status,
                'changes_summary' => $summary,
                'created_by' => $actor?->id ?? auth()->id(),
            ]
        );
    }
}
