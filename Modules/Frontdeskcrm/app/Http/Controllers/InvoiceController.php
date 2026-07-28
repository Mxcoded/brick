<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Frontdeskcrm\Models\CreditNote;
use Modules\Frontdeskcrm\Models\Folio;
use Modules\Frontdeskcrm\Models\Invoice;
use Modules\Frontdeskcrm\Models\Receipt;
use Modules\Frontdeskcrm\Services\InvoiceService;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request)
    {
        $query = Invoice::with(['registration', 'registration.guest']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('issue_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('issue_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('frontdeskcrm::invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['items', 'registration', 'registration.guest', 'creditNotes', 'receipts']);

        return view('frontdeskcrm::invoices.show', compact('invoice'));
    }

    public function createFromFolio(Request $request, Folio $folio)
    {
        $invoice = $this->invoiceService->generateFromFolio($folio, $request->all(), auth()->id());

        return redirect()->route('frontdesk.invoices.show', $invoice)
            ->with('success', 'Invoice generated from folio.');
    }

    public function issue(Invoice $invoice)
    {
        $this->invoiceService->issueInvoice($invoice);

        return redirect()->route('frontdesk.invoices.show', $invoice)
            ->with('success', 'Invoice issued.');
    }

    public function void(Request $request, Invoice $invoice)
    {
        $validated = $request->validate(['reason' => 'required|string']);
        $this->invoiceService->voidInvoice($invoice, $validated['reason']);

        return redirect()->route('frontdesk.invoices.show', $invoice)
            ->with('success', 'Invoice voided.');
    }

    public function creditNote(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|lte:'.$invoice->total,
            'reason' => 'required|string|max:255',
        ]);

        $this->invoiceService->createCreditNote($invoice, $validated['amount'], $validated['reason'], auth()->id());

        return redirect()->route('frontdesk.invoices.show', $invoice)
            ->with('success', 'Credit note created.');
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['items', 'registration', 'registration.guest', 'folio', 'creditNotes', 'receipts']);

        $pdf = Pdf::loadView('frontdeskcrm::pdf.invoice', compact('invoice'))
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    public function receiptPdf(Receipt $receipt)
    {
        $receipt->load(['invoice', 'payment', 'payment.registration', 'payment.registration.guest', 'printedBy']);

        $pdf = Pdf::loadView('frontdeskcrm::pdf.receipt', compact('receipt'))
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        $receipt->increment('print_count');
        $receipt->update(['printed_by' => auth()->id()]);

        return $pdf->download("receipt-{$receipt->receipt_number}.pdf");
    }

    public function creditNotePdf(CreditNote $creditNote)
    {
        $creditNote->load(['invoice', 'invoice.registration', 'invoice.registration.guest', 'createdBy']);

        $pdf = Pdf::loadView('frontdeskcrm::pdf.credit-note', compact('creditNote'))
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        return $pdf->download("credit-note-{$creditNote->credit_note_number}.pdf");
    }
}
