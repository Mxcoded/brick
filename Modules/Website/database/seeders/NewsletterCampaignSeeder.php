<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Website\Models\Newsletter;
use Modules\Website\Models\NewsletterSubscriber;

/**
 * Seeds a ready-to-preview newsletter campaign (draft) together with a handful
 * of sample subscribers. Use it to preview how a real campaign renders in the
 * mobile and desktop preview frames without sending anything.
 *
 * Run on demand with:
 *   php artisan db:seed --class="Modules\Website\Database\Seeders\NewsletterCampaignSeeder"
 */
class NewsletterCampaignSeeder extends Seeder
{
    /**
     * Subject used to keep the seeder idempotent.
     */
    protected string $subject = 'October Offers at Brickspoint — An Exclusive Escape Awaits';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedSubscribers();
        $this->seedCampaign();

        $this->command?->info('Newsletter demo campaign seeded successfully!');
        $this->command?->info('   - '.count($this->subscribers()).' sample subscribers available');
        $this->command?->info('   - 1 draft campaign: "'.$this->subject.'"');
    }

    /**
     * Seed a few active subscribers so delivery-style previews look realistic.
     */
    protected function seedSubscribers(): void
    {
        foreach ($this->subscribers() as $subscriber) {
            NewsletterSubscriber::updateOrCreate(
                ['email' => $subscriber['email']],
                [
                    'name' => $subscriber['name'],
                    'is_active' => true,
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                ]
            );
        }
    }

    /**
     * Seed (or refresh) the demo campaign as a draft.
     */
    protected function seedCampaign(): void
    {
        Newsletter::updateOrCreate(
            ['subject' => $this->subject],
            [
                'preview_text' => 'Save 15% on suites, complimentary breakfast, and late checkout this October.',
                'content' => $this->content(),
                'status' => Newsletter::STATUS_DRAFT,
            ]
        );
    }

    /**
     * Sample subscriber list.
     */
    protected function subscribers(): array
    {
        return [
            ['name' => 'John Doe', 'email' => 'john.doe@example.com'],
            ['name' => 'Jane Smith', 'email' => 'jane.smith@example.com'],
            ['name' => 'Amina Bello', 'email' => 'amina.bello@example.com'],
            ['name' => 'Chinedu Okafor', 'email' => 'chinedu.okafor@example.com'],
            ['name' => 'Grace Adeyemi', 'email' => 'grace.adeyemi@example.com'],
        ];
    }

    /**
     * Email-safe HTML content for the campaign body.
     *
     * Uses inline styles and a stacked layout so it degrades gracefully in both
     * desktop and mobile email clients.
     */
    protected function content(): string
    {
        return <<<'HTML'
        <div style="text-align:center; margin:0 0 28px 0;">
            <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1200&auto=format&fit=crop"
                 alt="Brickspoint Boutique Aparthotel"
                 width="560"
                 style="width:100%; max-width:560px; height:auto; display:block; margin:0 auto; border:0; border-radius:8px;">
        </div>

        <h2 style="color:#1A1A1A; font-size:24px; font-weight:600; line-height:1.35; margin:0 0 14px 0;">
            An Exclusive Escape Awaits You
        </h2>

        <p style="margin:0 0 16px 0;">
            Thank you for being part of the Brickspoint family. This month we have curated
            a collection of experiences designed to make your next stay unforgettable —
            from serene suites to memorable dining.
        </p>

        <h3 style="color:#1A1A1A; font-size:18px; font-weight:600; margin:24px 0 10px 0;">
            This Month's Highlights
        </h3>

        <ul style="margin:0 0 20px 0; padding-left:20px; color:#4A4A4A;">
            <li style="margin-bottom:8px;"><strong>15% off</strong> our Executive Studio Suites for stays of 3 nights or more.</li>
            <li style="margin-bottom:8px;"><strong>Complimentary breakfast</strong> for two when you book direct.</li>
            <li style="margin-bottom:8px;"><strong>Late checkout</strong> until 2:00 PM, on the house.</li>
        </ul>

        <div style="text-align:center; margin:30px 0;">
            <a href="https://brickspoint.com/rooms"
               style="display:inline-block; background-color:#C9A962; color:#1A1A1A; font-size:15px; font-weight:700; letter-spacing:1px; text-transform:uppercase; text-decoration:none; padding:16px 40px; border-radius:4px;">
                Reserve Your Stay
            </a>
        </div>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:30px 0;">
            <tr>
                <td style="border-left:4px solid #C9A962; padding:14px 20px; background-color:#FAF7F2; font-style:italic; color:#4A4A4A;">
                    &ldquo;The most restful stay we have had in years. Every detail felt considered.&rdquo;
                    <br>
                    <span style="font-style:normal; font-size:13px; color:#888888;">&mdash; A. Guest, October 2026</span>
                </td>
            </tr>
        </table>

        <p style="margin:0 0 16px 0;">
            We look forward to welcoming you back soon.
        </p>

        <p style="margin:0;">
            Warm regards,<br>
            <strong>The Brickspoint Team</strong>
        </p>
        HTML;
    }
}
