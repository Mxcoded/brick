<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Website\Models\ContactMessage;
use Illuminate\Support\Facades\Log;

class ContactMessageController extends Controller
{
    /**
     * Display a listing of messages.
     */
    public function index(Request $request)
    {
        $query = ContactMessage::latest();

        // Filter by Status (Read/Unread)
        if ($request->filled('status')) {
            $isRead = $request->status === 'read';
            $query->where('is_read', $isRead);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('subject', 'like', "%$search%");
            });
        }

        $messages = $query->paginate(15)->withQueryString();

        return view('website::admin.contact-messages.index', compact('messages'));
    }

    /**
     * Display the specified contact message.
     *
     * @param  \Modules\Website\Models\ContactMessage  $contactMessage
     * @return \Illuminate\View\View
     */
    public function show(ContactMessage $contactMessage)
    {
        if ($contactMessage->status === 'unread') {
            $contactMessage->update(['status' => 'read']);
        }
        return view('website::admin.contact-messages.show', compact('contactMessage'));
    }

    /**
     * Update the specified contact message in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Modules\Website\Models\ContactMessage  $contactMessage
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, ContactMessage $contactMessage)
    {
        $validated = $request->validate([
            'status' => 'required|in:unread,read,replied',
        ]);

        $contactMessage->update($validated);

        Log::info('Contact message updated:', $contactMessage->toArray());

        return redirect()->route('website.admin.contact-messages.index')->with('success', 'Message status updated successfully.');
    }

    /**
     * Remove the specified contact message from storage.
     *
     * @param  \Modules\Website\Models\ContactMessage  $contactMessage
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();
        return redirect()->route('website.admin.contact-messages.index')->with('success', 'Message deleted successfully.');
    }
}
