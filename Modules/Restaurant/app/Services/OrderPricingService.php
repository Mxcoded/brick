<?php

namespace Modules\Restaurant\Services;

use Modules\Restaurant\Models\RestaurantSetting;

/**
 * One definition of what an order costs.
 *
 * The POS and the guest checkout each priced their own order inline, and had
 * drifted apart: only the POS knew about a service charge, so the same basket
 * cost a table guest more than a delivery guest, and neither path wrote down the
 * charge as a figure of its own. Both now quote through here, which means the
 * totals columns always add up and the rate that produced them is stored
 * alongside the amount.
 *
 * Service charge is charged on the post-discount subtotal and is added after
 * VAT, so VAT is not itself taxed. That ordering is deliberate and is what the
 * finance split in PostingService::saleLines() assumes.
 */
class OrderPricingService
{
    /**
     * Price an order.
     *
     * @return array{subtotal: float, discount: float, vat: float, vat_rate: float, service_charge: float, service_charge_rate: float, grand_total: float}
     */
    public function quote(float $subtotal, float $discountAmount = 0.0): array
    {
        $subtotal = max(0.0, $subtotal);

        // Clamp here as well as in the caller: an order can never be discounted
        // past zero, whatever the request claimed.
        $discountAmount = min(max(0.0, $discountAmount), $subtotal);
        $net = $subtotal - $discountAmount;

        $vatRate = max(0.0, (float) RestaurantSetting::getValue('vat_rate', '7.5'));
        $serviceChargeRate = max(0.0, (float) RestaurantSetting::getValue('service_charge_rate', '0'));

        // Round each component before adding, so subtotal - discount + vat +
        // service_charge reproduces grand_total to the cent once MySQL has
        // stored all four as decimals.
        $vat = round($net * $vatRate / 100, 2);
        $serviceCharge = round($net * $serviceChargeRate / 100, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discountAmount, 2),
            'vat' => $vat,
            'vat_rate' => $vatRate,
            'service_charge' => $serviceCharge,
            'service_charge_rate' => $serviceChargeRate,
            'grand_total' => round($net + $vat + $serviceCharge, 2),
        ];
    }

    /**
     * The non-revenue parts of a payment, scaled to the part of the order that
     * payment covers.
     *
     * A payment may settle less than the whole order, so the order's VAT and
     * service charge cannot simply be re-posted as-is: that would credit more
     * tax than was collected. Scaling by the fraction of the grand total the
     * payment represents also means a refund of the same payment reverses
     * exactly the components the sale was posted with.
     *
     * @return array{vat: float, service_charge: float}
     */
    public function paymentSplit(float $grandTotal, float $vat, float $serviceCharge, float $paymentAmount): array
    {
        if ($grandTotal <= 0 || $paymentAmount <= 0) {
            return ['vat' => 0.0, 'service_charge' => 0.0];
        }

        $ratio = min(1.0, $paymentAmount / $grandTotal);

        return [
            'vat' => round(max(0.0, $vat) * $ratio, 2),
            'service_charge' => round(max(0.0, $serviceCharge) * $ratio, 2),
        ];
    }
}
