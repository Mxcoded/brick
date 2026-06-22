<?php

namespace Modules\Staff\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Staff\Models\SharedDocument;

class SharedDocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = SharedDocument::with('uploader')
            ->search($request->search)
            ->latest()
            ->paginate(20);

        $totalSize = SharedDocument::sum('file_size');
        $totalCount = SharedDocument::count();

        $docPath = storage_path('app/documents');
        if (! is_dir($docPath)) {
            mkdir($docPath, 0755, true);
        }
        $diskFree = @disk_free_space($docPath) ?: 0;
        $diskTotal = @disk_total_space($docPath) ?: 0;

        return view('staff::documents.index', compact(
            'documents', 'totalSize', 'totalCount', 'diskFree', 'diskTotal'
        ));
    }

    public function create()
    {
        return view('staff::documents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|max:307200',
            'description' => 'nullable|string|max:1000',
        ]);

        $uploaded = 0;
        $errors = [];

        foreach ($request->file('files') as $file) {
            try {
                $path = $file->store('', 'documents');

                SharedDocument::create([
                    'filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'extension' => $file->getClientOriginalExtension(),
                    'description' => $request->description,
                    'uploaded_by' => auth()->id(),
                ]);

                $uploaded++;
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                if (str_contains($msg, 'disk is full')) {
                    $reason = 'storage disk is full';
                } elseif (str_contains($msg, 'file size')) {
                    $reason = 'file size exceeds server limit';
                } elseif (str_contains($msg, 'No space left')) {
                    $reason = 'no space left on server';
                } elseif (str_contains($msg, 'uploaded_by')) {
                    $reason = 'your account could not be verified';
                } elseif (str_contains($msg, 'Allowed memory')) {
                    $reason = 'file too large for server memory';
                } elseif (str_contains($msg, 'Maximum execution')) {
                    $reason = 'upload timed out, try a smaller file';
                } elseif (str_contains($msg, 'failed to open stream')) {
                    $reason = 'storage path not writable';
                } else {
                    $reason = $msg;
                }
                $errors[] = $file->getClientOriginalName().': '.$reason;
                logger()->error('Document upload failed: '.$msg, [
                    'file' => $file->getClientOriginalName(),
                    'reason' => $reason,
                ]);
            }
        }

        $message = "{$uploaded} file(s) uploaded successfully.";
        $type = 'success';

        if (! empty($errors)) {
            $message .= ' '.implode(', ', $errors);
            $type = 'warning';
        }

        if ($request->ajax() || $request->wantsJson()) {
            session()->flash($type, $message);
            return response()->json([
                'success' => empty($errors),
                'message' => $message,
                'uploaded' => $uploaded,
                'errors' => $errors,
            ]);
        }

        return redirect()->route('staff.documents.index')->with($type, $message);
    }

    public function download(SharedDocument $document)
    {
        if (! Storage::disk('documents')->exists($document->file_path)) {
            return back()->with('error', 'File not found on disk.');
        }

        $document->increment('downloads_count');

        return Storage::disk('documents')->download($document->file_path, $document->filename);
    }

    public function publicDownload(string $token)
    {
        $document = SharedDocument::where('share_token', $token)->firstOrFail();

        if (! Storage::disk('documents')->exists($document->file_path)) {
            abort(404, 'File not found on disk.');
        }

        $document->increment('downloads_count');

        return Storage::disk('documents')->download($document->file_path, $document->filename);
    }

    public function regenerateShareLink(SharedDocument $document)
    {
        $document->update(['share_token' => (string) \Illuminate\Support\Str::uuid()]);

        return back()->with('success', 'Share link regenerated successfully.');
    }

    public function destroy(SharedDocument $document)
    {
        $document->delete();

        return redirect()->route('staff.documents.index')->with('success', 'File deleted successfully.');
    }
}
