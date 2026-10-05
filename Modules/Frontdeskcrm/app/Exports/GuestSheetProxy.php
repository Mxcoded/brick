<?php

namespace Modules\Frontdeskcrm\Exports;

/**
 * Runs the inherited row handling on behalf of a multi-sheet import.
 *
 * Exists only to keep the counters and the rejection list on the object the
 * caller is holding: the rows are processed on this instance, but every
 * recorded outcome is forwarded to the parent, because that is the instance
 * whose getImportedCount()/getRejections() the report reads. Without the
 * forwarding of the row number too, each rejection would be stamped with the
 * parent's own count - which never advances - and every row in the report
 * would say row 0.
 */
class GuestSheetProxy extends BaseGuestsImport
{
    public function __construct(private readonly BaseGuestsImport $parent) {}

    protected function recordImported(): void
    {
        $this->parent->recordImported();
    }

    protected function reject(string $reason, ?string $detail = null): void
    {
        $this->parent->reject($reason, $detail);
    }

    protected function rowNumber(): int
    {
        return $this->parent->rowNumber();
    }

    protected function advanceRow(): void
    {
        $this->parent->advanceRow();
    }
}
