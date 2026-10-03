<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Facades\DB;
use Modules\Finance\Services\PostingService;
use Modules\Restaurant\Events\OrderPaid;
use Modules\Restaurant\Events\OrderRefunded;
use Modules\Restaurant\Events\PaymentRecorded;
use Modules\Restaurant\Models\Customer;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\Payment;
use RuntimeException;

/**
 * The one place restaurant money moves.
 *
 * Both the waiter terminal and the back office take payments, and each used to
 * post to the ledger from its own controller method. That left the invariant
 * "a completed payment has exactly one ledger entry" maintained by copy-paste,
 * and any new entry point — a Filament resource, a webhook, a corrected
 * endpoint — would have had to remember the whole sequence. Here it is written
 * down once.
 *
 * The posting itself is deliberately *not* inside the database transaction.
 * Finance owns its own idempotency key, and a ledger write that fails must not
 * roll back the payment the customer actually made; the payment stays
 * authoritative and finance_posted stays false so
 * `restaurant:reconcile-finance` can retry. The alternative — one transaction
 * across both — would lose a real sale to a ledger hiccup, which is the worse
 * failure in a restaurant at 9pm on a Saturday.
 */
class OrderPaymentService
{
    public function __construct(
        protected PostingService $posting,
        protected OrderPricingService $pricing,
    ) {}

    /**
     * Take a payment against an order.
     *
     * @param  array{method: string, reference?: string|null, notes?: string|null, amount?: float|null, amount_tendered?: float|null}  $attributes
     * @return array{payment: Payment, change_due: float, balance_remaining: float, fully_paid: bool}
     */
    public function takePayment(Order $order, array $attributes): array
    {
        $balance = $order->balance;
        $amount = (float) ($attributes['amount'] ?? $balance);

        // Never take more than is owed, and never record a zero-value payment.
        $amount = min($amount, $balance);

        if ($amount <= 0) {
            throw new RuntimeException('No balance due.');
        }

        $payment = $this->createPayment($order, array_merge($attributes, [
            'amount' => $amount,
            'amount_tendered' => (float) ($attributes['amount_tendered'] ?? $amount),
        ]));

        $this->postSale($payment);

        $order->refresh()->load('payments');
        $balanceRemaining = $order->balance;

        if ($order->is_fully_paid) {
            $this->markPaid($order);
        }

        PaymentRecorded::dispatch($payment, $balanceRemaining);

        return [
            'payment' => $payment,
            'change_due' => (float) $payment->change_due,
            'balance_remaining' => $balanceRemaining,
            'fully_paid' => $order->is_fully_paid,
        ];
    }

    /**
     * Record a payment that cleared through an external gateway, such as
     * Paystack at online checkout.
     *
     * This is deliberately not takePayment(). That method closes the order out
     * once the balance reaches zero, which is right for a counter settlement but
     * wrong here: the customer has paid, yet the kitchen has neither cooked nor
     * delivered anything. Advancing the order would drop it straight off the
     * kitchen board, so only the money is recorded and the operational status is
     * left for the kitchen to drive.
     *
     * @param  array{method: string, reference?: string|null, amount?: float|null, notes?: string|null}  $attributes
     */
    public function recordGatewayPayment(Order $order, array $attributes): Payment
    {
        $amount = min((float) ($attributes['amount'] ?? $order->balance), $order->balance);

        if ($amount <= 0) {
            throw new RuntimeException('No balance due.');
        }

        $payment = $this->createPayment($order, array_merge($attributes, ['amount' => $amount]));

        $this->postSale($payment);

        // The sale is final even though fulfilment is not, so the guest earns
        // the visit now rather than when the rider finally arrives.
        $this->recordVisit($order);

        PaymentRecorded::dispatch($payment, $order->fresh()->balance);

        return $payment;
    }

    /**
     * Persist one completed payment row. Shared by every entry point so the
     * shape of a payment is defined in exactly one place.
     *
     * @param  array{method: string, amount: float, reference?: string|null, amount_tendered?: float|null, notes?: string|null}  $attributes
     */
    protected function createPayment(Order $order, array $attributes): Payment
    {
        $amount = (float) $attributes['amount'];
        $tendered = (float) ($attributes['amount_tendered'] ?? $amount);

        return DB::transaction(fn () => Payment::create([
            'restaurant_order_id' => $order->id,
            'amount' => $amount,
            'method' => $attributes['method'],
            'reference' => $attributes['reference'] ?? null,
            'change_due' => max(0, $tendered - $amount),
            'status' => 'completed',
            'paid_at' => now(),
            'notes' => $attributes['notes'] ?? null,
        ]));
    }

    /**
     * Reverse a completed payment and its ledger entry.
     *
     * Only ever the payment's own entry: the idempotency key is derived from the
     * payment id, so refunding one order twice is impossible and refunding a
     * second payment on the same order does not disturb the first.
     */
    public function refundPayment(Order $order, string $reason): Payment
    {
        if ($order->status !== 'completed' || $order->tracking_status !== 'paid') {
            throw new RuntimeException('Only paid orders can be refunded.');
        }

        $payment = $order->payments()->where('status', 'completed')->first();

        if (! $payment) {
            throw new RuntimeException('No completed payment found for this order.');
        }

        DB::transaction(function () use ($order, $payment, $reason) {
            $payment->status = 'refunded';
            $payment->refunded_at = now();
            $payment->refund_reason = $reason;
            $payment->save();

            $order->status = 'completed';
            $order->tracking_status = 'refunded';
            $order->save();
        });

        $this->postRefund($payment);

        OrderRefunded::dispatch($order->fresh(), $payment, $reason);

        return $payment;
    }

