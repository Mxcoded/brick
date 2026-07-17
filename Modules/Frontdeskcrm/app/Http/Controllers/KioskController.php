<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Frontdeskcrm\Models\Registration;

class KioskController extends Controller
{
    public function signForm()
    {
        return view('frontdeskcrm::kiosk.sign');
    }

    public function lookupBooking(Request $request)
    {
        $request->validate([
            'reservation_code' => 'required|string|max:20',
        ]);

        $registration = Registration::where('reservation_code', $request->reservation_code)->first();

        if (! $registration) {
            return response()->json(['found' => false, 'message' => 'No reservation found with that code.']);
        }

        if ($registration->guest_signature) {
            return response()->json([
                'found' => true,
                'already_signed' => true,
                'message' => 'You have already signed for this reservation.',
                'guest' => [
                    'name' => $registration->full_name,
                    'code' => $registration->reservation_code,
                    'room' => $registration->room_allocation,
                    'check_in' => $registration->check_in?->format('d M Y'),
                    'check_out' => $registration->check_out?->format('d M Y'),
                ],
            ]);
        }

        if ($registration->stay_status === 'checked_in') {
            return response()->json([
                'found' => true,
                'already_signed' => true,
                'message' => 'This reservation has already been checked in. Signature is no longer required.',
                'guest' => [
                    'name' => $registration->full_name,
                    'code' => $registration->reservation_code,
                    'room' => $registration->room_allocation,
                    'check_in' => $registration->check_in?->format('d M Y'),
                    'check_out' => $registration->check_out?->format('d M Y'),
                ],
            ]);
        }

        return response()->json([
            'found' => true,
            'already_signed' => false,
            'guest' => [
                'name' => $registration->full_name,
                'code' => $registration->reservation_code,
                'room' => $registration->room_allocation,
                'check_in' => $registration->check_in?->format('d M Y'),
                'check_out' => $registration->check_out?->format('d M Y'),
            ],
        ]);
    }

    public function submitSignature(Request $request)
    {
        $validated = $request->validate([
            'reservation_code' => 'required|string|max:20',
            'guest_signature' => [
                'required',
                'string',
                'regex:/^data:image\/(png|jpeg|jpg);base64,[A-Za-z0-9+\/=]+$/i',
            ],
        ]);

        $registration = Registration::where('reservation_code', $request->reservation_code)->first();

        if (! $registration) {
            return response()->json(['success' => false, 'message' => 'No reservation found with that code.']);
        }

        if ($registration->guest_signature) {
            return response()->json(['success' => false, 'message' => 'You have already signed for this reservation.']);
        }

        if ($registration->stay_status === 'checked_in') {
            return response()->json(['success' => false, 'message' => 'This reservation has already been checked in.']);
        }

        $sigImage = $request->guest_signature;
        if (str_contains($sigImage, ',')) {
            $sigImage = explode(',', $sigImage)[1];
        }
        $sigImage = base64_decode($sigImage);
        $imageName = 'signatures/'.uniqid().'.png';
        Storage::disk('public')->put($imageName, $sigImage);

        $registration->update([
            'guest_signature' => $imageName,
            'agreed_to_policies' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Signature captured successfully!',
            'guest' => [
                'name' => $registration->full_name,
                'code' => $registration->reservation_code,
                'room' => $registration->room_allocation,
                'check_in' => $registration->check_in?->format('d M Y'),
                'check_out' => $registration->check_out?->format('d M Y'),
            ],
        ]);
    }
}
