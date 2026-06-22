<?php

namespace Modules\Staff\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SharedDocument extends Model
{
    protected $fillable = [
        'filename',
        'file_path',
        'file_size',
        'mime_type',
        'extension',
        'description',
        'uploaded_by',
        'downloads_count',
        'share_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $document) {
            if (! $document->share_token) {
                $document->share_token = (string) Str::uuid();
            }
        });

        static::deleted(function (self $document) {
            Storage::disk('documents')->delete($document->file_path);
        });
    }

    public function shareUrl(): string
    {
        return route('shared.documents.download', $this->share_token);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function formattedSize(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0).' KB';
        }
        return $bytes.' B';
    }

    public function icon(): string
    {
        return match ($this->extension) {
            'pdf' => 'fa-file-pdf',
            'doc', 'docx' => 'fa-file-word',
            'xls', 'xlsx', 'csv' => 'fa-file-excel',
            'ppt', 'pptx' => 'fa-file-powerpoint',
            'zip', 'rar', '7z' => 'fa-file-archive',
            'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp' => 'fa-file-image',
            'mp4', 'avi', 'mkv' => 'fa-file-video',
            'mp3', 'wav', 'flac' => 'fa-file-audio',
            'txt', 'log' => 'fa-file-alt',
            default => 'fa-file',
        };
    }

    public function scopeSearch($query, ?string $term)
    {
        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('filename', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('extension', 'like', "%{$term}%");
            });
        }
    }

}
