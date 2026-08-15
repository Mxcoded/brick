<?php

namespace Modules\Frontdeskcrm\Exports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Frontdeskcrm\Models\Guest;

class GuestsImport extends BaseGuestsImport implements ToModel, WithHeadingRow, WithValidation
{
    // No WithMultipleSheets - works for CSV and single-sheet Excel
    // For multi-sheet Excel, use GuestsExcelImport
}