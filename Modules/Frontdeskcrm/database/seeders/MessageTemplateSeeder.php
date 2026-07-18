<?php

namespace Modules\Frontdeskcrm\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Frontdeskcrm\Models\MessageTemplate;

class MessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'event' => 'pre_arrival_reminder',
                'name' => 'Pre-Arrival Reminder',
                'email_subject' => 'Complete Your Pre-Arrival Check-In at {{hotel_name}}',
                'email_body' => "Dear {{guest_title}} {{guest_name}},\n\nWe're excited to welcome you to {{hotel_name}}! Your reservation ({{reservation_code}}) begins on {{check_in}}.\n\nTo save time at the front desk, please complete your pre-arrival check-in by clicking the link below:\n\n{{pre_arrival_link}}\n\nYou can upload your ID documents, provide your estimated arrival time, and sign your registration card in advance.\n\nWarm regards,\n{{hotel_name}} Team",
                'sms_body' => 'Hi {{guest_name}}, complete your pre-arrival check-in for {{reservation_code}} at {{hotel_name}}: {{pre_arrival_link}}',
                'whatsapp_body' => 'Hello {{guest_name}}! 🌟 Your stay at {{hotel_name}} ({{reservation_code}}) starts {{check_in}}. Save time at check-in by completing your pre-arrival form: {{pre_arrival_link}}',
                'placeholders' => '["{{guest_name}}","{{guest_title}}","{{reservation_code}}","{{hotel_name}}","{{check_in}}","{{pre_arrival_link}}"]',
                'is_active' => true,
            ],
            [
                'event' => 'pre_arrival_confirmation',
                'name' => 'Pre-Arrival Confirmation',
                'email_subject' => 'Pre-Arrival Complete — See You at {{hotel_name}}!',
                'email_body' => "Dear {{guest_title}} {{guest_name}},\n\nYour pre-arrival check-in is complete for reservation {{reservation_code}}.\n\nHere's a summary:\n- Check-in: {{check_in}}\n- Check-out: {{check_out}}\n- Room Type: {{room_type}}\n- Nights: {{no_of_nights}}\n\nWhen you arrive, simply present your ID at the front desk to collect your key.\n\nWe look forward to hosting you!\n{{hotel_name}} Team",
                'sms_body' => "You're all set, {{guest_name}}! Pre-arrival complete for {{reservation_code}}. See you at {{hotel_name}} on {{check_in}}.",
                'whatsapp_body' => "All done, {{guest_name}}! ✅ Your pre-arrival for {{reservation_code}} is complete. We'll see you at {{hotel_name}} on {{check_in}}. Safe travels! 🏨",
                'placeholders' => '["{{guest_name}}","{{guest_title}}","{{reservation_code}}","{{hotel_name}}","{{check_in}}","{{check_out}}","{{room_type}}","{{no_of_nights}}"]',
                'is_active' => true,
            ],
            [
                'event' => 'check_in_welcome',
                'name' => 'Check-In Welcome',
                'email_subject' => 'Welcome to {{hotel_name}}, {{guest_name}}!',
                'email_body' => "Dear {{guest_title}} {{guest_name}},\n\nWelcome to {{hotel_name}}! We're delighted to have you with us.\n\nYour room ({{room_type}}{{room_number ? ' - Room ' . room_number : ''}}) is ready. If there's anything we can do to make your stay more comfortable, please don't hesitate to reach out to the front desk.\n\nEnjoy your stay!\n{{hotel_name}} Team",
                'sms_body' => 'Welcome to {{hotel_name}}, {{guest_name}}! Your room is ready. Front desk: dial 0 for any assistance.',
                'whatsapp_body' => 'Welcome to {{hotel_name}}, {{guest_name}}! 🎉 Your {{room_type}} is ready for you. Need anything? Just let us know!',
                'placeholders' => '["{{guest_name}}","{{guest_title}}","{{hotel_name}}","{{room_type}}","{{room_number}}"]',
                'is_active' => true,
            ],
            [
                'event' => 'checkout_receipt',
                'name' => 'Checkout Receipt',
                'email_subject' => 'Your Stay Receipt — {{hotel_name}}',
                'email_body' => "Dear {{guest_title}} {{guest_name}},\n\nThank you for staying with us at {{hotel_name}}.\n\nReservation: {{reservation_code}}\nCheck-in: {{check_in}} | Check-out: {{check_out}}\nRoom Type: {{room_type}}\nTotal Amount: ₦{{total_amount}}\n\nWe hope you had a wonderful experience. If you have any feedback, we'd love to hear from you.\n\nSafe travels!\n{{hotel_name}} Team",
                'sms_body' => 'Thank you for staying at {{hotel_name}}, {{guest_name}}! Receipt for {{reservation_code}} has been sent to your email.',
                'whatsapp_body' => 'Thank you for choosing {{hotel_name}}, {{guest_name}}! 🙏 Your checkout receipt for {{reservation_code}} is on its way to your email. We hope to see you again soon!',
                'placeholders' => '["{{guest_name}}","{{guest_title}}","{{reservation_code}}","{{hotel_name}}","{{check_in}}","{{check_out}}","{{room_type}}","{{total_amount}}"]',
                'is_active' => true,
            ],
            [
                'event' => 'review_request',
                'name' => 'Post-Stay Review Request',
                'email_subject' => 'How Was Your Stay at {{hotel_name}}?',
                'email_body' => "Dear {{guest_title}} {{guest_name}},\n\nWe hope you enjoyed your stay at {{hotel_name}}! We'd love to hear about your experience.\n\nYour feedback helps us improve and serve future guests better. Please take a moment to leave a review.\n\nClick here to share your feedback: https://{{hotel_name}}/review/{{reservation_code}}\n\nThank you for choosing us!\n{{hotel_name}} Team",
                'sms_body' => 'Hi {{guest_name}}, how was your stay at {{hotel_name}}? Share your feedback: https://review/{{reservation_code}}',
                'whatsapp_body' => "Hello {{guest_name}}! 👋 We hope you loved your stay at {{hotel_name}}. We'd love to hear your thoughts — leave a review here: https://review/{{reservation_code}}",
                'placeholders' => '["{{guest_name}}","{{guest_title}}","{{hotel_name}}","{{reservation_code}}"]',
                'is_active' => true,
            ],
            [
                'event' => 're_engagement',
                'name' => 'Re-Engagement Offer',
                'email_subject' => 'We Miss You at {{hotel_name}}, {{guest_name}}!',
                'email_body' => "Dear {{guest_title}} {{guest_name}},\n\nIt's been a while since your last stay at {{hotel_name}}, and we've missed you!\n\nAs a valued guest, we'd like to offer you an exclusive discount on your next booking. Use code WELCOMEBACK for 15% off your stay.\n\nBook now: https://{{hotel_name}}/book\n\nWe can't wait to welcome you back!\n{{hotel_name}} Team",
                'sms_body' => 'We miss you, {{guest_name}}! Book your next stay at {{hotel_name}} with code WELCOMEBACK for 15% off. https://book',
                'whatsapp_body' => "Hey {{guest_name}}! 👋 It's been a while since you visited {{hotel_name}}. We've got a special 15% discount waiting for you — use code WELCOMEBACK on your next booking. Book here: https://book",
                'placeholders' => '["{{guest_name}}","{{guest_title}}","{{hotel_name}}"]',
                'is_active' => true,
            ],
            [
                'event' => 'birthday',
                'name' => 'Birthday Wishes',
                'email_subject' => 'Happy Birthday from {{hotel_name}}!',
                'email_body' => "Dear {{guest_title}} {{guest_name}},\n\nWishing you a very happy birthday from all of us at {{hotel_name}}!\n\nAs a birthday treat, enjoy 20% off your next stay with us. Use code BIRTHDAY20 when booking.\n\nWe hope you have a fantastic day!\n{{hotel_name}} Team",
                'sms_body' => 'Happy Birthday, {{guest_name}}! 🎂 Enjoy 20% off your next stay at {{hotel_name}} with code BIRTHDAY20.',
                'whatsapp_body' => 'Happy Birthday, {{guest_name}}! 🎉🎂 From all of us at {{hotel_name}}, have a wonderful day! As a gift, enjoy 20% off your next stay with code BIRTHDAY20.',
                'placeholders' => '["{{guest_name}}","{{guest_title}}","{{hotel_name}}"]',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            MessageTemplate::updateOrCreate(
                ['event' => $template['event']],
                $template
            );
        }
    }
}
