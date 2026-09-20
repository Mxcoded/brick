<?php

namespace Modules\Contracts\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Contracts\Models\Agreement;

class AgreementPdfService
{
    /**
     * Render the agreement to a printable, branded PDF.
     */
    public function render(Agreement $agreement): string
    {
        $agreement->loadMissing([
            'template',
            'creator',
            'parties',
            'signatures',
            'clauses',
            'obligations',
        ]);

        $pdf = Pdf::loadView('contracts::pdfs.agreement', [
            'agreement' => $agreement,
            'generatedBy' => auth()->user()?->name ?? 'System',
        ])
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        return $pdf->output();
    }

    public function filename(Agreement $agreement): string
    {
        return 'agreement-'.$agreement->agreement_number.'.pdf';
    }
}
