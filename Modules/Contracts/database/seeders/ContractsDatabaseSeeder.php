<?php

namespace Modules\Contracts\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Contracts\Models\AgreementTemplate;

class ContractsDatabaseSeeder extends Seeder
{
    /**
     * Seed hotel-standard agreement templates so agreements can be
     * prefilled from clauses instead of drafted from scratch.
     */
    public function run(): void
    {
        $createdBy = User::where('email', 'it@brickspoint.com')->value('id');

        $templates = [
            [
                'name' => 'Corporate Accommodation',
                'type' => 'corporate_accommodation',
                'description' => 'Standard agreement covering corporate room accommodation, billing and cancellation.',
                'clauses' => [
                    ['title' => 'Definitions', 'content' => "In this Agreement: 'Hotel' means the contracting hotel; 'Client' means the corporate customer; 'Room' means the accommodation unit described in the commercial terms; and 'Rate' means the nightly accommodation rate agreed in the commercial terms."],
                    ['title' => 'Accommodation & Rates', 'content' => 'The Hotel shall provide the Client with the number of rooms set out in the commercial terms at the agreed rate per night. Rates are inclusive of taxes unless the commercial terms state otherwise.'],
                    ['title' => 'Billing & Payment', 'content' => 'The Client will be invoiced monthly for confirmed nights actually consumed. Payment is due within 7 days of the invoice date. Late payments may suspend room allocation until cleared.'],
                    ['title' => 'Cancellation & Early Departure', 'content' => 'Cancellations and early departures require 14 days notice. Rooms released after the notice period remain chargeable per the cancellation terms.'],
                    ['title' => 'Confidentiality', 'content' => 'Both parties agree to keep the commercial terms and any negotiated rates confidential and to use them solely for this Agreement.'],
                    ['title' => 'Term & Renewal', 'content' => 'This Agreement is effective from the effective date and, if auto-renewal is enabled, renews automatically unless either party gives 30 days written notice.'],
                ],
            ],
            [
                'name' => 'Event / Banquet',
                'type' => 'event_banquet',
                'description' => 'Standard banqueting contract covering venue hire, food & beverage and deposits.',
                'clauses' => [
                    ['title' => 'Event Details', 'content' => 'The Hotel shall host the event described in the commercial terms on the agreed date, for the agreed number of guests, in the agreed function venue.'],
                    ['title' => 'Venue Hire & Setup', 'content' => 'Venue hire includes standard setup, tables, chairs, linen and basic décor. The Hotel shall allow reasonable setup and rehearsal time before the event.'],
                    ['title' => 'Deposit & Payment', 'content' => 'A deposit of 50% of the estimated value confirms the date. The balance is due 7 days before the event against the final invoice.'],
                    ['title' => 'Food & Beverage', 'content' => 'The menu is confirmed at least 14 days before the event; the final guest count is confirmed 72 hours before the event and billed in full.'],
                    ['title' => 'Damage & Liability', 'content' => 'The Client is responsible for damage to hotel property caused by the Client or its guests and shall indemnify the Hotel accordingly.'],
                    ['title' => 'Cancellation & Force Majeure', 'content' => 'Cancellation before the deposit deadline is free; thereafter a sliding cancellation charge applies. The Hotel is not liable for events prevented by force majeure.'],
                ],
            ],
            [
                'name' => 'Room Block',
                'type' => 'room_block',
                'description' => 'Waitlist and guaranteed room block contract with attrition and cut-off terms.',
                'clauses' => [
                    ['title' => 'Block Allocation', 'content' => 'The Hotel shall make available the number of rooms and room types set out in the commercial terms for the block period.'],
                    ['title' => 'Rates & Cut-off', 'content' => 'Block rooms are offered at the negotiated rate until the cut-off date. Bookings after cut-off are subject to availability at prevailing rates.'],
                    ['title' => 'Rooming List', 'content' => 'The Client shall submit the final rooming list at least 7 days before the block start date.'],
                    ['title' => 'Attrition', 'content' => 'If actual room pick-up falls below the agreed threshold, the Client shall pay the agreed attrition amount on the unused rooms.'],
                    ['title' => 'Billing', 'content' => 'Occupied rooms are billed nightly against the Client invoice account or the guest folio as agreed in the commercial terms.'],
                    ['title' => 'Release of Unsold Rooms', 'content' => 'Rooms not booked by the cut-off date are released to general sale without penalty to the Hotel.'],
                ],
            ],
            [
                'name' => 'Vendor / Supplier',
                'type' => 'vendor',
                'description' => 'General vendor contract for goods and services supplied to the Hotel.',
                'clauses' => [
                    ['title' => 'Scope of Services', 'content' => 'The Supplier shall provide the goods or services described in the commercial terms to the quality, specification and schedule agreed between the parties.'],
                    ['title' => 'Pricing & Invoicing', 'content' => 'The agreed rates are fixed for the term unless the commercial terms state otherwise. Invoices are payable on net-30 terms from the invoice date.'],
                    ['title' => 'Delivery & Quality Standards', 'content' => 'Goods and services must comply with agreed specifications, relevant standards and safety requirements, and be delivered on the agreed schedule.'],
                    ['title' => 'Warranties', 'content' => 'The Supplier warrants its goods and services for the warranty period and shall remedy any defect notified during that period at no cost.'],
                    ['title' => 'Termination', 'content' => 'Either party may terminate on 30 days written notice. The Hotel may terminate immediately for material breach or non-performance.'],
                ],
            ],
            [
                'name' => 'Long Stay',
                'type' => 'long_stay',
                'description' => 'Extended-stay accommodation contract with utilities, housekeeping and early-exit terms.',
                'clauses' => [
                    ['title' => 'Term', 'content' => 'Accommodation is provided for the minimum period stated in the commercial terms, beginning on the effective date.'],
                    ['title' => 'Rates & Invoicing', 'content' => 'Rates are invoiced weekly or monthly as agreed. Utilities are included unless the commercial terms state otherwise.'],
                    ['title' => 'Housekeeping', 'content' => 'Weekly housekeeping is included. Additional cleaning may be arranged at extra cost.'],
                    ['title' => 'Occupancy Rules', 'content' => 'Maximum occupancy per room applies as set out in the commercial terms. Guests must comply with hotel rules and applicable law.'],
                    ['title' => 'Early Termination', 'content' => 'Early exit requires 30 days written notice; penalties apply as agreed in the commercial terms.'],
                ],
            ],
            [
                'name' => 'Conference',
                'type' => 'conference',
                'description' => 'Conference and seminar contract covering function capacity, equipment, catering and billing.',
                'clauses' => [
                    ['title' => 'Function & Capacity', 'content' => 'The Hotel shall provide the function venue for the conference described in the commercial terms, for the agreed date, time and guest capacity.'],
                    ['title' => 'Equipment & Setup', 'content' => 'Standard audio-visual equipment and the agreed room layout are included. Special equipment is provided at additional cost on request.'],
                    ['title' => 'Catering & Tea Breaks', 'content' => 'Catering and tea-break packages are confirmed before the event and billed per the final guest count.'],
                    ['title' => 'Payment', 'content' => 'A deposit confirms the booking; the balance is payable on or before the event date as agreed in the commercial terms.'],
                    ['title' => 'Liability', 'content' => 'The Hotel is not liable for loss or damage to client equipment or belongings unless caused by Hotel negligence.'],
                ],
            ],
        ];

        foreach ($templates as $data) {
            $clauses = $data['clauses'];
            unset($data['clauses']);

            $template = AgreementTemplate::updateOrCreate(
                ['name' => $data['name']],
                [
                    ...$data,
                    'is_active' => true,
                    'created_by' => $createdBy,
                    'updated_by' => $createdBy,
                ]
            );

            foreach ($clauses as $index => $clause) {
                $template->clauses()->updateOrCreate(
                    ['title' => $clause['title']],
                    [
                        'content' => $clause['content'],
                        'sort_order' => $index,
                    ]
                );
            }
        }
    }
}
