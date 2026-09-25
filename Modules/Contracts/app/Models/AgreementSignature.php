<?php

namespace Modules\Contracts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AgreementSignature extends Model
{
    protected $fillable = [
        'agreement_id',
        'party_role',
        'party_name',
        'position',
        'signature_type',
        'signature_data',
        'signed_at',
        'ip_address',
        'user_agent',
        'verification',
        'hash',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }

    /**
     * Resolve a renderable image source for drawn/uploaded signatures.
     * Accepts a data URI or a path stored on the public disk.
     */
    public function getSignatureImageSrcAttribute(): ?string
    {
        $data = trim((string) $this->signature_data);

        if ($data === '') {
            return null;
        }

        if (str_starts_with($data, 'data:image/')) {
            return $data;
        }

        if (str_contains($data, '://') || str_starts_with($data, '/')) {
            return null;
        }

        $path = ltrim($data, '/');

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        $binary = Storage::disk('public')->get($path);

        return $binary === null
            ? null
            : 'data:image/png;base64,'.base64_encode($binary);
    }
}
