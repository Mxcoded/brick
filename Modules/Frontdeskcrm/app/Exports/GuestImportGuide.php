<?php

namespace Modules\Frontdeskcrm\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GuestImportGuide implements Export, WithMultipleSheets
{
    /**
     * Downloadable Excel guide for bulk-importing guest profiles.
     * Sheet 1 is a fillable template; Sheet 2 explains each column.
     */
    public function sheets(): array
    {
        return [
            new GuestImportTemplateSheet,
            new GuestImportInstructionsSheet,
        ];
    }
}
