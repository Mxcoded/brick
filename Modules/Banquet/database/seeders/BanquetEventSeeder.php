<?php

namespace Modules\Banquet\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Banquet\Models\BanquetOrder;
use Modules\Banquet\Models\BanquetOrderDay;
use Modules\Banquet\Models\BanquetOrderMenuItem;
use Modules\Banquet\Models\BanquetPayment;
use Modules\Banquet\Models\BanquetSetupStyle;
use Modules\Banquet\Models\BanquetVenue;
use Modules\Banquet\Models\Customer;
use Modules\Finance\Database\Seeders\ChartOfAccountsSeeder;
use Modules\Finance\Services\PostingService;

class BanquetEventSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BanquetVenueSeeder::class,
            BanquetSetupStyleSeeder::class,
            ChartOfAccountsSeeder::class,
        ]);

        $posting = app(PostingService::class);

        $events = [
            [
                'customer' => ['name' => 'Adaeze Obioma', 'email' => 'adaeze.obioma@example.com', 'phone' => '08012345001', 'organization' => 'Obioma Family'],
                'order' => [
                    'preparation_date' => now()->subDays(5)->toDateString(),
                    'contact_person_name' => 'Adaeze Obioma',
                    'contact_person_phone' => '08012345001',
                    'contact_person_email' => 'adaeze.obioma@example.com',
                    'hall_rental_fees' => 500000,
                    'total_revenue' => 1850000,
                    'expenses' => 620000,
                    'profit_margin' => 1230000,
                    'status' => 'Confirmed',
                ],
                'days' => [
                    [
                        'event_date' => now()->addDays(7)->toDateString(),
                        'event_description' => 'Wedding Reception of Nkechi & Emeka',
                        'guest_count' => 180,
                        'event_status' => 'Confirmed',
                        'event_type' => 'Wedding',
                        'venue' => 'Adamawa Hall',
                        'setup_style' => 'Banquet Style',
                        'start_time' => '16:00',
                        'end_time' => '22:00',
                        'duration_minutes' => 360,
                        'menu' => [
                            ['meal_type' => 'Main Course', 'menu_items' => ['Jollof Rice', 'Grilled Chicken', 'Moi Moi', 'Coleslaw'], 'quantity' => 180, 'unit_price' => 6000, 'total_price' => 1080000],
                            ['meal_type' => 'Dessert', 'menu_items' => ['Wedding Cake', 'Chocolate Puff Puff'], 'quantity' => 180, 'unit_price' => 1500, 'total_price' => 270000],
                        ],
                    ],
                ],
                'payments' => [
                    ['amount' => 700000, 'payment_date' => now()->subDays(4)->toDateString(), 'payment_method' => 'cash', 'reference' => 'BP-ADAEZE-001', 'notes' => 'Advance deposit'],
                    ['amount' => 1000000, 'payment_date' => now()->subDay()->toDateString(), 'payment_method' => 'transfer', 'reference' => 'TRF-88231', 'notes' => 'Balance top-up'],
                ],
            ],
            [
                'customer' => ['name' => 'Dele Okafor', 'email' => 'dele.okafor@zenicorp.com', 'phone' => '08012345002', 'organization' => 'ZeniCorp Ltd'],
                'order' => [
                    'preparation_date' => now()->subDays(10)->toDateString(),
                    'contact_person_name' => 'Dele Okafor',
                    'contact_person_phone' => '08012345002',
                    'contact_person_email' => 'dele.okafor@zenicorp.com',
                    'hall_rental_fees' => 1360000,
                    'total_revenue' => 3460000,
                    'expenses' => 980000,
                    'profit_margin' => 2480000,
                    'status' => 'Confirmed',
                ],
                'days' => [
                    [
                        'event_date' => now()->addDays(14)->toDateString(),
                        'event_description' => 'Annual Corporate Conference - Day 1',
                        'guest_count' => 350,
                        'event_status' => 'Confirmed',
                        'event_type' => 'Conference',
                        'venue' => 'Adamawa Hall + Kano Hall',
                        'setup_style' => 'Theater Style',
                        'start_time' => '09:00',
                        'end_time' => '17:00',
                        'duration_minutes' => 480,
                        'menu' => [
                            ['meal_type' => 'Lunch', 'menu_items' => ['Fried Rice', 'Beef Stew', 'Vegetable Salad'], 'quantity' => 350, 'unit_price' => 3000, 'total_price' => 1050000],
                        ],
                    ],
                    [
                        'event_date' => now()->addDays(15)->toDateString(),
                        'event_description' => 'Annual Corporate Conference - Day 2',
                        'guest_count' => 350,
                        'event_status' => 'Confirmed',
                        'event_type' => 'Conference',
                        'venue' => 'Adamawa Hall + Kano Hall',
                        'setup_style' => 'Classroom Style',
                        'start_time' => '09:00',
                        'end_time' => '15:00',
                        'duration_minutes' => 360,
                        'menu' => [
                            ['meal_type' => 'Lunch', 'menu_items' => ['Jollof Rice', 'Chicken Wings', 'Plantain'], 'quantity' => 350, 'unit_price' => 3000, 'total_price' => 1050000],
                        ],
                    ],
                ],
                'payments' => [
                    ['amount' => 1500000, 'payment_date' => now()->subDays(8)->toDateString(), 'payment_method' => 'transfer', 'reference' => 'TRF-551092', 'notes' => 'Conference deposit'],
                    ['amount' => 1960000, 'payment_date' => now()->subDays(2)->toDateString(), 'payment_method' => 'cheque', 'reference' => 'CHQ-00221', 'notes' => 'Final settlement'],
                ],
            ],
            [
                'customer' => ['name' => 'Fatima Bello', 'email' => 'fatima.bello@example.com', 'phone' => '08012345003', 'organization' => 'Bello Family'],
                'order' => [
                    'preparation_date' => now()->subDays(3)->toDateString(),
                    'contact_person_name' => 'Fatima Bello',
                    'contact_person_phone' => '08012345003',
                    'contact_person_email' => 'fatima.bello@example.com',
                    'hall_rental_fees' => 120000,
                    'total_revenue' => 420000,
                    'expenses' => 150000,
                    'profit_margin' => 270000,
                    'status' => 'Completed',
                ],
                'days' => [
                    [
                        'event_date' => now()->subDays(1)->toDateString(),
                        'event_description' => 'Birthday Pool Party',
                        'guest_count' => 60,
                        'event_status' => 'Confirmed',
                        'event_type' => 'Banquet',
                        'venue' => 'Pool Party',
                        'setup_style' => 'Reception',
                        'start_time' => '14:00',
                        'end_time' => '20:00',
                        'duration_minutes' => 360,
                        'menu' => [
                            ['meal_type' => 'Snacks', 'menu_items' => ['Pepper Soup', 'Small Chops', 'Soft Drinks'], 'quantity' => 60, 'unit_price' => 4000, 'total_price' => 240000],
                            ['meal_type' => 'Cake', 'menu_items' => ['Birthday Cake'], 'quantity' => 60, 'unit_price' => 1000, 'total_price' => 60000],
                        ],
                    ],
                ],
                'payments' => [
                    ['amount' => 420000, 'payment_date' => now()->subDays(2)->toDateString(), 'payment_method' => 'pos', 'reference' => 'POS-91334', 'notes' => 'Payment in full'],
                ],
            ],
            [
                'customer' => ['name' => 'Chidi Nwosu', 'email' => 'chidi.nwosu@boardworks.com', 'phone' => '08012345004', 'organization' => 'BoardWorks Plc'],
                'order' => [
                    'preparation_date' => now()->subDay()->toDateString(),
                    'contact_person_name' => 'Chidi Nwosu',
                    'contact_person_phone' => '08012345004',
                    'contact_person_email' => 'chidi.nwosu@boardworks.com',
                    'hall_rental_fees' => 60000,
                    'total_revenue' => 90000,
                    'expenses' => 25000,
                    'profit_margin' => 65000,
                    'status' => 'Pending',
                ],
                'days' => [
                    [
                        'event_date' => now()->addDays(3)->toDateString(),
                        'event_description' => 'Executive Board Meeting',
                        'guest_count' => 12,
                        'event_status' => 'Pending',
                        'event_type' => 'Meeting',
                        'venue' => 'Board Room',
                        'setup_style' => 'Boardroom Style',
                        'start_time' => '10:00',
                        'end_time' => '13:00',
                        'duration_minutes' => 180,
                        'menu' => [
                            ['meal_type' => 'Light Refreshments', 'menu_items' => ['Tea', 'Coffee', 'Pastries'], 'quantity' => 12, 'unit_price' => 2500, 'total_price' => 30000],
                        ],
                    ],
                ],
                'payments' => [],
            ],
        ];

        foreach ($events as $index => $event) {
            $customer = Customer::firstOrCreate(
                ['email' => $event['customer']['email']],
                $event['customer']
            );

            $orderId = sprintf('%04d-%d', $index + 1, now()->year);

            $order = BanquetOrder::firstOrCreate(
                ['order_id' => $orderId],
                $event['order'] + ['customer_id' => $customer->id]
            );

            foreach ($event['days'] as $dayData) {
                $venue = BanquetVenue::where('name', $dayData['venue'])->first();
                $style = BanquetSetupStyle::where('name', $dayData['setup_style'])->first();

                $menu = $dayData['menu'] ?? [];
                $setupStyleName = $dayData['setup_style'] ?? null;
                unset($dayData['venue'], $dayData['setup_style'], $dayData['menu']);

                $day = BanquetOrderDay::firstOrCreate(
                    [
                        'banquet_order_id' => $order->id,
                        'event_date' => $dayData['event_date'],
                        'event_description' => $dayData['event_description'],
                    ],
                    $dayData + [
                        'banquet_venue_id' => $venue?->id,
                        'banquet_setup_style_id' => $style?->id,
                        'room' => $dayData['event_type'],
                        'setup_style' => $setupStyleName,
                    ]
                );

                foreach ($menu as $menuItem) {
                    BanquetOrderMenuItem::firstOrCreate(
                        [
                            'banquet_order_day_id' => $day->id,
                            'meal_type' => $menuItem['meal_type'],
                        ],
                        $menuItem + ['dietary_restrictions' => []]
                    );
                }
            }

            foreach ($event['payments'] as $paymentData) {
                $payment = BanquetPayment::firstOrCreate(
                    [
                        'banquet_order_id' => $order->id,
                        'reference' => $paymentData['reference'],
                    ],
                    $paymentData
                );

                // Mirror BanquetController::storePayment() — post sale to Finance COA.
                $posting->recordSale(
                    'banquet',
                    (float) $payment->amount,
                    $payment->payment_method,
                    'banquet_payment',
                    $payment->id
                );
            }
        }
    }
}
