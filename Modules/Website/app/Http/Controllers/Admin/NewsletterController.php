<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Website\Models\NewsletterSubscriber;

class NewsletterController extends Controller
{
    /**
     * Display all newsletter subscribers.
     */
    public function index(Request $request)
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

        return view('website::admin.newsletter.index', compact('subscribers', 'stats'));
    }

    /**
     * Export subscribers to CSV
     */
    public function export()
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
    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();
        return redirect()->route('website.admin.newsletter.index')
            ->with('success', 'Subscriber removed successfully.');
    }

    /**
     * Toggle subscriber status
     */
    public function toggleStatus(NewsletterSubscriber $subscriber)
    {
        $subscriber->update([
            'is_active' => !$subscriber->is_active,
            'unsubscribed_at' => $subscriber->is_active ? now() : null,
        ]);

        $status = $subscriber->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Subscriber {$status} successfully.");
    }
}
