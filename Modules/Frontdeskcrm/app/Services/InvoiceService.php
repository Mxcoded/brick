<?php

namespace Modules\Frontdeskcrm\Services;

use Illuminate\Support\Facades\DB;
use Modules\Frontdeskcrm\Models\CreditNote;
use Modules\Frontdeskcrm\Models\Folio;
use Modules\Frontdeskcrm\Models\Invoice;
use Modules\Frontdeskcrm\Models\InvoiceItem;
use Modules\Frontdeskcrm\Models\Receipt;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationPayment;

class InvoiceService
{
    public function generateFromFolio(Folio $folio, array $options = [], ?int $createdBy = null): Invoice
    {
        return DB::transaction(function () use ($folio, $options, $createdBy) {
            $items = $folio->items;
            $subtotal = 0;
            $totalTax = 0;

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'registration_id' => $folio->registration_id,
                'folio_id' => $folio->id,
                'status' => 'draft',
                'issue_date' => $options['issue_date'] ?? now(),
                'due_date' => $options['due_date'] ?? now()->addDays(7),
                'currency' => $options['currency'] ?? 'NGN',
                'subtotal' => 0,
                'tax_total' => 0,
                'total' => 0,
                'paid_amount' => 0,
                'notes' => $options['notes'] ?? null,
                'created_by' => $createdBy,
            ]);

            foreach ($items as $item) {
                $lineTotal = (float) $item->amount;
                $lineTax = (float) $item->tax_amount;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'sourceable_type' => $item->sourceable_type,
                    'sourceable_id' => $item->sourceable_id,
                    'description' => $item->description ?? $item->charge_type,
                    'quantity' => 1,
                    'unit_price' => $lineTotal,
                    'tax_rate' => (float) $item->tax_rate,
                    'tax_amount' => $lineTax,
                    'total' => $lineTotal + $lineTax,
                ]);

                $subtotal += $lineTotal;
                $totalTax += $lineTax;
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_total' => $totalTax,
                'total' => $subtotal + $totalTax,
            ]);

            return $invoice;
        });
    }

    public function generateFromRegistration(Registration $registration, array $options = [], ?int $createdBy = null): Invoice
    {
        $folio = $registration->folios()->where('status', 'open')->first();

        if (! $folio) {
            throw new \RuntimeException("No open folio found for registration #{$registration->id}");
        }

        return $this->generateFromFolio($folio, $options, $createdBy);
    }

    public function issueInvoice(Invoice $invoice): Invoice
    {
        if ($invoice->status !== 'draft') {
            throw new \RuntimeException('Only draft invoices can be issued.');
        }

        $invoice->update(['status' => 'issued']);

        return $invoice;
    }

    public function voidInvoice(Invoice $invoice, string $reason): Invoice
    {
        $invoice->update([
            'status' => 'void',
            'notes' => ($invoice->notes ? $invoice->notes."\n" : '')."Voided: {$reason}",
        ]);

        return $invoice;
    }

    public function createCreditNote(Invoice $invoice, float $amount, string $reason, ?int $createdBy = null): CreditNote
    {
        return DB::transaction(function () use ($invoice, $amount, $reason, $createdBy) {
            $cn = CreditNote::create([
                'credit_note_number' => CreditNote::generateCreditNoteNumber(),
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'reason' => $reason,
                'issue_date' => now(),
                'created_by' => $createdBy,
            ]);

            $invoice->decrement('total', $amount);

            return $cn;
        });
    }

    public function generateReceipt(Invoice $invoice, RegistrationPayment $payment, ?int $printedBy = null): Receipt
    {
        return Receipt::create([
            'receipt_number' => Receipt::generateReceiptNumber(),
            'invoice_id' => $invoice->id,
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'receipted_at' => now(),
            'printed_by' => $printedBy,
        ]);
    }

    public function printReceipt(Receipt $receipt): Receipt
    {
        $receipt->increment('print_count');

        return $receipt;
    }
}
