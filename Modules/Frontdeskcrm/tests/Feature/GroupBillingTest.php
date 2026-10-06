<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationCharge;
use Modules\Frontdeskcrm\Models\RegistrationPayment;
use Modules\Frontdeskcrm\Services\FolioService;
use Modules\Website\Models\Booking;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The grouping and billing fixes.
 *
 * Three silent bugs lived in the group flow: `addMember()` created the member
 * registration with `guest_id => null` (so group members never entered the
 * guest databank, unlike every member created from the public site or a
 * walk-in); `storeWalkin()` ignored the `is_group_lead` / `group_members`
 * fields its own request class validated; and the balance screens counted the
 * synced online deposit twice and ignored member bills entirely, which made a
 * consolidated group's outstanding debt look lower than it was.
 */
class GroupBillingTest extends TestCase
{
    use DatabaseTransactions;

    private User $agent;

    private FolioService $folioService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->agent = User::factory()->create();
        Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard', 'guard_name' => 'web']);
        $this->agent->givePermissionTo('access_frontdesk_dashboard');

        $this->folioService = app(FolioService::class);
    }

    private function makeGuest(string $name, ?string $phone = null): Guest
    {
        return Guest::create([
            'full_name' => $name,
            'contact_number' => $phone ?? '080'.rand(10000000, 99999999),
            'email' => str()->lower(str()->slug($name)).uniqid().'@example.com',
        ]);
    }

    private function makeLead(?Guest $guest = null): Registration
    {
        return Registration::create([
            'guest_id' => ($guest ?? $this->makeGuest('Group Lead'))->id,
            'full_name' => 'Group Lead',
            'contact_number' => '080'.rand(10000000, 99999999),
            'check_in' => Carbon::today()->toDateString(),
            'check_out' => Carbon::today()->addDays(2)->toDateString(),
            'no_of_nights' => 2,
            'no_of_guests' => 2,
            'room_rate' => 40000,
            'billing_type' => 'consolidate',
            'stay_status' => 'checked_in',
            'is_group_lead' => true,
        ]);
    }

    private function grantAccess(): static
    {
        $this->actingAs($this->agent);

        return $this;
    }

    // =====================================================================
    // addMember()
    // =====================================================================

    #[Test]
    public function add_member_creates_and_links_a_guest_record(): void
    {
        $lead = $this->makeLead();
        $phone = '080'.rand(10000000, 99999999);

        $this->grantAccess()
            ->post(route('frontdesk.registrations.add-member', $lead), [
                'full_name' => 'Member One',
                'contact_number' => $phone,
            ])
            ->assertSessionHas('success');

        $member = Registration::where('parent_registration_id', $lead->id)->first();

        $this->assertNotNull($member, 'A member registration should be created.');
        $this->assertNotNull($member->guest_id, 'The member must be linked to a guest record.');
        $this->assertSame('Member One', $member->guest->full_name);
        $this->assertSame($phone, $member->guest->contact_number);
    }

    #[Test]
    public function add_member_reuses_an_existing_guest_by_phone(): void
    {
        $lead = $this->makeLead();
        $existing = $this->makeGuest('Original Name');

        $this->grantAccess()
            ->post(route('frontdesk.registrations.add-member', $lead), [
                'full_name' => 'Updated Name',
                'contact_number' => $existing->contact_number,
            ])
            ->assertSessionHas('success');

        $member = Registration::where('parent_registration_id', $lead->id)->first();

        $this->assertSame($existing->id, $member->guest_id);
        $this->assertSame('Updated Name', $existing->refresh()->full_name, 'The existing guest name is refreshed like the public-site flow.');
        $this->assertSame(1, Guest::where('contact_number', $existing->contact_number)->count(), 'No duplicate guest may be created.');
    }

    #[Test]
    public function add_member_requires_a_contact_number(): void
    {
        $lead = $this->makeLead();

        $this->grantAccess()
            ->post(route('frontdesk.registrations.add-member', $lead), [
                'full_name' => 'No Phone Member',
            ])
            ->assertSessionHasErrors('contact_number');

        $this->assertSame(0, Registration::where('parent_registration_id', $lead->id)->count());
    }

    // =====================================================================
    // storeWalkin() group fields
    // =====================================================================

    private function walkinPayload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Walk-in Lead',
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => 'walkin'.uniqid().'@example.com',
            'gender' => 'male',
            'check_in' => Carbon::today()->toDateString(),
            'check_out' => Carbon::today()->addDays(2)->toDateString(),
            'no_of_guests' => 2,
            'room_rate' => 40000,
            'billing_type' => 'consolidate',
            'payment_method' => 'cash',
        ], $overrides);
    }

    #[Test]
    public function store_walkin_persists_group_members(): void
    {
        $memberPhone = '080'.rand(10000000, 99999999);

        $this->grantAccess()
            ->post(route('frontdesk.registrations.storeWalkin'), $this->walkinPayload([
                'is_group_lead' => 1,
                'group_members' => [
                    [
                        'full_name' => 'Walk-in Member',
                        'contact_number' => $memberPhone,
                        'email' => 'member'.uniqid().'@example.com',
                    ],
                ],
            ]))
            ->assertSessionHas('success');

        $lead = Registration::where('full_name', 'Walk-in Lead')->first();

        $this->assertNotNull($lead);
        $this->assertTrue($lead->is_group_lead, 'The lead flag must be persisted.');

        $member = Registration::where('parent_registration_id', $lead->id)->first();

        $this->assertNotNull($member);
        $this->assertNotNull($member->guest_id, 'Walk-in members must live in the guest databank.');
        $this->assertSame('Walk-in Member', $member->guest->full_name);
        $this->assertSame($memberPhone, $member->guest->contact_number);
    }

    #[Test]
    public function store_walkin_marks_the_lead_when_members_are_present(): void
    {
        $this->grantAccess()
            ->post(route('frontdesk.registrations.storeWalkin'), $this->walkinPayload([
                'group_members' => [
                    ['full_name' => 'Implicit Member', 'contact_number' => '080'.rand(10000000, 99999999)],
                ],
            ]))
            ->assertSessionHas('success');

        $lead = Registration::where('full_name', 'Walk-in Lead')->first();

        $this->assertTrue($lead->is_group_lead, 'A walk-in with members is a group lead even without the checkbox.');
    }

    #[Test]
    public function store_walkin_accepts_only_the_canonical_billing_vocabulary(): void
    {
        $this->grantAccess()
            ->post(route('frontdesk.registrations.storeWalkin'), $this->walkinPayload([
                'billing_type' => 'split',
            ]))
            ->assertSessionHasErrors('billing_type');

        $this->grantAccess()
            ->post(route('frontdesk.registrations.storeWalkin'), $this->walkinPayload([
                'billing_type' => 'individual',
            ]))
            ->assertSessionHasNoErrors('billing_type');

        $lead = Registration::where('full_name', 'Walk-in Lead')->first();

        $this->assertSame('individual', $lead->billing_type);
    }

    // =====================================================================
    // FolioService::getOutstandingBalance()
    // =====================================================================

    #[Test]
    public function outstanding_balance_counts_folio_charges_once(): void
    {
        $lead = $this->makeLead();
        $folio = $this->folioService->ensureFolio($lead);

        // Night audit and incidental charges both write a RegistrationCharge and
        // a FolioItem, and the folio balance is the sum of those items.
        $charge = RegistrationCharge::create([
            'registration_id' => $lead->id,
            'folio_id' => $folio->id,
            'charge_type' => 'room',
            'description' => 'Room charge for today',
            'amount' => 40000,
            'charge_date' => Carbon::today(),

        ]);
        $this->folioService->postCharge($folio, [
            'charge_type' => 'room',
            'description' => 'Room charge for today',
            'amount' => 40000,
            'sourceable_type' => RegistrationCharge::class,
            'sourceable_id' => $charge->id,
        ]);

        RegistrationPayment::create([
            'registration_id' => $lead->id,
            'amount' => 10000,
            'payment_method' => 'Cash',
            'payment_date' => Carbon::today(),
        ]);

        $settled = $this->folioService->getOutstandingBalance($lead);

        $this->assertSame(30000.0, $settled, 'Only the paid folio charges may count toward the balance.');
        $this->assertSame(40000.0, (float) $lead->folios->first()->balance);
    }

    // =====================================================================
    // Show page financial summary
    // =====================================================================

    #[Test]
    public function show_page_reports_consolidated_group_figures(): void
    {
        $lead = $this->makeLead();
        $lead->update([
            'no_of_nights' => 2,
            'total_amount' => 120000, // own 80000 + member 40000, as finalize() writes
        ]);

        $member = $lead->children()->create([
            'guest_id' => $this->makeGuest('Group Member')->id,
            'full_name' => 'Group Member',
            'contact_number' => '080'.rand(10000000, 99999999),
            'room_rate' => 20000,
            'no_of_nights' => 2,
            'total_amount' => 0, // zeroed under consolidate
            'check_in' => $lead->check_in,
            'check_out' => $lead->check_out,
            'stay_status' => 'checked_in',
        ]);

        $booking = Booking::create([
            'guest_name' => $lead->full_name,
            'guest_email' => 'lead'.uniqid().'@example.com',
            'booking_reference' => 'BK-GRP-'.rand(10000, 99999),
            'check_in_date' => $lead->check_in->toDateString(),
            'check_out_date' => $lead->check_out->toDateString(),
            'total_amount' => 120000,
            'amount_paid' => 50000,
        ]);
        $lead->update(['booking_id' => $booking->id]);

        // The deposit is on its payment list with the booking reference as the
        // marker, exactly as finalize() leaves it.
        RegistrationPayment::create([
            'registration_id' => $lead->id,
            'amount' => 50000,
            'payment_method' => 'online',
            'reference' => $booking->booking_reference,
            'payment_date' => Carbon::today(),

        ]);

        $response = $this->grantAccess()->get(route('frontdesk.registrations.show', $lead));
        $response->assertOk();

        $financials = $response->viewData('groupFinancialSummary');

        $this->assertSame(80000.0, $financials['lead_personal_bill']);
        $this->assertSame(40000.0, $financials['members_bill']);
        $this->assertSame(120000.0, $financials['group_charges']);
        $this->assertSame(70000.0, $financials['balance_due']);
        $this->assertTrue($financials['online_payment_synced']);
        $this->assertSame(50000.0, $financials['group_paid'], 'The synced deposit must be counted once, not twice.');
    }

    #[Test]
    public function show_page_adds_member_bills_when_the_group_is_individual(): void
    {
        $lead = $this->makeLead();
        $lead->update([
            'billing_type' => 'individual',
            'total_amount' => 80000, // the lead's own bill only
        ]);

        $lead->children()->create([
            'guest_id' => $this->makeGuest('Individual Member')->id,
            'full_name' => 'Individual Member',
            'contact_number' => '080'.rand(10000000, 99999999),
            'room_rate' => 20000,
            'no_of_nights' => 2,
            'total_amount' => 40000, // members keep their own bill
            'check_in' => $lead->check_in,
            'check_out' => $lead->check_out,
            'stay_status' => 'checked_in',
        ]);

        $response = $this->grantAccess()->get(route('frontdesk.registrations.show', $lead));
        $response->assertOk();

        $financials = $response->viewData('groupFinancialSummary');

        $this->assertSame(80000.0, $financials['lead_personal_bill']);
        $this->assertSame(40000.0, $financials['members_bill']);
        $this->assertSame(120000.0, $financials['group_charges'], 'Member bills must be added under individual billing.');
        $this->assertSame(120000.0, $financials['balance_due']);
    }

    #[Test]
    public function show_page_counts_an_unsynced_online_deposit_once(): void
    {
        $lead = $this->makeLead();
        $lead->update(['total_amount' => 80000]);

        $booking = Booking::create([
            'guest_name' => $lead->full_name,
            'guest_email' => 'lead'.uniqid().'@example.com',
            'booking_reference' => 'BK-DEP-'.rand(10000, 99999),
            'check_in_date' => $lead->check_in->toDateString(),
            'check_out_date' => $lead->check_out->toDateString(),
            'total_amount' => 80000,
            'amount_paid' => 50000,
        ]);
        $lead->update(['booking_id' => $booking->id]);

        // No synced payment on the list yet - the deposit is only on the booking.
        $response = $this->grantAccess()->get(route('frontdesk.registrations.show', $lead));
        $response->assertOk();

        $financials = $response->viewData('groupFinancialSummary');

        $this->assertFalse($financials['online_payment_synced']);
        $this->assertSame(50000.0, $financials['group_paid'], 'An unsynced deposit still counts towards the balance once.');
        $this->assertSame(30000.0, $financials['balance_due']);
    }
}
