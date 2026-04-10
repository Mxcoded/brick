<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Website\Models\Newsletter;
use Modules\Website\Models\NewsletterSubscriber;
use Modules\Website\Jobs\SendNewsletterJob;

class NewsletterController extends Controller
{
    // ==========================================
    // SUBSCRIBER MANAGEMENT
    // ==========================================

    /**
     * Display all newsletter subscribers.
     */
    public function subscribersIndex(Request $request)
    {
        $query = NewsletterSubscriber::latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search
        if ($request->filled('search')) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }

        $subscribers = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => NewsletterSubscriber::count(),
            'active' => NewsletterSubscriber::active()->count(),
            'inactive' => NewsletterSubscriber::where('is_active', false)->count(),
        ];

        return view('website::admin.newsletter.subscribers', compact('subscribers', 'stats'));
    }

    /**
     * Export subscribers to CSV
     */
    public function exportSubscribers()
    {
        $subscribers = NewsletterSubscriber::active()->get();

        $filename = 'newsletter_subscribers_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($subscribers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Email', 'Subscribed At', 'Status']);
            
            foreach ($subscribers as $sub) {
                fputcsv($file, [
                    $sub->email,
                    $sub->subscribed_at?->format('Y-m-d H:i:s'),
                    $sub->is_active ? 'Active' : 'Inactive',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Delete a subscriber
     */
    public function destroySubscriber(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();
        return redirect()->route('website.admin.newsletter.subscribers')
            ->with('success', 'Subscriber removed successfully.');
    }

    /**
     * Toggle subscriber status
     */
    public function toggleSubscriberStatus(NewsletterSubscriber $subscriber)
    {
        $subscriber->update([
            'is_active' => !$subscriber->is_active,
            'unsubscribed_at' => $subscriber->is_active ? now() : null,
        ]);

        $status = $subscriber->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Subscriber {$status} successfully.");
    }

    // ==========================================
    // CAMPAIGN MANAGEMENT
    // ==========================================

    /**
     * Display all newsletter campaigns.
     */
    public function index(Request $request)
    {
        $query = Newsletter::with('author')->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by subject
        if ($request->filled('search')) {
            $query->where('subject', 'like', '%' . $request->search . '%');
        }

        $newsletters = $query->paginate(15)->withQueryString();

        // Campaign stats
        $stats = [
            'total' => Newsletter::count(),
            'draft' => Newsletter::draft()->count(),
            'scheduled' => Newsletter::scheduled()->count(),
            'sent' => Newsletter::sent()->count(),
            'subscribers' => NewsletterSubscriber::active()->count(),
        ];

        return view('website::admin.newsletter.campaigns.index', compact('newsletters', 'stats'));
    }

    /**
     * Show form for creating a new campaign.
     */
    public function create()
    {
        $subscriberCount = NewsletterSubscriber::active()->count();
        return view('website::admin.newsletter.campaigns.compose', compact('subscriberCount'));
    }

    /**
     * Store a new newsletter campaign.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'preview_text' => 'nullable|string|max:500',
            'content' => 'required|string',
            'action' => 'required|in:draft,schedule,send',
            'scheduled_at' => 'required_if:action,schedule|nullable|date|after:now',
        ]);

        $newsletter = Newsletter::create([
            'subject' => $validated['subject'],
            'preview_text' => $validated['preview_text'] ?? null,
            'content' => $validated['content'],
            'status' => Newsletter::STATUS_DRAFT,
            'created_by' => auth()->id(),
        ]);

        // Handle action
        if ($validated['action'] === 'schedule') {
            $newsletter->update([
                'status' => Newsletter::STATUS_SCHEDULED,
                'scheduled_at' => $validated['scheduled_at'],
            ]);
            return redirect()->route('website.admin.newsletter.campaigns.index')
                ->with('success', 'Newsletter scheduled for ' . $newsletter->scheduled_at->format('M d, Y \a\t h:i A'));
        }

        if ($validated['action'] === 'send') {
            return $this->dispatchNewsletter($newsletter);
        }

        return redirect()->route('website.admin.newsletter.campaigns.index')
            ->with('success', 'Newsletter draft saved successfully.');
    }

    /**
     * Show the newsletter campaign details.
     */
    public function show(Newsletter $campaign)
    {
        return view('website::admin.newsletter.campaigns.show', compact('campaign'));
    }

    /**
     * Show form for editing a campaign.
     */
    public function edit(Newsletter $campaign)
    {
        if (!$campaign->canEdit()) {
            return redirect()->route('website.admin.newsletter.campaigns.index')
                ->with('error', 'This newsletter cannot be edited.');
        }

        $subscriberCount = NewsletterSubscriber::active()->count();
        return view('website::admin.newsletter.campaigns.compose', [
            'newsletter' => $campaign,
            'subscriberCount' => $subscriberCount,
        ]);
    }

    /**
     * Update a newsletter campaign.
     */
    public function update(Request $request, Newsletter $campaign)
    {
        if (!$campaign->canEdit()) {
            return redirect()->route('website.admin.newsletter.campaigns.index')
                ->with('error', 'This newsletter cannot be edited.');
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'preview_text' => 'nullable|string|max:500',
            'content' => 'required|string',
            'action' => 'required|in:draft,schedule,send',
            'scheduled_at' => 'required_if:action,schedule|nullable|date|after:now',
        ]);

        $campaign->update([
            'subject' => $validated['subject'],
            'preview_text' => $validated['preview_text'] ?? null,
            'content' => $validated['content'],
        ]);

        // Handle action
        if ($validated['action'] === 'schedule') {
            $campaign->update([
                'status' => Newsletter::STATUS_SCHEDULED,
                'scheduled_at' => $validated['scheduled_at'],
            ]);
            return redirect()->route('website.admin.newsletter.campaigns.index')
                ->with('success', 'Newsletter scheduled for ' . $campaign->fresh()->scheduled_at->format('M d, Y \a\t h:i A'));
        }

        if ($validated['action'] === 'send') {
            return $this->dispatchNewsletter($campaign);
        }

        // Save as draft
        $campaign->update(['status' => Newsletter::STATUS_DRAFT, 'scheduled_at' => null]);
        return redirect()->route('website.admin.newsletter.campaigns.index')
            ->with('success', 'Newsletter draft updated successfully.');
    }

    /**
     * Delete a newsletter campaign.
     */
    public function destroy(Newsletter $campaign)
    {
        $campaign->delete();
        return redirect()->route('website.admin.newsletter.campaigns.index')
            ->with('success', 'Newsletter deleted successfully.');
    }

    /**
     * Preview a newsletter.
     */
    public function preview(Newsletter $campaign)
    {
        // Create a dummy subscriber for preview
        $subscriber = new NewsletterSubscriber([
            'email' => auth()->user()->email ?? 'preview@example.com',
            'unsubscribe_token' => 'preview-token',
        ]);

        $unsubscribeUrl = route('website.newsletter.unsubscribe', ['token' => 'preview-token']);

        return view('website::emails.newsletter', [
            'newsletter' => $campaign,
            'subscriber' => $subscriber,
            'unsubscribeUrl' => $unsubscribeUrl,
        ]);
    }

    /**
     * Send a newsletter immediately.
     */
    public function send(Newsletter $campaign)
    {
        if (!$campaign->canSend()) {
            return redirect()->route('website.admin.newsletter.campaigns.index')
                ->with('error', 'This newsletter cannot be sent.');
        }

        return $this->dispatchNewsletter($campaign);
    }

    /**
     * Duplicate a newsletter campaign.
     */
    public function duplicate(Newsletter $campaign)
    {
        $newCampaign = $campaign->duplicate();
        return redirect()->route('website.admin.newsletter.campaigns.edit', $newCampaign)
            ->with('success', 'Newsletter duplicated. You can now edit and send it.');
    }

    /**
     * Send test email to admin.
     */
    public function sendTest(Request $request, Newsletter $campaign)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $subscriber = new NewsletterSubscriber([
                'email' => $request->email,
                'unsubscribe_token' => 'test-' . bin2hex(random_bytes(16)),
            ]);

            \Mail::to($request->email)->send(
                new \Modules\Website\Emails\NewsletterMail($campaign, $subscriber)
            );

            return response()->json(['success' => true, 'message' => 'Test email sent to ' . $request->email]);
        } catch (\Exception $e) {
            Log::error('Failed to send test newsletter', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to send: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Dispatch newsletter to all active subscribers.
     */
    protected function dispatchNewsletter(Newsletter $newsletter)
    {
        $subscribers = NewsletterSubscriber::active()->get();
        $count = $subscribers->count();

        if ($count === 0) {
            return redirect()->route('website.admin.newsletter.campaigns.index')
                ->with('error', 'No active subscribers to send to.');
        }

        // Mark newsletter as sending
        $newsletter->markAsSending($count);

        // Dispatch jobs for each subscriber
        foreach ($subscribers as $subscriber) {
            SendNewsletterJob::dispatch($newsletter, $subscriber);
        }

        // Mark as sent (jobs will update counts)
        $newsletter->markAsSent();

        Log::info('Newsletter dispatched', [
            'newsletter_id' => $newsletter->id,
            'recipients' => $count,
        ]);

        return redirect()->route('website.admin.newsletter.campaigns.index')
            ->with('success', "Newsletter is being sent to {$count} subscribers.");
    }

    // ==========================================
    // PUBLIC ROUTES (Unsubscribe)
    // ==========================================

    /**
     * Handle unsubscribe request.
     */
    public function unsubscribe(string $token)
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->first();

        if (!$subscriber) {
            return view('website::newsletter.unsubscribe', [
                'success' => false,
                'message' => 'Invalid unsubscribe link.',
            ]);
        }

        if (!$subscriber->is_active) {
            return view('website::newsletter.unsubscribe', [
                'success' => true,
                'message' => 'You have already been unsubscribed.',
            ]);
        }

        $subscriber->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);

        return view('website::newsletter.unsubscribe', [
            'success' => true,
            'message' => 'You have been successfully unsubscribed from our newsletter.',
        ]);
    }
}
