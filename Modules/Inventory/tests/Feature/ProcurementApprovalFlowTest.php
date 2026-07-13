<?php

namespace Modules\Inventory\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Inventory\Models\PurchaseRequest;
use Modules\Inventory\Models\Store;
use Modules\Inventory\Models\Supplier;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProcurementApprovalFlowTest extends TestCase
{
    use DatabaseTransactions;

    private User $lineManager;

    private User $staff;

    private User $purchaser;

    private User $gm;

    private User $finance;

    private User $auditor;

    private User $ggm;

    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ValidateCsrfToken::class,
        ]);

        $this->setupPermissions();

        $this->lineManager = User::factory()->create();
        $this->lineManager->assignRole('line_manager');

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');

        $this->purchaser = User::factory()->create();
        $this->purchaser->assignRole('purchaser');

        $this->gm = User::factory()->create();
        $this->gm->assignRole('gm');

        $this->finance = User::factory()->create();
        $this->finance->assignRole('finance');

        $this->auditor = User::factory()->create();
        $this->auditor->assignRole('auditor');

        $this->ggm = User::factory()->create();
        $this->ggm->assignRole('ggm');

        $this->supplier = Supplier::create([
            'name' => 'Test Supplier',
            'email' => 'supplier@test.com',
            'phone' => '08012345678',
        ]);

        Store::create([
            'name' => 'Main Store',
            'address' => '123 Test Street',
        ]);
    }

    private function setupPermissions(): void
    {
        $permissions = [
            'procurement.create_request',
            'procurement.view_own_requests',
            'procurement.view_all_requests',
            'procurement.review_request',
            'procurement.approve_request',
            'procurement.reject_request',
            'procurement.flag_request',
            'procurement.attach_invoice',
            'procurement.audit_request',
            'procurement.convert_to_po',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm);
        }

        Role::findOrCreate('line_manager')->givePermissionTo([
            'procurement.create_request',
            'procurement.view_own_requests',
        ]);

        Role::findOrCreate('staff')->givePermissionTo([
            'procurement.create_request',
            'procurement.view_own_requests',
        ]);

        Role::findOrCreate('purchaser')->givePermissionTo([
            'procurement.view_all_requests',
            'procurement.review_request',
            'procurement.reject_request',
            'procurement.flag_request',
            'procurement.attach_invoice',
        ]);

        Role::findOrCreate('gm')->givePermissionTo([
            'procurement.view_all_requests',
            'procurement.approve_request',
            'procurement.reject_request',
            'procurement.flag_request',
        ]);

        Role::findOrCreate('finance')->givePermissionTo([
            'procurement.view_all_requests',
            'procurement.approve_request',
            'procurement.reject_request',
        ]);

        Role::findOrCreate('auditor')->givePermissionTo([
            'procurement.view_all_requests',
            'procurement.audit_request',
            'procurement.reject_request',
        ]);

        Role::findOrCreate('ggm')->givePermissionTo([
            'procurement.view_all_requests',
            'procurement.approve_request',
            'procurement.reject_request',
            'procurement.convert_to_po',
        ]);
    }

    /** @test */
    public function line_manager_can_create_and_submit_request()
    {
        $this->actingAs($this->lineManager);

        $response = $this->post(route('inventory.procurement.requests.store'), [
            'department' => 'Kitchen',
            'urgency' => 'normal',
            'justification' => 'Need new cooking utensils for the kitchen',
            'items' => [
                ['item_name' => 'Chef Knife Set', 'quantity' => 3, 'estimated_unit_price' => 15000, 'notes' => 'Stainless steel'],
                ['item_name' => 'Cutting Board', 'quantity' => 5, 'estimated_unit_price' => 5000, 'notes' => null],
            ],
            'submit_and_send' => '1',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', [
            'requester_id' => $this->lineManager->id,
            'department' => 'Kitchen',
            'status' => 'pending_purchaser',
            'current_role' => 'purchaser',
        ]);

        $pr = PurchaseRequest::where('requester_id', $this->lineManager->id)->first();
        $this->assertNotNull($pr);
        $this->assertCount(2, $pr->items);
        $this->assertDatabaseHas('purchase_request_approvals', [
            'purchase_request_id' => $pr->id,
            'role' => 'line_manager',
            'action' => 'submitted',
        ]);
    }

    /** @test */
    public function purchaser_can_review_and_forward_to_gm()
    {
        $pr = $this->createSubmittedRequest();

        $this->actingAs($this->purchaser);

        $response = $this->post(route('inventory.procurement.review', $pr), [
            'supplier_id' => $this->supplier->id,
            'gl_code' => 'GL-5010',
            'cost_center' => 'CC-KITCHEN',
            'procurement_notes' => 'Verified pricing with supplier',
            'items' => json_encode([
                ['id' => $pr->items[0]->id, 'unit_price' => 14000],
                ['id' => $pr->items[1]->id, 'unit_price' => 4500],
            ]),
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $pr->id,
            'status' => 'pending_gm',
            'current_role' => 'gm',
            'supplier_id' => $this->supplier->id,
            'gl_code' => 'GL-5010',
        ]);

        $this->assertDatabaseHas('purchase_request_approvals', [
            'purchase_request_id' => $pr->id,
            'role' => 'purchaser',
            'action' => 'reviewed',
        ]);
    }

    /** @test */
    public function gm_can_approve_and_forward_to_finance()
    {
        $pr = $this->createRequestAtStage('pending_gm');

        $this->actingAs($this->gm);

        $response = $this->post(route('inventory.procurement.approve', $pr));

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $pr->id,
            'status' => 'pending_finance',
            'current_role' => 'finance',
        ]);
    }

    /** @test */
    public function finance_can_confirm_and_forward_to_auditor()
    {
        $pr = $this->createRequestAtStage('pending_finance');

        $this->actingAs($this->finance);

        $response = $this->post(route('inventory.procurement.approve', $pr));

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $pr->id,
            'status' => 'pending_auditor',
            'current_role' => 'auditor',
        ]);
    }

    /** @test */
    public function auditor_can_confirm_and_forward_to_ggm()
    {
        $pr = $this->createRequestAtStage('pending_auditor');

        $this->actingAs($this->auditor);

        $response = $this->post(route('inventory.procurement.approve', $pr));

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $pr->id,
            'status' => 'pending_ggm',
            'current_role' => 'ggm',
        ]);
    }

    /** @test */
    public function ggm_can_final_approve_request()
    {
        $pr = $this->createRequestAtStage('pending_ggm');

        $this->actingAs($this->ggm);

        $response = $this->post(route('inventory.procurement.approve', $pr));

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $pr->id,
            'status' => 'approved',
            'current_role' => null,
        ]);
    }

    /** @test */
    public function ggm_can_convert_approved_request_to_purchase_order()
    {
        $pr = $this->createRequestAtStage('approved');
        $pr->update(['supplier_id' => $this->supplier->id]);

        $this->actingAs($this->ggm);

        $response = $this->post(route('inventory.procurement.convert-to-po', $pr));

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $pr->id,
            'status' => 'ordered',
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->ggm->id,
        ]);
    }

    /** @test */
    public function full_happy_path_approval_flow()
    {
        $this->actingAs($this->lineManager);

        $response = $this->post(route('inventory.procurement.requests.store'), [
            'department' => 'Housekeeping',
            'urgency' => 'urgent',
            'justification' => 'Cleaning supplies running low',
            'items' => [
                ['item_name' => 'Detergent 5L', 'quantity' => 10, 'estimated_unit_price' => 3500, 'notes' => null],
            ],
            'submit_and_send' => '1',
        ]);

        $pr = PurchaseRequest::where('requester_id', $this->lineManager->id)->first();
        $this->assertEquals('pending_purchaser', $pr->status);

        $this->actingAs($this->purchaser);
        $this->post(route('inventory.procurement.review', $pr), [
            'supplier_id' => $this->supplier->id,
            'gl_code' => 'GL-100',
            'cost_center' => 'CC-HK',
            'items' => json_encode([['id' => $pr->items[0]->id, 'unit_price' => 3200]]),
        ]);
        $this->assertEquals('pending_gm', $pr->fresh()->status);

        $this->actingAs($this->gm);
        $this->post(route('inventory.procurement.approve', $pr));
        $this->assertEquals('pending_finance', $pr->fresh()->status);

        $this->actingAs($this->finance);
        $this->post(route('inventory.procurement.approve', $pr));
        $this->assertEquals('pending_auditor', $pr->fresh()->status);

        $this->actingAs($this->auditor);
        $this->post(route('inventory.procurement.approve', $pr));
        $this->assertEquals('pending_ggm', $pr->fresh()->status);

        $this->actingAs($this->ggm);
        $this->post(route('inventory.procurement.approve', $pr));
        $this->assertEquals('approved', $pr->fresh()->status);

        $this->post(route('inventory.procurement.convert-to-po', $pr));
        $this->assertEquals('ordered', $pr->fresh()->status);

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $this->supplier->id,
        ]);
    }

    /** @test */
    public function gm_can_reject_request_back_to_purchaser()
    {
        $pr = $this->createRequestAtStage('pending_gm');

        $this->actingAs($this->gm);

        $response = $this->post(route('inventory.procurement.reject', $pr), [
            'notes' => 'Budget constraints, reduce quantity',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $pr->id,
            'status' => 'pending_purchaser',
            'current_role' => 'purchaser',
        ]);

        $this->assertDatabaseHas('purchase_request_approvals', [
            'purchase_request_id' => $pr->id,
            'role' => 'purchaser',
            'action' => 'rejected',
            'notes' => 'Budget constraints, reduce quantity',
        ]);
    }

    /** @test */
    public function purchaser_can_flag_request_as_incomplete()
    {
        $pr = $this->createSubmittedRequest();

        $this->actingAs($this->purchaser);

        $response = $this->post(route('inventory.procurement.flag', $pr), [
            'notes' => 'Missing item specifications',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $pr->id,
            'status' => 'flagged',
            'current_role' => 'line_manager',
        ]);
    }

    /** @test */
    public function line_manager_can_edit_flagged_request_and_resubmit()
    {
        $pr = $this->createSubmittedRequest();
        $pr->update(['status' => 'flagged', 'current_role' => 'line_manager']);

        $this->actingAs($this->lineManager);

        $response = $this->put(route('inventory.procurement.requests.update', $pr), [
            'department' => 'Kitchen',
            'urgency' => 'urgent',
            'justification' => 'Updated justification with specs',
            'items' => [
                ['id' => $pr->items[0]->id, 'item_name' => 'Chef Knife Set Pro', 'quantity' => 4, 'estimated_unit_price' => 18000, 'notes' => 'Professional grade'],
                ['id' => $pr->items[1]->id, 'item_name' => 'Cutting Board Large', 'quantity' => 5, 'estimated_unit_price' => 5000, 'notes' => null],
            ],
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_request_items', [
            'id' => $pr->items[0]->id,
            'item_name' => 'Chef Knife Set Pro',
            'quantity' => 4,
        ]);

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $pr->id,
            'status' => 'draft',
        ]);
    }

    /** @test */
    public function unauthorized_role_cannot_approve_at_wrong_stage()
    {
        $pr = $this->createSubmittedRequest();

        $this->actingAs($this->finance);

        $response = $this->post(route('inventory.procurement.approve', $pr));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('purchase_requests', [
            'id' => $pr->id,
            'status' => 'pending_purchaser',
        ]);
    }

    /** @test */
    public function drafts_can_only_be_submitted_by_requester()
    {
        $pr = $this->createSubmittedRequest();
        $pr->update(['status' => 'draft', 'current_role' => null]);

        $this->actingAs($this->purchaser);

        $response = $this->post(route('inventory.procurement.submit', $pr));

        $response->assertSessionHas('error');
        $this->assertEquals('draft', $pr->fresh()->status);
    }

    /** @test */
    public function staff_can_create_and_submit_request_like_line_manager()
    {
        $this->actingAs($this->staff);

        $response = $this->post(route('inventory.procurement.requests.store'), [
            'department' => 'Housekeeping',
            'urgency' => 'urgent',
            'justification' => 'Staff needs cleaning supplies',
            'items' => [
                ['item_name' => 'Mop Set', 'quantity' => 2, 'estimated_unit_price' => 3000, 'notes' => null],
            ],
            'submit_and_send' => '1',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', [
            'requester_id' => $this->staff->id,
            'department' => 'Housekeeping',
            'status' => 'pending_purchaser',
            'current_role' => 'purchaser',
        ]);

        $pr = PurchaseRequest::where('requester_id', $this->staff->id)->first();
        $this->assertNotNull($pr);
        $this->assertCount(1, $pr->items);
        $this->assertDatabaseHas('purchase_request_approvals', [
            'purchase_request_id' => $pr->id,
            'role' => 'line_manager',
            'action' => 'submitted',
        ]);
    }

    /** @test */
    public function staff_can_view_own_requests_only()
    {
        $this->actingAs($this->staff);

        $this->post(route('inventory.procurement.requests.store'), [
            'department' => 'Housekeeping',
            'urgency' => 'normal',
            'justification' => 'Staff request',
            'items' => [
                ['item_name' => 'Broom', 'quantity' => 1, 'estimated_unit_price' => 2000, 'notes' => null],
            ],
        ]);

        $pr = PurchaseRequest::where('requester_id', $this->staff->id)->first();

        $response = $this->get(route('inventory.procurement.requests.show', $pr));
        $response->assertOk();

        $otherPr = $this->createSubmittedRequest();
        $this->actingAs($this->staff);
        $response = $this->get(route('inventory.procurement.requests.show', $otherPr));
        $response->assertStatus(403);
    }

    /** @test */
    public function staff_submitted_request_triggers_full_approval_flow()
    {
        $this->actingAs($this->staff);

        $response = $this->post(route('inventory.procurement.requests.store'), [
            'department' => 'Laundry',
            'urgency' => 'normal',
            'justification' => 'Staff needs detergent',
            'items' => [
                ['item_name' => 'Detergent 5L', 'quantity' => 4, 'estimated_unit_price' => 3500, 'notes' => null],
            ],
            'submit_and_send' => '1',
        ]);

        $pr = PurchaseRequest::where('requester_id', $this->staff->id)->first();
        $this->assertEquals('pending_purchaser', $pr->status);

        $this->actingAs($this->purchaser);
        $this->post(route('inventory.procurement.review', $pr), [
            'supplier_id' => $this->supplier->id,
            'items' => json_encode([['id' => $pr->items[0]->id, 'unit_price' => 3200]]),
        ]);
        $this->assertEquals('pending_gm', $pr->fresh()->status);

        $this->actingAs($this->gm);
        $this->post(route('inventory.procurement.approve', $pr));
        $this->assertEquals('pending_finance', $pr->fresh()->status);

        $this->actingAs($this->finance);
        $this->post(route('inventory.procurement.approve', $pr));
        $this->assertEquals('pending_auditor', $pr->fresh()->status);

        $this->actingAs($this->auditor);
        $this->post(route('inventory.procurement.approve', $pr));
        $this->assertEquals('pending_ggm', $pr->fresh()->status);

        $this->actingAs($this->ggm);
        $this->post(route('inventory.procurement.approve', $pr));
        $this->assertEquals('approved', $pr->fresh()->status);
    }

    private function createSubmittedRequest(): PurchaseRequest
    {
        $this->actingAs($this->lineManager);

        $this->post(route('inventory.procurement.requests.store'), [
            'department' => 'Kitchen',
            'urgency' => 'normal',
            'justification' => 'Test request',
            'items' => [
                ['item_name' => 'Test Item 1', 'quantity' => 2, 'estimated_unit_price' => 1000, 'notes' => null],
                ['item_name' => 'Test Item 2', 'quantity' => 1, 'estimated_unit_price' => 500, 'notes' => null],
            ],
            'submit_and_send' => '1',
        ]);

        return PurchaseRequest::where('requester_id', $this->lineManager->id)->first();
    }

    private function createRequestAtStage(string $stage): PurchaseRequest
    {
        $pr = $this->createSubmittedRequest();
        $pr->update(['supplier_id' => $this->supplier->id]);

        $stages = ['pending_purchaser', 'pending_gm', 'pending_finance', 'pending_auditor', 'pending_ggm', 'approved'];
        $currentIdx = array_search($stage, $stages);

        if ($currentIdx === false) {
            return $pr;
        }

        foreach (['purchaser', 'gm', 'finance', 'auditor', 'ggm'] as $i => $roleName) {
            if ($i + 1 > $currentIdx) {
                break;
            }
            $roleUser = match ($roleName) {
                'purchaser' => $this->purchaser,
                'gm' => $this->gm,
                'finance' => $this->finance,
                'auditor' => $this->auditor,
                'ggm' => $this->ggm,
            };

            if ($i + 1 === 1) {
                $this->actingAs($roleUser);
                $this->post(route('inventory.procurement.review', $pr), [
                    'supplier_id' => $this->supplier->id,
                    'items' => json_encode(
                        $pr->items->map(fn ($item) => ['id' => $item->id, 'unit_price' => 1000])->toArray()
                    ),
                ]);
            } else {
                $this->actingAs($roleUser);
                $this->post(route('inventory.procurement.approve', $pr));
            }
        }

        $pr->refresh();

        return $pr;
    }
}