    /**
     * Post a completed payment to the ledger, stamping finance_posted on
     * success. Never throws: a ledger outage must not fail a sale.
     */
    public function postSale(Payment $payment): bool
    {
        $split = $this->taxSplit($payment);

        return $this->post(
            $payment,
            fn () => $this->posting->recordSale(
                $this->source(),
                (float) $payment->amount,
                $payment->method,
                $this->referenceType(),
                $payment->id,
                null,
                null,
                $split['vat'],
                $split['service_charge'],
                $this->revenueCode($payment),
                $this->assetCode($payment),
            ),
        );
    }

    /**
     * Post the reversal of a payment that was itself posted.
     *
     * Guarded on finance_posted, because reversing an entry that was never
     * created would put a credit in the ledger with nothing behind it.
     */
    public function postRefund(Payment $payment): bool
    {
        if (! $payment->finance_posted) {
            return false;
        }

        $split = $this->taxSplit($payment);

        return $this->post(
            $payment,
            fn () => $this->posting->recordRefund(
                $this->source(),
                (float) $payment->amount,
                $payment->method,
                $this->referenceType(),
                $payment->id,
                null,
                null,
                "REF-{$this->referenceType()}-{$payment->id}",
                $split['vat'],
                $split['service_charge'],
                $this->revenueCode($payment),
                $this->assetCode($payment),
            ),
        );
    }

    /**
     * The GL revenue account this sale belongs to.
     *
     * Null for an order with no sales point, or one whose sales point has no
     * account of its own, and PostingService then falls back to the module
     * revenue account. Read off the order rather than stored on the payment, so
     * a refund years later reverses into the same account the sale went to even
     * if the outlet has since been re-pointed.
     */
    protected function revenueCode(Payment $payment): ?string
    {
        return $payment->order?->salesPoint?->revenueCode();
    }

    /**
     * The asset (bank/till) account the takings landed in.
     *
     * A physical terminal can override the payment-method mapping: a Monipoint
     * on the front desk may bank its card receipts to a different bank account
     * than the one in the cafe, so we read the order's collection point rather
     * than hardcoding the method.
     */
    protected function assetCode(Payment $payment): ?string
    {
        return $payment->order?->collectionPoint?->assetCode();
    }

    /**
     * The VAT and service charge carried by one payment.
     *
     * Derived from the order rather than stored on the payment, because the
     * payment is one leg of an order that may be settled in several. Scaling by
     * the fraction of the grand total the payment covers keeps the components
     * proportional, and — because the same calculation runs on the way out — a
     * refund lands back in the accounts the sale came out of.
     *
     * @return array{vat: float, service_charge: float}
     */
    protected function taxSplit(Payment $payment): array
    {
        $order = $payment->order;

        if (! $order) {
            return ['vat' => 0.0, 'service_charge' => 0.0];
        }

        return $this->pricing->paymentSplit(
            (float) $order->grand_total,
            (float) $order->vat,
            (float) $order->service_charge,
            (float) $payment->amount,
        );
    }

    /**
     * @param  callable(): mixed  $post
     */
    protected function post(Payment $payment, callable $post): bool
    {
        if ((float) $payment->amount <= 0) {
            return false;
        }

        try {
            $entry = $post();
        } catch (\Throwable $e) {
            // Left unposted on purpose. The reconcile command is the safety net.
            report($e);

            return false;
        }

        // Finance returns null when the idempotency key already exists, which
        // means the entry is genuinely there. The flag still has to be stamped,
        // otherwise the reconcile command re-reports this payment as a gap on
        // every future run and can never clear it.
        $payment->forceFill(['finance_posted' => true])->save();

        return true;
    }

    /**
     * Close the order out and roll its visit forward onto the customer record.
     */
    protected function markPaid(Order $order): void
    {
        $order->status = 'completed';
        $order->tracking_status = 'paid';
        $order->save();

        $this->recordVisit($order);

        OrderPaid::dispatch($order, $order->payments()->where('status', 'completed')->latest()->first());
    }

    /**
     * Loyalty counters live on the customer, keyed by phone.
     *
     * firstOrCreate rather than updateOrCreate because the counters start at
     * zero on a new record and there is nothing here to preserve; the increment
     * below then applies to the first visit too.
     */
    protected function recordVisit(Order $order): void
    {
        if (blank($order->customer_phone)) {
            return;
        }

        $customer = Customer::firstOrCreate(
            ['phone' => $order->customer_phone],
            ['name' => $order->customer_name ?: 'Unknown'],
        );

        $customer->increment('visit_count');
        $customer->increment('total_spent', $order->grand_total);

        // One point per 100 in spend, floored so small tabs do not silently
        // round down to nothing.
        $customer->increment('loyalty_points', (int) floor((float) $order->grand_total / 100));
    }

    /**
     * The Finance module slug for Restaurant. Configurable so a deployment can
     * re-point the revenue account without editing code.
     */
    protected function source(): string
    {
        return (string) config('restaurant.finance.source', 'restaurant');
    }

    protected function referenceType(): string
    {
        return (string) config('restaurant.finance.reference_type', 'restaurant_payment');
    }
}
