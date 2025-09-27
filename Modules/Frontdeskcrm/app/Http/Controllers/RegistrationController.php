<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Http\Requests\StoreRegistrationRequest;
use Modules\Frontdeskcrm\Models\Registration;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\GuestPreference;
use Modules\Frontdeskcrm\Models\BookingSource;
use Modules\Frontdeskcrm\Models\GuestType;

class RegistrationController extends Controller
{
    public function index()
    {
        $registrations = Registration::with('guest', 'bookingSource', 'guestType')->latest()->paginate(10);
        return view('frontdeskcrm::registrations.index', compact('registrations'));
    }

    public function create()
    {
        $bookingSources = BookingSource::active()->get();
        $guestTypes = GuestType::active()->get();
        return view('frontdeskcrm::registrations.create', [
            'guest' => null,
            'bookingSources' => $bookingSources,
            'guestTypes' => $guestTypes,
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $guests = Guest::where('email', 'LIKE', "%{$query}%")
            ->orWhere('contact_number', 'LIKE', "%{$query}%")
            ->orWhere('full_name', 'LIKE', "%{$query}%")
            ->with('preference')
            ->limit(5)
            ->get();

        return response()->json($guests->map(function ($guest) {
            $prefs = $guest->preference;
            return [
                'id' => $guest->id,
                'full_name' => $guest->full_name,
                'email' => $guest->email,
                'contact_number' => $guest->contact_number,
                'title' => $guest->title,
                'nationality' => $guest->nationality,
                'birthday' => $guest->birthday?->format('Y-m-d'),
                'occupation' => $guest->occupation,
                'company_name' => $guest->company_name,
                'home_address' => $guest->home_address,
                'emergency_name' => $guest->emergency_name,
                'emergency_relationship' => $guest->emergency_relationship,
                'emergency_contact' => $guest->emergency_contact,
                'preferred_room_type' => $prefs?->preferred_room_type ?? null,
                'bb_included' => $prefs?->bb_included ?? false,
            ];
        }));
    }

    public function store(StoreRegistrationRequest $request)
    {
        $data = $request->validated();
        $data['front_desk_agent'] = Auth::user()->name;
        $data['registration_date'] = now()->toDateString();

        // Apply guest type discount if applicable
        if (isset($data['guest_type_id'])) {
            $guestType = GuestType::find($data['guest_type_id']);
            if ($guestType && $guestType->discount_rate > 0) {
                $data['room_rate'] = $data['room_rate'] * (1 - ($guestType->discount_rate / 100));
            }
        }

        // Lookup or create guest
        $guestAttributes = [
            'email' => $data['email'] ?? null,
            'contact_number' => $data['contact_number'],
        ];
        $guest = Guest::firstOrCreate($guestAttributes, [
            'title' => $data['title'] ?? null,
            'full_name' => $data['full_name'],
            'nationality' => $data['nationality'] ?? null,
            'birthday' => $data['birthday'] ?? null,
            'occupation' => $data['occupation'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'home_address' => $data['home_address'] ?? null,
            'emergency_name' => $data['emergency_name'] ?? null,
            'emergency_relationship' => $data['emergency_relationship'] ?? null,
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'opt_in_data_save' => $request->boolean('opt_in_data_save', true),
        ]);

        if ($guest->wasRecentlyCreated) {
            $guest->visit_count = 1;
            $guest->save();
        } else {
            $guest->increment('visit_count');
            $guest->update(['last_visit_at' => now()]);
        }

        // Handle preferences
        if ($guest->opt_in_data_save) {
            $prefs = $guest->preference ?? new GuestPreference(['guest_id' => $guest->id]);
            $prefs->preferences = array_merge($prefs->preferences ?? [], [
                'preferred_room_type' => $data['room_type'],
                'bb_included' => $data['bed_breakfast'] ?? false,
            ]);
            $prefs->save();
        }

        $data['guest_id'] = $guest->id;

        // Handle signature
        if (isset($data['guest_signature'])) {
            $signature = $data['guest_signature'];
            $signature = str_replace('data:image/png;base64,', '', $signature);
            $signature = str_replace(' ', '+', $signature);
            $fileData = base64_decode($signature);
            $filename = 'signatures/' . uniqid() . '.png';
            Storage::disk('public')->put($filename, $fileData);
            $data['guest_signature'] = $filename;
        }

        // Create main registration (boot will calc nights/total/stay_status)
        $registration = Registration::create($data);

        // Handle group members (create guest for each sub to normalize)
        if ($request->boolean('is_group_lead') && $request->has('group_members')) {
            foreach ($request->input('group_members', []) as $index => $member) {
                if (empty($member['full_name']) || empty($member['contact_number'])) {
                    continue;
                }
                // Create minimal guest for sub
                $subGuest = Guest::firstOrCreate(
                    ['contact_number' => $member['contact_number']],
                    [
                        'full_name' => $member['full_name'],
                        'opt_in_data_save' => false, // Subs default no prefs
                    ]
                );
                $subData = [
                    'guest_id' => $subGuest->id,
                    'full_name' => $member['full_name'],
                    'contact_number' => $member['contact_number'],
                    'room_assignment' => $member['room_assignment'] ?? null,
                    'group_master_id' => $registration->id,
                    'is_group_lead' => false,
                    'guest_type_id' => $data['guest_type_id'],
                    'booking_source_id' => $data['booking_source_id'],
                    'check_in' => $data['check_in'],
                    'check_out' => $data['check_out'],
                    'room_type' => $data['room_type'],
                    'room_rate' => $data['room_rate'],
                    'bed_breakfast' => $data['bed_breakfast'],
                    'no_of_guests' => 1,
                    'payment_method' => $data['payment_method'],
                    'agreed_to_policies' => true,
                    'front_desk_agent' => $data['front_desk_agent'],
                    'registration_date' => $data['registration_date'],
                ];
                Registration::create($subData);
            }
            $registration->loadCount('groupMembers');
        }

        return redirect()->route('frontdesk.registrations.index')
            ->with('success', 'Guest registration completed successfully!');
    }

    public function addGroupMember(Request $request, Registration $master)
    {
        if (!$master->is_group_lead) {
            abort(404);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'room_assignment' => 'nullable|string|max:50',
        ]);

        $subData = array_merge($validated, [
            'group_master_id' => $master->id,
            'is_group_lead' => false,
            'guest_type_id' => $master->guest_type_id,
            'booking_source_id' => $master->booking_source_id,
            'check_in' => $master->check_in,
            'check_out' => $master->check_out,
            'room_type' => $master->room_type,
            'room_rate' => $master->room_rate,
            'bed_breakfast' => $master->bed_breakfast,
            'no_of_guests' => 1,
            'payment_method' => $master->payment_method,
            'agreed_to_policies' => true,
            'front_desk_agent' => Auth::user()->name,
            'registration_date' => now()->toDateString(),
        ]);

        Registration::create($subData);

        return redirect()->back()->with('success', 'Group member added.');
    }

    public function show(Registration $registration)
    {
        $registration->load('guest', 'bookingSource', 'guestType', 'groupMaster', 'groupMembers');
        return view('frontdeskcrm::registrations.show', compact('registration'));
    }
}
