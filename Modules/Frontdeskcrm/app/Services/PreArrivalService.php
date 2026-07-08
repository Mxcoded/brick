<?php

namespace Modules\Frontdeskcrm\Services;

use Illuminate\Support\Str;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\GuestDocument;
use Modules\Frontdeskcrm\Models\Registration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PreArrivalService
{
    public function lookupByToken(string $token): ?Registration
    {
        return Registration::where('pre_arrival_token', $token)
            ->whereIn('stay_status', ['reserved'])
            ->first();
    }

    public function lookupByCode(string $code, string $email): ?Registration
    {
        return Registration::where('reservation_code', $code)
            ->whereIn('stay_status', ['reserved'])
            ->where(function ($query) use ($email) {
                $query->where('email', $email)
                      ->orWhere('contact_number', $email);
            })
            ->first();
    }

    public function generateToken(Registration $registration): string
    {
        $token = Str::random(40);
        $registration->update(['pre_arrival_token' => $token]);
        return $token;
    }

    public function updateGuestDetails(Registration $registration, array $data): void
    {
        $guest = $registration->guest;
        $guestFields = array_intersect_key($data, array_flip([
            'title', 'full_name', 'nationality', 'contact_number', 'email',
            'occupation', 'company_name', 'home_address', 'city', 'state',
            'emergency_name', 'emergency_contact', 'emergency_relationship',
        ]));

        if (!empty($guestFields)) {
            $guest->update($guestFields);
        }

        $registration->update(array_intersect_key($data, array_flip([
            'special_requests', 'estimated_arrival_at',
        ])));
    }

    public function uploadDocument(Registration $registration, UploadedFile $file, string $type): GuestDocument
    {
        $path = $file->store('guest-documents/' . $registration->id, 'public');

        return GuestDocument::create([
            'registration_id' => $registration->id,
            'guest_id' => $registration->guest_id,
            'type' => $type,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'pending',
        ]);
    }

    public function deleteDocument(GuestDocument $document): bool
    {
        Storage::disk('public')->delete($document->file_path);
        return $document->delete();
    }

    public function submitSignature(Registration $registration, string $signatureData): string
    {
        $decoded = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureData));
        $filename = 'signatures/' . uniqid('pre_arrival_', true) . '.png';
        Storage::disk('public')->put($filename, $decoded);

        $registration->update(['guest_signature' => $filename]);

        return $filename;
    }

    public function complete(Registration $registration): void
    {
        $registration->update([
            'stay_status' => 'draft_by_guest',
            'pre_arrival_completed_at' => now(),
        ]);

        $messaging = app(GuestMessagingService::class);
        $messaging->sendFromTemplate($registration, 'pre_arrival_confirmation', 'email');
    }

    public function getSteps(Registration $registration): array
    {
        $detailsDone = $registration->guest->full_name && $registration->no_of_guests;
        $documentsDone = $registration->documents()->count() > 0;
        $signatureDone = !empty($registration->guest_signature);

        return [
            'details'    => ['completed' => $detailsDone, 'order' => 1, 'label' => 'Personal Details'],
            'documents'  => ['completed' => $documentsDone, 'order' => 2, 'label' => 'Upload ID'],
            'signature'  => ['completed' => $signatureDone, 'order' => 3, 'label' => 'Sign Registration Card'],
        ];
    }
}
