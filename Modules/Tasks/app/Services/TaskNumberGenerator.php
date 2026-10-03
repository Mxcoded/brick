<?php

namespace Modules\Tasks\Services;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskSequence;

/**
 * Issues task references.
 *
 * Kept separate from TaskService because the recurrence service needs it too,
 * and routing that through TaskService would make the two mutually dependent.
 */
class TaskNumberGenerator
{
    /**
     * The next reference for a task raised on the given day, e.g. TASK-250926-0007.
     *
     * References used to come from count()+1, which handed the same number to
     * two staff creating a task at the same moment, and handed it out again
     * once the task was deleted. The counter is persisted and its row is locked
     * for the duration, so a number is issued exactly once.
     */
    public function next(Carbon $on): string
    {
        $prefix = sprintf('TASK-%s-', $on->format('dmy'));

        return DB::transaction(function () use ($prefix): string {
            $this->ensureRowExists($prefix);

            $sequence = TaskSequence::where('prefix', $prefix)
                ->lockForUpdate()
                ->firstOrFail();

            $candidate = $this->firstFreeNumber($sequence, $prefix);

            $sequence->next_number = (int) substr($candidate, -4);
            $sequence->save();

            return $candidate;
        });
    }

    /**
     * Two people can reach this on the same day at the same moment, so the
     * insert may lose the race on the unique prefix. Losing that race is fine;
     * the caller then locks the row the winner created.
     */
    protected function ensureRowExists(string $prefix): void
    {
        try {
            TaskSequence::create(['prefix' => $prefix, 'next_number' => 0]);
        } catch (QueryException $e) {
            // Another request created the row between the check and the insert.
        }
    }

    /**
     * Walk forward from the counter until the reference is genuinely unused.
     * Only reachable for references issued before this table existed, or if a
     * task was created with a hand-picked number.
     */
    protected function firstFreeNumber(TaskSequence $sequence, string $prefix): string
    {
        do {
            $sequence->next_number++;
            $candidate = $prefix.str_pad((string) $sequence->next_number, 4, '0', STR_PAD_LEFT);
        } while (Task::where('task_number', $candidate)->exists());

        return $candidate;
    }
}
