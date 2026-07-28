<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Finance\Database\Seeders\ChartOfAccountsSeeder;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Models\JournalLine;
use Modules\Finance\Services\PostingService;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationPayment;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Tests\TestCase;

class FinancialIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected PostingService $posting;

    protected function setUp(): void
    {
        parent::setUp();

        (new ChartOfAccountsSeeder)->run();

        JournalEntry::query()->delete();

        $this->posting = app(PostingService::class);
    }

    public function test_each_module_revenue_maps_to_correct_gl_account(): void
    {
        $scenarios = [
            'frontdesk' => ['ref' => 'registration_payment', 'expected_revenue' => '4000'],
            'website'   => ['ref' => 'booking',              'expected_revenue' => '4000'],
            'restaurant'=> ['ref' => 'restaurant_payment',    'expected_revenue' => '4100'],
            'banquet'   => ['ref' => 'banquet_payment',       'expected_revenue' => '4200'],
            'gym'       => ['ref' => 'gym_payment',           'expected_revenue' => '4300'],
        ];

        $id = 1;
        foreach ($scenarios as $module => $cfg) {
            $entry = $this->posting->recordSale($module, 500, 'cash', $cfg['ref'], $id++);

            $this->assertNotNull($entry, "recordSale($module) returned null");

            $revenueLine = $entry->lines->firstWhere('credit', '>', 0);
            $this->assertSame(
                $cfg['expected_revenue'],
                $revenueLine->account->code,
                "{$module} revenue should map to GL {$cfg['expected_revenue']}"
            );

            $debitLine = $entry->lines->firstWhere('debit', '>', 0);
            $this->assertSame('1000', $debitLine->account->code, 'cash payment should debit Cash');
        }
    }

    public function test_each_payment_method_maps_to_correct_asset_account(): void
    {
        $methods = [
            'cash'          => '1000',
            'pos'           => '1100',
            'card'          => '1100',
            'mobile_money'  => '1100',
            'transfer'      => '1100',
            'bank_transfer' => '1100',
            'cheque'        => '1100',
            'crypto'        => '1100',
            'paystack'      => '1110',
            'stripe'        => '1120',
        ];

        $id = 1;
        foreach ($methods as $method => $expectedAssetCode) {
            $entry = $this->posting->recordSale('restaurant', 200, $method, 'payment_method_test', $id++);

            $this->assertNotNull($entry, "recordSale with method [$method] returned null");

            $debitLine = $entry->lines->firstWhere('debit', '>', 0);
            $this->assertSame(
                $expectedAssetCode,
                $debitLine->account->code,
                "Payment method [$method] should map to GL $expectedAssetCode"
            );
        }
    }

    public function test_reference_type_is_recorded_for_polymorphic_tracing(): void
    {
        $entry = $this->posting->recordSale('frontdesk', 300, 'cash', 'registration_payment', 42);

        $this->assertSame('registration_payment', $entry->reference_type);
        $this->assertSame(42, $entry->reference_id);
        $this->assertStringContainsString('registration_payment', $entry->entry_number);
    }

    public function test_can_trace_from_module_record_to_journal_entry_via_polymorphic_reference(): void
    {
        $this->posting->recordSale('website', 150000, 'paystack', 'booking', 99);

        $found = JournalEntry::where('reference_type', 'booking')
            ->where('reference_id', 99)
            ->first();

        $this->assertNotNull($found);
        $this->assertSame('SALE-booking-99', $found->entry_number);
    }

    public function test_gym_expense_posts_correct_accounts(): void
    {
        $entry = $this->posting->recordExpense('gym', 50000, 'bank_transfer', 'gym_trainer_payment', 10);

        $this->assertNotNull($entry);
        $this->assertSame('5400', $entry->lines->firstWhere('debit', '>', 0)->account->code);
        $this->assertSame('1100', $entry->lines->firstWhere('credit', '>', 0)->account->code);
    }

    public function test_inventory_ap_liability_posts_correct_accounts(): void
    {
        $entry = $this->posting->recordApLiability(75000, '5000', 'purchase_order', 20);

        $this->assertNotNull($entry);
        $this->assertSame('5000', $entry->lines->firstWhere('debit', '>', 0)->account->code);
        $this->assertSame('2000', $entry->lines->firstWhere('credit', '>', 0)->account->code);
    }

    public function test_all_modules_produce_balanced_entries(): void
    {
        $results = [
            $this->posting->recordSale('frontdesk', 1000, 'cash', 'test_balance', 1),
            $this->posting->recordSale('website', 2000, 'paystack', 'test_balance', 2),
            $this->posting->recordSale('restaurant', 500, 'card', 'test_balance', 3),
            $this->posting->recordSale('banquet', 3000, 'transfer', 'test_balance', 4),
            $this->posting->recordSale('gym', 1500, 'cash', 'test_balance', 5),
            $this->posting->recordExpense('gym', 800, 'bank_transfer', 'test_balance', 6),
            $this->posting->recordApLiability(1200, '5000', 'test_balance', 7),
        ];

        foreach ($results as $i => $entry) {
            $this->assertNotNull($entry, "Call $i returned null");

            $totalDebit = (float) $entry->lines->sum('debit');
            $totalCredit = (float) $entry->lines->sum('credit');
            $this->assertEqualsWithDelta($totalDebit, $totalCredit, 0.005, "Call $i is not balanced");
        }
    }

    public function test_idempotency_prevents_duplicate_entries(): void
    {
        $first = $this->posting->recordSale('restaurant', 1000, 'cash', 'idempotent_test', 1);
        $second = $this->posting->recordSale('restaurant', 1000, 'cash', 'idempotent_test', 1);

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertSame(1, JournalEntry::where('entry_number', $first->entry_number)->count());
    }

    public function test_zero_amount_is_skipped(): void
    {
        $this->assertNull($this->posting->recordSale('frontdesk', 0, 'cash', 'zero_test', 1));
        $this->assertNull($this->posting->recordExpense('gym', 0, 'cash', 'zero_test', 2));
        $this->assertNull($this->posting->recordApLiability(0, '5000', 'zero_test', 3));
    }

    public function test_unknown_module_defaults_to_other_income(): void
    {
        $entry = $this->posting->recordSale('unknown_module', 500, 'cash', 'unknown_test', 1);

        $this->assertNotNull($entry);
        $revenueLine = $entry->lines->firstWhere('credit', '>', 0);
        $this->assertSame('4900', $revenueLine->account->code);
    }

    public function test_refund_reverses_sale_accounts(): void
    {
        $this->posting->recordSale('restaurant', 1000, 'cash', 'refund_test', 1);

        $refund = $this->posting->recordRefund('restaurant', 1000, 'cash', 'refund_test', 1);

        $this->assertNotNull($refund);
        $this->assertSame('4100', $refund->lines->firstWhere('debit', '>', 0)->account->code);
        $this->assertSame('1000', $refund->lines->firstWhere('credit', '>', 0)->account->code);
    }

    public function test_website_booking_flows_to_frontdeskcrm_registration(): void
    {
        $roomType = RoomType::create([
            'name' => 'Integration Test Room',
            'slug' => 'integration-test-room',
            'price' => 50000,
            'capacity' => 2,
        ]);

        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'unit_number' => 'INT-001',
            'room_number' => '101',
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'guest_name' => 'Integration Guest',
            'guest_email' => 'integration@example.com',
            'guest_phone' => '0800000000',
            'check_in_date' => now()->addDays(1)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
            'adults' => 1,
            'children' => 0,
            'total_amount' => 150000,
            'amount_paid' => 150000,
            'payment_status' => 'paid',
            'payment_method' => 'paystack',
            'status' => 'confirmed',
            'source' => 'website',
        ]);

        $this->assertSame(150000.0, (float) $booking->total_amount);
        $this->assertSame(150000.0, (float) $booking->amount_paid);
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('confirmed', $booking->status);

        $registration = Registration::create([
            'booking_id' => $booking->id,
            'full_name' => 'Integration Guest',
            'contact_number' => '0800000000',
            'email' => 'integration@example.com',
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'room_rate' => 50000,
            'check_in' => now()->addDays(1)->toDateString(),
            'check_out' => now()->addDays(4)->toDateString(),
            'no_of_nights' => 3,
            'total_amount' => 150000,
            'stay_status' => 'reserved',
        ]);

        $payment = RegistrationPayment::create([
            'registration_id' => $registration->id,
            'amount' => 50000,
            'payment_method' => 'cash',
            'reference' => 'WALKIN-001',
            'received_by' => User::factory()->create()->id,
            'payment_date' => now()->toDateString(),
        ]);

        $entry = $this->posting->recordSale('frontdesk', (float) $payment->amount, $payment->payment_method, 'registration_payment', $payment->id);

        $this->assertNotNull($entry, 'Frontdesk walk-in payment should post to finance');
        $this->assertSame('1000', $entry->lines->firstWhere('debit', '>', 0)->account->code);

        $localTotal = (float) $registration->payments()->sum('amount');
        $onlineTotal = (float) ($registration->booking?->amount_paid ?? 0);
        $totalPaid = $localTotal + $onlineTotal;

        $this->assertSame(200000.0, $totalPaid);
    }

    public function test_all_reference_types_used_across_modules_are_known(): void
    {
        $this->posting->recordSale('frontdesk', 100, 'cash', 'registration_payment', 1);
        $this->posting->recordSale('website', 100, 'paystack', 'booking', 2);
        $this->posting->recordSale('restaurant', 100, 'card', 'restaurant_payment', 3);
        $this->posting->recordSale('banquet', 100, 'transfer', 'banquet_payment', 4);
        $this->posting->recordSale('gym', 100, 'cash', 'gym_payment', 5);
        $this->posting->recordExpense('gym', 100, 'bank_transfer', 'gym_trainer_payment', 6);
        $this->posting->recordApLiability(100, '5000', 'purchase_order', 7);

        $referenceTypes = JournalEntry::pluck('reference_type')->unique()->sort()->values()->toArray();

        $expected = [
            'banquet_payment',
            'booking',
            'gym_payment',
            'gym_trainer_payment',
            'purchase_order',
            'registration_payment',
            'restaurant_payment',
        ];

        $this->assertSame($expected, $referenceTypes);
    }

    public function test_journal_entry_number_uniqueness_across_modules(): void
    {
        $this->posting->recordSale('frontdesk', 100, 'cash', 'uniq_test', 1);
        $this->posting->recordSale('website', 200, 'paystack', 'uniq_test', 2);
        $this->posting->recordSale('restaurant', 300, 'card', 'uniq_test', 3);
        $this->posting->recordSale('banquet', 400, 'transfer', 'uniq_test', 4);
        $this->posting->recordSale('gym', 500, 'cash', 'uniq_test', 5);
        $this->posting->recordExpense('gym', 150, 'bank_transfer', 'uniq_test', 6);
        $this->posting->recordApLiability(250, '5000', 'uniq_test', 7);

        $entries = JournalEntry::all();
        $numbers = $entries->pluck('entry_number');

        $this->assertSame($numbers->count(), $numbers->unique()->count());
    }

    public function test_website_callback_does_not_post_to_finance(): void
    {
        $this->posting->recordSale('website', 50000, 'paystack', 'booking', 55);

        $this->assertNotNull(
            JournalEntry::where('reference_type', 'booking')->where('reference_id', 55)->first()
        );

        $this->assertNull(
            $this->posting->recordSale('website', 50000, 'paystack', 'booking', 55)
        );
    }

    public function test_record_ap_payment_is_available_but_unused(): void
    {
        $entry = $this->posting->recordApPayment(1000, 'bank_transfer', 'supplier_payment', 1);

        $this->assertNotNull($entry);
        $this->assertSame('2000', $entry->lines->firstWhere('debit', '>', 0)->account->code);
        $this->assertSame('1100', $entry->lines->firstWhere('credit', '>', 0)->account->code);
        $this->assertStringContainsString('APP-supplier_payment-1', $entry->entry_number);
    }

    public function test_frontdesk_and_website_share_room_revenue_account(): void
    {
        $frontdeskEntry = $this->posting->recordSale('frontdesk', 1000, 'cash', 'shared_rev_test', 1);
        $websiteEntry = $this->posting->recordSale('website', 2000, 'paystack', 'shared_rev_test', 2);

        $frontdeskRevenue = $frontdeskEntry->lines->firstWhere('credit', '>', 0)->account->code;
        $websiteRevenue = $websiteEntry->lines->firstWhere('credit', '>', 0)->account->code;

        $this->assertSame('4000', $frontdeskRevenue);
        $this->assertSame('4000', $websiteRevenue);
    }

    public function test_each_entry_has_exactly_two_lines(): void
    {
        $this->posting->recordSale('frontdesk', 1000, 'cash', 'lines_test', 1);
        $this->posting->recordSale('restaurant', 500, 'card', 'lines_test', 2);
        $this->posting->recordExpense('gym', 300, 'bank_transfer', 'lines_test', 3);
        $this->posting->recordApLiability(700, '5000', 'lines_test', 4);

        $entries = JournalEntry::where('reference_type', 'lines_test')->get();

        foreach ($entries as $entry) {
            $this->assertCount(2, $entry->lines, "Entry {$entry->entry_number} should have 2 lines");
        }
    }

    public function test_unknown_payment_method_falls_back_to_bank(): void
    {
        $entry = $this->posting->recordSale('restaurant', 500, 'unknown_method_xyz', 'fallback_test', 1);

        $this->assertNotNull($entry);
        $debitLine = $entry->lines->firstWhere('debit', '>', 0);
        $this->assertSame('1100', $debitLine->account->code);
    }

    public function test_gl_accounts_exist_for_all_module_config(): void
    {
        $codes = ['1000', '1100', '1110', '1120', '1200', '1300', '2000', '4000', '4100', '4200', '4300', '4900', '5000', '5400'];

        foreach ($codes as $code) {
            $account = ChartOfAccount::where('code', $code)->first();
            $this->assertNotNull($account, "GL account $code should exist");
        }
    }
}
