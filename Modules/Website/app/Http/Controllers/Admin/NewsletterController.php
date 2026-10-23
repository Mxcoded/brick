<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Website\Emails\NewsletterMail;
use Modules\Website\Imports\NewsletterSubscriberImport;
use Modules\Website\Models\Newsletter;
use Modules\Website\Models\NewsletterDeliveryLog;
use Modules\Website\Models\NewsletterSubscriber;
use Modules\Website\Services\NewsletterSendingService;

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
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            });
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

        $filename = 'newsletter_subscribers_'.date('Y-m-d').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($subscribers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Email', 'Subscribed At', 'Status']);

            foreach ($subscribers as $sub) {
                fputcsv($file, [
                    $sub->name ?? '',
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
     * Import subscribers from Excel/CSV.
     */
    public function importSubscribers(Request $request)
    {
        $request->validate([
            'file' => 'required|file|extensions:csv,xls,xlsx|max:5120',
        ]);

        $import = new NewsletterSubscriberImport;
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            Log::error('Newsletter import failed to read the uploaded file', [
                'message' => $e->getMessage(),
                'original_name' => $request->file('file')->getClientOriginalName(),
            ]);

            throw ValidationException::withMessages([
                'file' => 'The uploaded file could not be read. Make sure it is a valid CSV or Excel file with a "name" and "email" column in the first row.',
            ]);
        }

        $imported = $import->getImportedCount();
        $reactivated = $import->getReactivatedCount();
        $skipped = $import->getSkippedCount();

        $parts = [];
        if ($imported > 0) {
            $parts[] = "{$imported} new subscribers added";
        }
        if ($reactivated > 0) {
            $parts[] = "{$reactivated} reactivated";
        }
        if ($skipped > 0) {
            $parts[] = "{$skipped} already existed";
        }

        $message = 'Import completed. '.(empty($parts) ? 'No changes made.' : implode(', ', $parts).'.');

        $failures = $import->failures();
        $failureCount = $failures->count();

        if ($failureCount > 0) {
            Log::warning('Newsletter import had failures', [
                'count' => $failureCount,
                'failures' => $failures->map(fn ($f) => [
                    'row' => $f->row(),
                    'attribute' => $f->attribute(),
                    'errors' => $f->errors(),
                    'values' => $f->values(),
                ])->toArray(),
            ]);
        }

        return redirect()->route('website.admin.newsletter.subscribers')
            ->with('success', $message)
            ->with('import_failures', $failureCount)
            ->with('import_failure_details', $failures->take(20)->map(fn ($f) => [
                'row' => $f->row(),
                'email' => (string) data_get($f->values(), 'email', '—'),
                'error' => $f->errors()[0] ?? 'This row could not be read.',
            ])->values()->all());
    }

    /**
     * Download a sample CSV template for importing subscribers.
     */
    public function downloadSampleImport()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="newsletter_import_sample.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['name', 'email']);
            fputcsv($file, ['John Doe', 'john.doe@gmail.com']);
            fputcsv($file, ['Jane Smith', 'jane.smith@gmail.com']);
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
            'is_active' => ! $subscriber->is_active,
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
            $query->where('subject', 'like', '%'.$request->search.'%');
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
                ->with('success', 'Newsletter scheduled for '.$newsletter->scheduled_at->format('M d, Y \a\t h:i A'));
        }

        if ($validated['action'] === 'send') {
            return $this->dispatchNewsletter($newsletter);
        }

        return redirect()->route('website.admin.newsletter.campaigns.index')
            ->with('success', 'Newsletter draft saved successfully.');
    }

    /**
     * Autosave an in-progress campaign without touching its status.
     */
    public function autosave(Request $request, Newsletter $campaign)
    {
        if (! $campaign->canEdit()) {
            return response()->json(['message' => 'This newsletter can no longer be edited.'], 409);
        }

        $validated = $request->validate([
            'subject' => 'nullable|string|max:255',
            'preview_text' => 'nullable|string|max:500',
            'content' => 'nullable|string',
        ]);

        $campaign->fill(array_intersect_key($validated, array_flip(['subject', 'preview_text', 'content'])))->save();

        return response()->json(['saved_at' => now()->toDateTimeString()]);
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
        if (! $campaign->canEdit()) {
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
        if (! $campaign->canEdit()) {
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
                ->with('success', 'Newsletter scheduled for '.$campaign->fresh()->scheduled_at->format('M d, Y \a\t h:i A'));
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
     * Preview a newsletter inside desktop and mobile device frames.
     */
    public function preview(Newsletter $campaign)
    {
        return view('website::admin.newsletter.campaigns.preview', compact('campaign'));
    }

    /**
     * Render the raw newsletter email HTML.
     *
     * Used as the source document for the desktop/mobile frames on the preview
     * page, so the email renders in isolation at its true responsive width.
     */
    public function previewEmail(Newsletter $campaign)
    {
        // Render the real mailable with a sample subscriber so the preview shows
        // exactly what recipients see, including merge tags and the greeting.
        $subscriber = new NewsletterSubscriber([
            'name' => auth()->user()->name,
            'email' => auth()->user()->email ?? 'preview@example.com',
            'unsubscribe_token' => 'preview-token',
        ]);

        return response((new NewsletterMail($campaign, $subscriber))->render());
    }

    /**
     * Send a newsletter immediately.
     */
    public function send(Newsletter $campaign)
    {
        if (! $campaign->canSend()) {
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
        $validated = $request->validate([
            'email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'preview_text' => 'nullable|string|max:500',
            'content' => 'nullable|string',
        ]);

        try {
            $subscriber = new NewsletterSubscriber([
                'name' => auth()->user()->name,
                'email' => $request->email,
                'unsubscribe_token' => 'test-'.bin2hex(random_bytes(16)),
            ]);

            // Send the campaign as it currently looks in the editor, even if the
            // draft has not been saved since the last edit.
            $previewCampaign = $campaign->replicate();
            if ($request->filled('subject')) {
                $previewCampaign->subject = $validated['subject'];
            }
            if ($request->has('preview_text')) {
                $previewCampaign->preview_text = $validated['preview_text'];
            }
            if ($request->filled('content')) {
                $previewCampaign->content = $validated['content'];
            }

            \Mail::to($request->email)->send(
                new NewsletterMail($previewCampaign, $subscriber)
            );

            return response()->json(['success' => true, 'message' => 'Test email sent to '.$request->email]);
        } catch (\Exception $e) {
            Log::error('Failed to send test newsletter', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to send: '.$e->getMessage()], 500);
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
        $result = app(NewsletterSendingService::class)->send($newsletter);

        if ($result['recipients'] === 0) {
            return redirect()->route('website.admin.newsletter.campaigns.index')
                ->with('error', 'No active subscribers to send to.');
        }

        // Redirect to delivery status page showing results
        return redirect()->route('website.admin.newsletter.campaigns.delivery-status', $newsletter)
            ->with('success', "Newsletter sent! {$result['sent']} delivered".($result['failed'] > 0 ? ", {$result['failed']} failed" : '').'.');
    }

    /**
     * Get real-time delivery status for a newsletter.
     */
    public function deliveryStatus(Newsletter $campaign)
    {
        $stats = [
            'total' => $campaign->recipients_count,
            'sent' => $campaign->deliveryLogs()->where('status', 'sent')->count(),
            'failed' => $campaign->deliveryLogs()->where('status', 'failed')->count(),
            'pending' => $campaign->deliveryLogs()->where('status', 'pending')->count(),
        ];

        $failedEmails = $campaign->deliveryLogs()
            ->where('status', 'failed')
            ->with('subscriber:id,email')
            ->latest('failed_at')
            ->get();

        return view('website::admin.newsletter.campaigns.delivery-status', compact('campaign', 'stats', 'failedEmails'));
    }

    /**
     * API: Get delivery status for polling.
     */
    public function deliveryStatusApi(Newsletter $campaign)
    {
        $stats = [
            'total' => $campaign->recipients_count,
            'sent' => $campaign->deliveryLogs()->where('status', 'sent')->count(),
            'failed' => $campaign->deliveryLogs()->where('status', 'failed')->count(),
            'pending' => $campaign->deliveryLogs()->where('status', 'pending')->count(),
            'status' => $campaign->status,
            'sent_count' => $campaign->sent_count,
            'failed_count' => $campaign->failed_count,
        ];

        // Calculate progress percentage
        $stats['progress'] = $stats['total'] > 0
            ? round((($stats['sent'] + $stats['failed']) / $stats['total']) * 100, 1)
            : 0;

        // Check if all emails have been processed
        $stats['completed'] = ($stats['sent'] + $stats['failed']) >= $stats['total'];

        // If completed, update newsletter status
        if ($stats['completed'] && $campaign->status === Newsletter::STATUS_SENDING) {
            $campaign->markAsSent();
            $stats['status'] = Newsletter::STATUS_SENT;
        }

        // Get recent failed emails
        $stats['recent_failures'] = $campaign->deliveryLogs()
            ->where('status', 'failed')
            ->latest('failed_at')
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'email' => $log->email,
                    'error' => $log->error_message,
                    'failed_at' => $log->failed_at?->diffForHumans(),
                ];
            });

        return response()->json($stats);
    }

    /**
     * Retry failed deliveries for a newsletter.
     */
    public function retryFailed(Newsletter $campaign)
    {
        $failedLogs = $campaign->deliveryLogs()->where('status', 'failed')->with('subscriber')->get();
        $count = $failedLogs->count();

        if ($count === 0) {
            return redirect()->back()->with('info', 'No failed deliveries to retry.');
        }

        $sentCount = 0;
        $stillFailed = 0;

        foreach ($failedLogs as $log) {
            // Reset the log status
            $log->update([
                'status' => NewsletterDeliveryLog::STATUS_PENDING,
                'error_message' => null,
            ]);

            try {
                // Send email directly
                $log->subscriber->ensureUnsubscribeToken();

                \Mail::to($log->email)->send(
                    new NewsletterMail($campaign, $log->subscriber)
                );

                $log->markAsSent();
                $sentCount++;

            } catch (\Exception $e) {
                $log->markAsFailed($e->getMessage());
                $stillFailed++;

                Log::error('Newsletter retry failed', [
                    'newsletter_id' => $campaign->id,
                    'subscriber_email' => $log->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Update newsletter counts
        $campaign->update([
            'sent_count' => $campaign->sent_count + $sentCount,
            'failed_count' => $stillFailed,
        ]);

        Log::info('Retried failed newsletter deliveries', [
            'newsletter_id' => $campaign->id,
            'retry_count' => $count,
            'sent' => $sentCount,
            'still_failed' => $stillFailed,
        ]);

        $message = "{$sentCount} of {$count} retried successfully";
        if ($stillFailed > 0) {
            $message .= ", {$stillFailed} still failed";
        }

        return redirect()->route('website.admin.newsletter.campaigns.delivery-status', $campaign)
            ->with('success', $message.'.');
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

        if (! $subscriber) {
            return view('website::newsletter.unsubscribe', [
                'success' => false,
                'message' => 'Invalid unsubscribe link.',
            ]);
        }

        if (! $subscriber->is_active) {
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
