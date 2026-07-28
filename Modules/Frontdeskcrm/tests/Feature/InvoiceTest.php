<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationPayment;
use Modules\Frontdeskcrm\Services\FolioService;
use Modules\Frontdeskcrm\Services\InvoiceService;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use DatabaseTransactions;

    protected InvoiceService $invoiceService;

    protected FolioService $folioService;

    protected User $user;

    protected Registration $registration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoiceService = app(InvoiceService::class);
        $this->folioService = app(FolioService::class);
        $this->user = User::factory()->create();
        Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard', 'guard_name' => 'web']);
        $this->user->givePermissionTo('access_frontdesk_dashboard');
        $this->registration = $this->makeRegistration();
    }

    private function makeRegistration(): Registration
    {
        $roomType = RoomType::create([
            'name' => 'Invoice Test Room',
            'slug' => 'inv-test-'.uniqid(),
            'price' => 50000,
            'capacity' => 2,
            'is_active' => true,
        ]);
        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => 'INV-'.rand(100, 999),
            'floor' => 1,
            'status' => 'occupied',
        ]);
        $guest = Guest::create([
            'full_name' => 'Invoice Guest',
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => 'inv'.uniqid().'@example.com',
            'nationality' => 'Nigerian',
            'gender' => 'male',
        ]);

        return Registration::create([
            'guest_id' => $guest->id,
            'full_name' => $guest->full_name,
            'contact_number' => $guest->contact_number,
            'email' => $guest->email,
            'nationality' => $guest->nationality,
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'room_rate' => 50000,
            'check_in' => Carbon::today()->subDays(2),
            'check_out' => Carbon::today()->addDays(3),
            'stay_status' => 'checked_in',
            'no_of_nights' => 5,
            'total_amount' => 250000,
        ]);
    }

    public function test_generates_invoice_from_folio(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);
        $this->folioService->postCharge($folio, [
            'charge_type' => 'room',
            'description' => 'Room charge',
            'amount' => 50000,
            'post_date' => Carbon::today(),
        ], $this->user->id);

        $invoice = $this->invoiceService->generateFromFolio($folio, [], $this->user->id);

        $this->assertNotNull($invoice);
        $this->assertEquals('draft', $invoice->status);
        $this->assertEquals(50000, $invoice->subtotal);
        $this->assertEquals(50000, $invoice->total);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
        $this->assertEquals(1, $invoice->items()->count());
    }

    public function test_issues_invoice(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);
        $this->folioService->postCharge($folio, [
            'charge_type' => 'room',
            'description' => 'Room charge',
            'amount' => 35000,
            'post_date' => Carbon::today(),
        ], $this->user->id);

        $invoice = $this->invoiceService->generateFromFolio($folio, [], $this->user->id);
        $invoice = $this->invoiceService->issueInvoice($invoice);

        $this->assertEquals('issued', $invoice->status);
    }

    public function test_voids_invoice(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);
        $this->folioService->postCharge($folio, [
            'charge_type' => 'room',
            'description' => 'Room charge',
            'amount' => 25000,
            'post_date' => Carbon::today(),
        ], $this->user->id);

        $invoice = $this->invoiceService->generateFromFolio($folio, [], $this->user->id);
        $invoice = $this->invoiceService->voidInvoice($invoice, 'Test void');

        $this->assertEquals('void', $invoice->status);
    }

    public function test_creates_credit_note(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);
        $this->folioService->postCharge($folio, [
            'charge_type' => 'room',
            'description' => 'Room charge',
            'amount' => 100000,
            'post_date' => Carbon::today(),
        ], $this->user->id);

        $invoice = $this->invoiceService->generateFromFolio($folio, [], $this->user->id);
        $cn = $this->invoiceService->createCreditNote($invoice, 20000, 'Partial refund', $this->user->id);

        $this->assertNotNull($cn);
        $this->assertEquals(20000, $cn->amount);
        $this->assertStringStartsWith('CN-', $cn->credit_note_number);
        $invoice->refresh();
        $this->assertEquals(80000, $invoice->total);
    }

    public function test_generates_receipt(): void
    {
        $payment = RegistrationPayment::create([
            'registration_id' => $this->registration->id,
            'amount' => 50000,
            'payment_method' => 'cash',
            'payment_date' => Carbon::today(),
            'received_by' => $this->user->id,
        ]);

        $folio = $this->folioService->ensureFolio($this->registration);
        $this->folioService->postCharge($folio, [
            'charge_type' => 'room',
            'description' => 'Room charge',
            'amount' => 50000,
            'post_date' => Carbon::today(),
        ], $this->user->id);

        $invoice = $this->invoiceService->generateFromFolio($folio, [], $this->user->id);
        $receipt = $this->invoiceService->generateReceipt($invoice, $payment, $this->user->id);

        $this->assertNotNull($receipt);
        $this->assertEquals(50000, $receipt->amount);
        $this->assertStringStartsWith('RCT-', $receipt->receipt_number);
    }

    public function test_invoice_page_loads(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);
        $this->folioService->postCharge($folio, [
            'charge_type' => 'room',
            'description' => 'Room charge',
            'amount' => 75000,
            'post_date' => Carbon::today(),
        ], $this->user->id);

        $invoice = $this->invoiceService->generateFromFolio($folio, [], $this->user->id);

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.invoices.show', $invoice));

        $response->assertOk();
        $response->assertSee($invoice->invoice_number);
        $response->assertSee('75,000');
    }
}
