<?php

namespace Modules\Banquet\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Banquet\Models\EventLead;
use Modules\Banquet\Models\LeadEvent;

class EventLeadPageSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'title' => 'Brickspoint New Year Gala 2026',
                'description' => 'Ring in the new year with an unforgettable evening of fine dining, live music, and fireworks at our luxury venue.',
                'event_date' => Carbon::parse('2026-12-31'),
                'location' => 'Brickspoint Boutique Aparthotel, Abuja',
                'organizer' => 'Brickspoint Events',
                'hero_subtitle' => 'Exclusive New Year Celebration',
                'form_heading' => 'Register Your Interest',
                'form_subtext' => 'Be the first to know when tickets go on sale. Fill in your details and our events team will reach out.',
                'thank_you_message' => 'Thank you for your interest! We will contact you with ticket details soon.',
                'is_active' => true,
            ],
            [
                'title' => 'Corporate Conference: Innovation in Hospitality',
                'description' => 'A two-day conference exploring the latest trends in hospitality management, technology, and guest experience.',
                'event_date' => Carbon::parse('2026-09-15'),
                'location' => 'Adamawa Hall, Brickspoint ApartHotel',
                'organizer' => 'Brickspoint Business Hub',
                'hero_subtitle' => 'Industry Leaders Summit',
                'form_heading' => 'Register for the Conference',
                'form_subtext' => 'Limited seats available. Register your interest to secure a spot.',
                'thank_you_message' => 'Your registration has been received. Our team will confirm your attendance shortly.',
                'is_active' => true,
            ],
            [
                'title' => 'Weekend Wedding Expo',
                'description' => 'Plan your dream wedding with top vendors, decor showcases, cake tastings, and exclusive venue tours.',
                'event_date' => Carbon::parse('2026-08-22'),
                'location' => 'Brickspoint Grand Ballroom',
                'organizer' => 'Brickspoint Weddings',
                'hero_subtitle' => 'Your Dream Wedding Starts Here',
                'form_heading' => 'Pre-Register for the Expo',
                'form_subtext' => 'Free entry for pre-registered guests. Bring your partner and start planning!',
                'thank_you_message' => 'You are registered for the Wedding Expo! We look forward to welcoming you.',
                'is_active' => true,
            ],
        ];

        foreach ($events as $data) {
            $event = LeadEvent::firstOrCreate(
                ['title' => $data['title']],
                $data
            );

            if ($event->wasRecentlyCreated && $event->title === 'Brickspoint New Year Gala 2026') {
                EventLead::create([
                    'event_id' => $event->id,
                    'name' => 'Chioma Okafor',
                    'email' => 'chioma@example.com',
                    'phone' => '+234 802 345 6789',
                    'company' => null,
                    'source' => 'Website',
                    'notes' => 'Interested in VIP table for 6 people.',
                    'status' => 'New',
                ]);

                EventLead::create([
                    'event_id' => $event->id,
                    'name' => 'Tunde Bakare',
                    'email' => 'tunde.bakare@example.com',
                    'phone' => '+234 803 987 6543',
                    'company' => 'Bakare Industries Ltd',
                    'source' => 'Social Media',
                    'notes' => 'Wants corporate package for 20 staff.',
                    'status' => 'Contacted',
                ]);

                EventLead::create([
                    'event_id' => $event->id,
                    'name' => 'Amara Eze',
                    'email' => 'amara.eze@example.com',
                    'phone' => '+234 809 112 2334',
                    'company' => null,
                    'source' => 'Referral',
                    'notes' => null,
                    'status' => 'New',
                ]);
            }
        }
    }
}
