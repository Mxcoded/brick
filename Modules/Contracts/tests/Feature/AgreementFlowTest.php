<?php

namespace Modules\Contracts\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Contracts\Enums\AgreementStatus;
use Modules\Contracts\Models\Agreement;
use Modules\Contracts\Models\AgreementTemplate;
use Modules\Contracts\Services\AgreementNumberGenerator;
use Modules\Contracts\Services\AgreementStatusService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AgreementFlowTest extends TestCase
{
    use DatabaseTransactions;

    private User $manager;

    protected array $contractPermissions = [
        'access_contracts_dashboard',
        'contracts.read',
        'contracts.create',
        'contracts.update',
        'contracts.delete',
        'contracts.approve',
        'contracts.sign',
        'contracts.manage_templates',
        'contracts.view_audit',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $permissions = collect($this->contractPermissions)
            ->map(fn (string $name) => Permission::findOrCreate($name, 'web'))
            ->all();

        $role = Role::firstOrCreate(['name' => 'contracts_manager', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->manager = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->manager->assignRole('contracts_manager');
    }

    protected function agreementPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Corporate Room Agreement - Amara Ltd',
            'type' => 'corporate_accommodation',
            'currency' => 'NGN',
            'location' => 'Brickspoint Asokoro',
            'department' => 'Sales',
            'effective_date' => '2026-09-01',
            'expiry_date' => '2027-09-01',
            'auto_renew' => true,
            'value_amount' => 5000000,
            'deposit_amount' => 1000000,
            'notes' => 'Test agreement',
            'room_type' => 'Executive Apartment',
            'number_of_rooms' => 3,
            'rate_per_night' => 85000,
            'discount_percent' => 10,
            'payment_terms' => 'Monthly',
            'cancellation_policy' => '14 days notice',
            'tax_applicable' => true,
            'hotel_legal_name' => 'Brickspoint Boutique Aparthotel Ltd',
            'hotel_contact_person' => 'GM Okafor',
            'hotel_position' => 'General Manager',
            'hotel_address' => 'Asokoro, Abuja',
            'hotel_email' => 'gm@brickspoint.com',
            'hotel_phone' => '+2348000000001',
            'client_legal_name' => 'Amara Ltd',
            'client_registration_no' => 'RC123456',
            'client_contact_person' => 'Ms Amara',
            'client_position' => 'Head of Administration',
            'client_address' => 'Wuse 2, Abuja',
            'client_email' => 'amara@example.com',
            'client_phone' => '+2348000000002',
            'clauses' => [
                ['title' => 'Accommodation', 'content' => 'Hotel provides 3 executive apartments.', 'sort_order' => 0],
                ['title' => 'Payment', 'content' => 'Monthly invoice settled within 7 days.', 'sort_order' => 1],
            ],
            'obligations' => [
                [
                    'obligation_type' => 'payment',
                    'title' => 'Monthly invoice by the 5th',
                    'description' => 'Standing invoice for monthly rate',
                    'responsible_party' => 'client',
                    'amount' => 2080000,
                    'due_date' => '2026-10-05',
                ],
            ],
        ], $overrides);
    }

    protected function actingAsManager(): void
    {
        $this->actingAs($this->manager);
    }

    public function test_manager_can_create_agreement_with_full_payload()
    {
        $this->actingAsManager();

        $response = $this->post(route('contracts.agreements.store'), $this->agreementPayload());

        $response->assertRedirect(route('contracts.agreements.index'));
        $response->assertSessionHas('success');

        $this->assertTrue(Agreement::where('title', 'Corporate Room Agreement - Amara Ltd')->where('status', 'draft')->exists());

        $agreement = Agreement::where('title', 'Corporate Room Agreement - Amara Ltd')->firstOrFail();
        $this->assertMatchesRegularExpression('/^AGR-\d{4}-\d{4}$/', $agreement->agreement_number);
        $this->assertSame(1, $agreement->current_version);
        $this->assertSame(3, $agreement->number_of_rooms ?? ($agreement->commercial_terms['number_of_rooms'] ?? null));
        $this->assertCount(2, $agreement->clauses);
        $this->assertSame('Accommodation', $agreement->clauses->first()->title);
        $this->assertSame(1, $agreement->clauses->first()->version);
        $this->assertCount(2, $agreement->parties);
        $this->assertSame('Amara Ltd', $agreement->primaryClient()->legal_name);
        $this->assertSame('Brickspoint Boutique Aparthotel Ltd', $agreement->hotelParty()->legal_name);
        $this->assertCount(1, $agreement->obligations);
        $this->assertSame('pending', $agreement->obligations->first()->status);
        $this->assertTrue($agreement->versions()->where('version', 1)->where('status', 'draft')->exists());
    }

    public function test_number_generator_is_sequential()
    {
        $this->actingAsManager();

        $this->post(route('contracts.agreements.store'), $this->agreementPayload());
        $first = Agreement::orderBy('id', 'desc')->first();
        $seqFirst = (int) substr($first->agreement_number, -4);

        $this->post(route('contracts.agreements.store'), $this->agreementPayload(['title' => 'Second Agreement']));
        $second = Agreement::where('title', 'Second Agreement')->first();

        $this->assertNotSame($first->agreement_number, $second->agreement_number);
        $this->assertSame($seqFirst + 1, (int) substr($second->agreement_number, -4));
    }

    public function test_status_service_runs_full_workflow_and_records_approval_and_versions()
    {
        $agreement = Agreement::create([
            'agreement_number' => app(AgreementNumberGenerator::class)->next(),
            'title' => 'Workflow Agreement',
            'type' => 'room_block',
            'status' => AgreementStatus::DRAFT->value,
            'currency' => 'NGN',
            'current_version' => 1,
        ]);

        $service = app(AgreementStatusService::class);

        $chain = [
            AgreementStatus::INTERNAL_REVIEW->value,
            AgreementStatus::PENDING_APPROVAL->value,
            AgreementStatus::APPROVED->value,
            AgreementStatus::SENT->value,
            AgreementStatus::CLIENT_REVIEW->value,
            AgreementStatus::CLIENT_SIGNED->value,
            AgreementStatus::HOTEL_SIGNED->value,
            AgreementStatus::EXECUTED->value,
            AgreementStatus::ACTIVE->value,
        ];

        foreach ($chain as $i => $to) {
            $service->transition($agreement, $to, $to === AgreementStatus::APPROVED->value ? 'Looks good' : null, $this->manager);
            $agreement->refresh();
            $this->assertSame($to, $agreement->status, "Failed at step {$i}");
        }

        $this->assertNotNull($agreement->approved_at);
        $this->assertSame($this->manager->id, $agreement->approved_by);
        $this->assertTrue($agreement->approvals()->where('status', 'approved')->exists());
        $this->assertTrue($agreement->versions()->where('changes_summary', 'like', '%→%')->exists());
    }

    public function test_status_service_rejects_invalid_transition()
    {
        $agreement = Agreement::create([
            'agreement_number' => app(AgreementNumberGenerator::class)->next(),
            'title' => 'Bad Transition',
            'type' => 'custom',
            'status' => AgreementStatus::DRAFT->value,
            'currency' => 'NGN',
            'current_version' => 1,
        ]);

        $this->expectException(\DomainException::class);

        app(AgreementStatusService::class)->transition($agreement, AgreementStatus::APPROVED->value);
    }

    public function test_http_transition_moves_agreement_forward()
    {
        $this->actingAsManager();

        $this->post(route('contracts.agreements.store'), $this->agreementPayload());
        $agreement = Agreement::orderBy('id', 'desc')->first();

        $response = $this->post(route('contracts.agreements.status', $agreement), ['to' => 'internal_review']);

        $response->assertSessionHas('success');
        $this->assertSame('internal_review', $agreement->fresh()->status);
    }

    public function test_http_transition_rejects_disallowed_move()
    {
        $this->actingAsManager();

        $this->post(route('contracts.agreements.store'), $this->agreementPayload());
        $agreement = Agreement::orderBy('id', 'desc')->first();

        $response = $this->post(route('contracts.agreements.status', $agreement), ['to' => 'active']);

        $response->assertSessionHas('error');
        $this->assertSame('draft', $agreement->fresh()->status);
    }

    public function test_manager_can_record_signature_which_advances_status()
    {
        $this->actingAsManager();

        $this->post(route('contracts.agreements.store'), $this->agreementPayload());
        $agreement = Agreement::orderBy('id', 'desc')->first();

        $service = app(AgreementStatusService::class);
        foreach (array_slice($service->workflowSteps(), 1, 5) as $to) {
            $service->transition($agreement, $to, null, $this->manager);
            $agreement->refresh();
        }
        $this->assertSame('client_review', $agreement->status);

        $response = $this->post(route('contracts.agreements.signatures.store', $agreement), [
            'party_role' => 'client',
            'party_name' => 'Ms Amara',
            'position' => 'Head of Administration',
            'signature_type' => 'click',
            'signature_data' => null,
            'verification' => 'OTP-1234',
        ]);

        $response->assertSessionHas('success');
        $this->assertSame('client_signed', $agreement->fresh()->status);

        $signature = $agreement->signatures()->firstOrFail();
        $this->assertSame('Ms Amara', $signature->party_name);
        $this->assertSame('client', $signature->party_role);
        $this->assertNotNull($signature->ip_address);
        $this->assertNotNull($signature->user_agent);
        $this->assertNotNull($signature->hash);
        $this->assertSame('OTP-1234', $signature->verification);
    }

    public function test_obligation_status_update_sets_completed_at()
    {
        $this->actingAsManager();

        $this->post(route('contracts.agreements.store'), $this->agreementPayload());
        $agreement = Agreement::orderBy('id', 'desc')->first();
        $obligation = $agreement->obligations()->firstOrFail();

        $this->patch(route('contracts.agreements.obligations.update', [$agreement, $obligation]), ['status' => 'completed'])
            ->assertSessionHas('success');

        $obligation->refresh();
        $this->assertSame('completed', $obligation->status);
        $this->assertNotNull($obligation->completed_at);
    }

    public function test_amendments_allowed_only_on_active_or_executed_agreements()
    {
        $this->actingAsManager();

        $this->post(route('contracts.agreements.store'), $this->agreementPayload());
        $agreement = Agreement::orderBy('id', 'desc')->first();

        $response = $this->post(route('contracts.agreements.amendments.store', $agreement), [
            'title' => 'Rate reduction',
            'description' => 'Reduce monthly rate by 5%.',
            'effective_date' => '2026-11-01',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(0, $agreement->amendments()->count());

        $service = app(AgreementStatusService::class);
        foreach (array_slice($service->workflowSteps(), 1) as $to) {
            $service->transition($agreement, $to, null, $this->manager);
            $agreement->refresh();
        }

        $this->post(route('contracts.agreements.amendments.store', $agreement), [
            'title' => 'Rate reduction',
            'description' => 'Reduce monthly rate by 5%.',
            'effective_date' => '2026-11-01',
        ])->assertSessionHas('success');

        $this->assertSame(1, $agreement->amendments()->count());
        $this->assertSame('amended', $agreement->fresh()->status);
    }

    public function test_edit_and_update_locked_for_executed_or_active_agreement()
    {
        $this->actingAsManager();

        $this->post(route('contracts.agreements.store'), $this->agreementPayload());
        $agreement = Agreement::orderBy('id', 'desc')->first();

        $service = app(AgreementStatusService::class);
        foreach ($service->workflowSteps() as $to) {
            if ($to === AgreementStatus::DRAFT->value) {
                continue;
            }
            $service->transition($agreement, $to, null, $this->manager);
            $agreement->refresh();
        }

        $this->assertSame('active', $agreement->status);

        $this->get(route('contracts.agreements.edit', $agreement))
            ->assertRedirect(route('contracts.agreements.show', $agreement))
            ->assertSessionHas('error');

        $this->put(route('contracts.agreements.update', $agreement), $this->agreementPayload(['title' => 'Locked Change']))
            ->assertRedirect(route('contracts.agreements.show', $agreement))
            ->assertSessionHas('error');

        $this->assertNotSame('Locked Change', $agreement->fresh()->title);
    }

    public function test_only_draft_agreements_can_be_destroyed()
    {
        $this->actingAsManager();

        $this->post(route('contracts.agreements.store'), $this->agreementPayload());
        $agreement = Agreement::orderBy('id', 'desc')->first();

        app(AgreementStatusService::class)->transition($agreement, AgreementStatus::INTERNAL_REVIEW->value, null, $this->manager);

        $this->delete(route('contracts.agreements.destroy', $agreement))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('agreements', ['id' => $agreement->id]);

        app(AgreementStatusService::class)->transition($agreement, AgreementStatus::DRAFT->value, null, $this->manager);

        $this->delete(route('contracts.agreements.destroy', $agreement))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('agreements', ['id' => $agreement->id]);
    }

    public function test_manager_can_create_template_and_prefill_agreement_clauses()
    {
        $this->actingAsManager();

        $this->post(route('contracts.templates.store'), [
            'name' => 'Corporate Standard Template',
            'type' => 'corporate_accommodation',
            'description' => 'Standard corporate clauses',
            'is_active' => true,
            'clauses' => [
                ['title' => 'Accommodation', 'content' => 'Standard accommodation clause.', 'sort_order' => 0],
                ['title' => 'Insurance', 'content' => 'Insurance clause body.', 'sort_order' => 1],
            ],
        ])->assertSessionHas('success');

        $template = AgreementTemplate::where('name', 'Corporate Standard Template')->firstOrFail();
        $this->assertCount(2, $template->clauses);

        $this->get(route('contracts.agreements.create').'?template='.$template->id)
            ->assertOk()
            ->assertSee('Accommodation')
            ->assertSee('Insurance');
    }

    public function test_agreements_datatable_returns_rows()
    {
        $this->actingAsManager();

        $this->post(route('contracts.agreements.store'), $this->agreementPayload());

        $response = $this->getJson(route('contracts.agreements.datatable'));

        $response->assertOk();
        $response->assertJsonStructure(['data' => [['agreement_number', 'title', 'client', 'status_badge', 'actions']]]);
    }

    public function test_agreement_pdf_is_downloadable_and_valid()
    {
        $this->actingAsManager();

        $this->post(route('contracts.agreements.store'), $this->agreementPayload());
        $agreement = Agreement::where('title', 'Corporate Room Agreement - Amara Ltd')->firstOrFail();

        $response = $this->get(route('contracts.agreements.pdf', $agreement));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename="agreement-'.$agreement->agreement_number.'.pdf"');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_agreement_pdf_requires_read_permission()
    {
        $this->actingAsManager();

        $this->post(route('contracts.agreements.store'), $this->agreementPayload());
        $agreement = Agreement::where('title', 'Corporate Room Agreement - Amara Ltd')->firstOrFail();

        $reader = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $reader->givePermissionTo('access_contracts_dashboard');

        $this->actingAs($reader)->get(route('contracts.agreements.pdf', $agreement))->assertForbidden();

        auth()->logout();
        $this->get(route('contracts.agreements.pdf', $agreement))->assertRedirect(route('login'));
    }

    public function test_pdf_download_records_audit_entry()
    {
        $this->actingAsManager();

        $this->post(route('contracts.agreements.store'), $this->agreementPayload());
        $agreement = Agreement::where('title', 'Corporate Room Agreement - Amara Ltd')->firstOrFail();

        $this->get(route('contracts.agreements.pdf', $agreement))->assertOk();

        $this->assertTrue(
            $agreement->audits()->where('event', 'pdf_generated')->where('user_id', $this->manager->id)->exists()
        );
    }

    public function test_agreement_pdf_renders_with_embedded_signature_image()
    {
        $this->actingAsManager();

        $this->post(route('contracts.agreements.store'), $this->agreementPayload());
        $agreement = Agreement::where('title', 'Corporate Room Agreement - Amara Ltd')->firstOrFail();

        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQDJ/pLvAAAAAElFTkSuQmCC';

        $this->post(route('contracts.agreements.signatures.store', $agreement), [
            'party_role' => 'client',
            'party_name' => 'Ms Amara',
            'position' => 'Head of Administration',
            'signature_type' => 'draw',
            'signature_data' => $png,
        ])->assertSessionHas('success');

        $response = $this->get(route('contracts.agreements.pdf', $agreement));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertTrue($agreement->signatures()->where('party_name', 'Ms Amara')->exists());
    }

    public function test_dashboard_requires_contracts_permission()
    {
        $plainUser = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        $this->get(route('contracts.dashboard'))->assertRedirect(route('login'));

        $this->actingAs($plainUser)->get(route('contracts.dashboard'))->assertForbidden();

        $this->actingAs($this->manager)->get(route('contracts.dashboard'))
            ->assertOk()
            ->assertSee('Contracts &amp; Agreements', false);
    }
}
