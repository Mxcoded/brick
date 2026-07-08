<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Website\Emails\ContactReply;
use Modules\Website\Models\ContactMessage;
use Modules\Website\Models\ContactMessageReply;
use Symfony\Component\Mailer\Exception\TransportException;

class ContactMessageController extends Controller
{
    /**
     * Display a listing of messages.
     */
    public function index(Request $request)
    {
        $query = ContactMessage::with('replies')->latest();

        // Filter by Archive Status
        if ($request->filled('archive')) {
            if ($request->archive === 'archived') {
                $query->archived();
            } elseif ($request->archive === 'active') {
                $query->active();
            }
        } else {
            // Default: show active (non-archived) messages
            $query->active();
        }

        // Filter by Status (Read/Unread/Replied)
        if ($request->filled('status')) {
            $status = $request->status;
            $query->where('status', $status);
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

        // Get counts for tabs
        $activeCount = ContactMessage::active()->count();
        $archivedCount = ContactMessage::archived()->count();
        $unreadCount = ContactMessage::active()->unread()->count();

        return view('website::admin.contact-messages.index', compact(
            'messages',
            'activeCount',
            'archivedCount',
            'unreadCount'
        ));
    }

    /**
     * Display the specified contact message with conversation thread.
     *
     * @return View
     */
    public function show(ContactMessage $contactMessage)
    {
        if ($contactMessage->status === 'unread') {
            $contactMessage->update(['status' => 'read']);
        }

        // Load replies with staff user info
        $contactMessage->load(['replies.user']);

        return view('website::admin.contact-messages.show', compact('contactMessage'));
    }

    /**
     * Show the reply form.
     *
     * @return View
     */
    public function reply(ContactMessage $contactMessage)
    {
        $contactMessage->load(['replies.user']);

        return view('website::admin.contact-messages.reply', compact('contactMessage'));
    }

    /**
     * Send a reply to the contact message.
     *
     * @return RedirectResponse
     */
    public function sendReply(Request $request, ContactMessage $contactMessage)
    {
        $validated = $request->validate([
            'message' => 'required|string|min:10',
        ]);

        // Create the reply record
        $reply = ContactMessageReply::create([
            'contact_message_id' => $contactMessage->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'sent_at' => now(),
        ]);

        // Update the contact message status and last_reply_at
        $contactMessage->update([
            'status' => 'replied',
            'last_reply_at' => now(),
        ]);

        // Send email to guest
        try {
            // Log mail configuration for debugging
            Log::info('Attempting to send contact reply email:', [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username') ? 'SET' : 'NOT SET',
                'encryption' => config('mail.mailers.smtp.encryption') ?? config('mail.mailers.smtp.scheme'),
                'from_address' => config('mail.from.address'),
                'to_email' => $contactMessage->email,
            ]);

            Mail::to($contactMessage->email)->send(
                new ContactReply($contactMessage, $reply, Auth::user()->name)
            );

            Log::info('Contact reply sent successfully:', [
                'contact_message_id' => $contactMessage->id,
                'reply_id' => $reply->id,
                'sent_to' => $contactMessage->email,
            ]);

            return redirect()
                ->route('website.admin.contact-messages.show', $contactMessage)
                ->with('success', 'Reply sent successfully to '.$contactMessage->email);
        } catch (\Swift_TransportException $e) {
            // SMTP connection/authentication errors
            Log::error('SMTP Transport Error - Failed to send contact reply:', [
                'error_type' => 'Swift_TransportException',
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'contact_message_id' => $contactMessage->id,
                'to_email' => $contactMessage->email,
                'mail_config' => [
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'username' => config('mail.mailers.smtp.username'),
                    'encryption' => config('mail.mailers.smtp.encryption') ?? config('mail.mailers.smtp.scheme'),
                ],
            ]);

            return redirect()
                ->route('website.admin.contact-messages.show', $contactMessage)
                ->with('warning', 'Reply saved but email failed: SMTP connection error. Check server logs for details. Error: '.Str::limit($e->getMessage(), 100));
        } catch (TransportException $e) {
            // Symfony Mailer transport errors (Laravel 9+)
            Log::error('Symfony Transport Error - Failed to send contact reply:', [
                'error_type' => 'Symfony\TransportException',
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'contact_message_id' => $contactMessage->id,
                'to_email' => $contactMessage->email,
                'debug' => $e->getDebug() ?? null,
                'mail_config' => [
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'username' => config('mail.mailers.smtp.username'),
                    'encryption' => config('mail.mailers.smtp.encryption') ?? config('mail.mailers.smtp.scheme'),
                ],
            ]);

            return redirect()
                ->route('website.admin.contact-messages.show', $contactMessage)
                ->with('warning', 'Reply saved but email failed: '.Str::limit($e->getMessage(), 150));
        } catch (\Exception $e) {
            // Generic catch-all
            Log::error('General Error - Failed to send contact reply:', [
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'contact_message_id' => $contactMessage->id,
                'to_email' => $contactMessage->email,
                'mail_config' => [
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                ],
            ]);

            return redirect()
                ->route('website.admin.contact-messages.show', $contactMessage)
                ->with('warning', 'Reply saved but email could not be sent. Error: '.Str::limit($e->getMessage(), 150));
        }
    }

    /**
     * Archive the specified contact message.
     *
     * @return RedirectResponse
     */
    public function archive(ContactMessage $contactMessage)
    {
        $contactMessage->archive(Auth::id());

        Log::info('Contact message archived:', [
            'id' => $contactMessage->id,
            'archived_by' => Auth::id(),
        ]);

        return redirect()
            ->route('website.admin.contact-messages.index')
            ->with('success', 'Message archived successfully.');
    }

    /**
     * Restore an archived contact message.
     *
     * @return RedirectResponse
     */
    public function restore(ContactMessage $contactMessage)
    {
        $contactMessage->restore();

        Log::info('Contact message restored:', [
            'id' => $contactMessage->id,
            'restored_by' => Auth::id(),
        ]);

        return redirect()
            ->route('website.admin.contact-messages.index', ['archive' => 'archived'])
            ->with('success', 'Message restored successfully.');
    }

    /**
     * Update the specified contact message in storage.
     *
     * @return RedirectResponse
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
     * @return RedirectResponse
     */
    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return redirect()->route('website.admin.contact-messages.index')->with('success', 'Message deleted successfully.');
    }
}
