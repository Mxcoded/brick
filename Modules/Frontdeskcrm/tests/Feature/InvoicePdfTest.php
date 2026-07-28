<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Frontdeskcrm\Models\CreditNote;
use Modules\Frontdeskcrm\Models\Folio;
use Modules\Frontdeskcrm\Models\FolioItem;
use Modules\Frontdeskcrm\Models\Invoice;
use Modules\Frontdeskcrm\Models\InvoiceItem;
use Modules\Frontdeskcrm\Models\Receipt;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationPayment;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $this->user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard', 'guard_name' => 'web']);
        $this->user->givePermissionTo($permission);
    }

    private function createInvoiceWithItems(): Invoice
    {
        $roomType = RoomType::create([
            'name' => 'Deluxe',
            'slug' => 'deluxe-pdf-'.uniqid(),
            'price' => 25000,
            'capacity' => 2,
        ]);

        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => '101',
            'status' => 'occupied',
        ]);

        $registration = Registration::create([
            'full_name' => 'John Guest',
            'email' => 'john@test.com',
            'contact_number' => '+2348012345678',
            'room_allocation' => '101',
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'check_in' => now()->subDay(),
            'check_out' => now()->addDays(2),
            'no_of_nights' => 3,
            'room_rate' => 25000,
            'stay_status' => 'checked_in',
            'finalized_by_agent_id' => $this->user->id,
            'currency' => 'NGN',
        ]);

        $folio = Folio::create([
            'registration_id' => $registration->id,
            'folio_number' => Folio::generateFolioNumber(),
            'folio_name' => 'Main Folio',
            'status' => 'open',
            'balance' => 75000,
        ]);

        FolioItem::create([
            'folio_id' => $folio->id,
            'charge_type' => 'room',
            'description' => 'Room Charge - Night 1',
            'amount' => 25000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'post_date' => now()->subDay(),
        ]);

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'registration_id' => $registration->id,
            'folio_id' => $folio->id,
            'status' => 'issued',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'currency' => 'NGN',
            'subtotal' => 75000,
            'tax_total' => 0,
            'total' => 75000,
            'paid_amount' => 0,
            'created_by' => $this->user->id,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Room Charge (3 nights)',
            'quantity' => 1,
            'unit_price' => 75000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total' => 75000,
        ]);

        return $invoice;
    }

    private function createReceipt(Invoice $invoice): Receipt
    {
        $registration = $invoice->registration;

        $payment = RegistrationPayment::create([
            'registration_id' => $registration->id,
            'amount' => 50000,
            'payment_method' => 'cash',
            'received_by' => $this->user->id,
            'payment_date' => now(),
        ]);

        return Receipt::create([
            'receipt_number' => Receipt::generateReceiptNumber(),
            'invoice_id' => $invoice->id,
            'payment_id' => $payment->id,
            'amount' => 50000,
            'payment_method' => 'cash',
            'receipted_at' => now(),
            'printed_by' => null,
            'print_count' => 0,
        ]);
    }

    private function assertIsPdfResponse($response, string $expectedFilename): void
    {
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        $content = $response->getContent();
        $this->assertNotEmpty($content);
        $this->assertStringStartsWith('%PDF-', $content);
    }

    // --- Invoice PDF tests ---

    public function test_invoice_pdf_returns_valid_pdf(): void
    {
        $invoice = $this->createInvoiceWithItems();

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.invoices.pdf', $invoice));

        $this->assertIsPdfResponse($response, "invoice-{$invoice->invoice_number}.pdf");
    }

    public function test_invoice_pdf_contains_invoice_number_in_filename(): void
    {
        $invoice = $this->createInvoiceWithItems();

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.invoices.pdf', $invoice));

        $response->assertStatus(200);
        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString($invoice->invoice_number, $disposition);
    }

    public function test_invoice_pdf_file_size_is_reasonable(): void
    {
        $invoice = $this->createInvoiceWithItems();

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.invoices.pdf', $invoice));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertGreaterThan(500, strlen($content));
        $this->assertLessThan(5_000_000, strlen($content));
    }

    public function test_invoice_pdf_with_tax_items(): void
    {
        $roomType = RoomType::create([
            'name' => 'Suite',
            'slug' => 'suite-pdf-'.uniqid(),
            'price' => 50000,
            'capacity' => 2,
        ]);

        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => '201',
            'status' => 'occupied',
        ]);

        $registration = Registration::create([
            'full_name' => 'Jane Taxpayer',
            'email' => 'jane@test.com',
            'contact_number' => '+2348098765432',
            'room_allocation' => '201',
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'check_in' => now()->subDay(),
            'check_out' => now()->addDays(2),
            'no_of_nights' => 2,
            'room_rate' => 50000,
            'stay_status' => 'checked_in',
            'finalized_by_agent_id' => $this->user->id,
            'currency' => 'NGN',
        ]);

        $folio = Folio::create([
            'registration_id' => $registration->id,
            'folio_number' => Folio::generateFolioNumber(),
            'folio_name' => 'Main Folio',
            'status' => 'open',
            'balance' => 100000,
        ]);

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'registration_id' => $registration->id,
            'folio_id' => $folio->id,
            'status' => 'issued',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'currency' => 'NGN',
            'subtotal' => 100000,
            'tax_total' => 7500,
            'total' => 107500,
            'paid_amount' => 0,
            'created_by' => $this->user->id,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Room Charge (2 nights)',
            'quantity' => 1,
            'unit_price' => 100000,
            'tax_rate' => 7.5,
            'tax_amount' => 7500,
            'total' => 107500,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.invoices.pdf', $invoice));

        $this->assertIsPdfResponse($response, "invoice-{$invoice->invoice_number}.pdf");
    }

    public function test_draft_invoice_can_be_downloaded_as_pdf(): void
    {
        $invoice = $this->createInvoiceWithItems();
        $invoice->update(['status' => 'draft']);

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.invoices.pdf', $invoice));

        $this->assertIsPdfResponse($response, "invoice-{$invoice->invoice_number}.pdf");
    }

    // --- Receipt PDF tests ---

    public function test_receipt_pdf_returns_valid_pdf(): void
    {
        $invoice = $this->createInvoiceWithItems();
        $receipt = $this->createReceipt($invoice);

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.invoices.receipt.pdf', $receipt));

        $this->assertIsPdfResponse($response, "receipt-{$receipt->receipt_number}.pdf");
    }

    public function test_receipt_pdf_increments_print_count(): void
    {
        $invoice = $this->createInvoiceWithItems();
        $receipt = $this->createReceipt($invoice);

        $this->assertEquals(0, $receipt->print_count);
        $this->assertNull($receipt->printed_by);

        $this->actingAs($this->user)
            ->get(route('frontdesk.invoices.receipt.pdf', $receipt));

        $receipt->refresh();
        $this->assertEquals(1, $receipt->print_count);
        $this->assertEquals($this->user->id, $receipt->printed_by);
    }

    public function test_receipt_pdf_print_count_increments_on_each_download(): void
    {
        $invoice = $this->createInvoiceWithItems();
        $receipt = $this->createReceipt($invoice);

        $this->actingAs($this->user)
            ->get(route('frontdesk.invoices.receipt.pdf', $receipt));

        $this->actingAs($this->user)
            ->get(route('frontdesk.invoices.receipt.pdf', $receipt));

        $this->actingAs($this->user)
            ->get(route('frontdesk.invoices.receipt.pdf', $receipt));

        $receipt->refresh();
        $this->assertEquals(3, $receipt->print_count);
    }

    public function test_receipt_pdf_contains_filename(): void
    {
        $invoice = $this->createInvoiceWithItems();
        $receipt = $this->createReceipt($invoice);

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.invoices.receipt.pdf', $receipt));

        $response->assertStatus(200);
        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString($receipt->receipt_number, $disposition);
    }

    // --- Credit Note PDF tests ---

    public function test_credit_note_pdf_returns_valid_pdf(): void
    {
        $invoice = $this->createInvoiceWithItems();

        $creditNote = CreditNote::create([
            'credit_note_number' => CreditNote::generateCreditNoteNumber(),
            'invoice_id' => $invoice->id,
            'amount' => 10000,
            'reason' => 'Service issue compensation',
            'issue_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.invoices.credit-note.pdf', $creditNote));

        $this->assertIsPdfResponse($response, "credit-note-{$creditNote->credit_note_number}.pdf");
    }

    public function test_credit_note_pdf_contains_filename(): void
    {
        $invoice = $this->createInvoiceWithItems();

        $creditNote = CreditNote::create([
            'credit_note_number' => CreditNote::generateCreditNoteNumber(),
            'invoice_id' => $invoice->id,
            'amount' => 5000,
            'reason' => 'Billing adjustment',
            'issue_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.invoices.credit-note.pdf', $creditNote));

        $response->assertStatus(200);
        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString($creditNote->credit_note_number, $disposition);
    }

    // --- Route tests ---

    public function test_invoice_pdf_route_is_defined(): void
    {
        $this->assertNotEmpty(route('frontdesk.invoices.pdf', 1));
    }

    public function test_receipt_pdf_route_is_defined(): void
    {
        $this->assertNotEmpty(route('frontdesk.invoices.receipt.pdf', 1));
    }

    public function test_credit_note_pdf_route_is_defined(): void
    {
        $this->assertNotEmpty(route('frontdesk.invoices.credit-note.pdf', 1));
    }
}
