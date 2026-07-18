<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\RoomUnit;
use App\Values\BookingEngineRequest;
use App\Values\BookingEngineResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Models\Booking;

class BookingEngine
{
    public function __construct(
        protected RoomAvailabilityService $availabilityService,
        protected PropertyService $propertyService,
    ) {}

    public function search(array $params): array
    {
        $propertyId = $params['property_id'] ?? null;
        $checkIn = Carbon::parse($params['check_in']);
        $checkOut = Carbon::parse($params['check_out']);
        $adults = $params['adults'] ?? 1;
        $children = $params['children'] ?? 0;

        if ($propertyId) {
            $property = Property::find($propertyId);
            if (! $property) {
                return ['success' => false, 'message' => 'Property not found.', 'properties' => []];
            }
            $properties = collect([$property]);
        } else {
            $properties = Property::active()->get();
            if ($properties->isEmpty()) {
                return ['success' => false, 'message' => 'No active properties found.', 'properties' => []];
            }
        }

        $originalPropertyId = $this->propertyService->id();
        $results = [];

        foreach ($properties as $property) {
            $this->propertyService->setCurrent($property);

            $roomTypes = RoomType::where('is_active', true)
                ->where(function ($q) use ($adults) {
                    $q->where('capacity', '>=', $adults);
                })
                ->ordered()
                ->get();

            $propertyResults = [];

            foreach ($roomTypes as $roomType) {
                $availability = $this->availabilityService->checkRoomTypeAvailability(
                    $roomType->id, $checkIn, $checkOut
                );

                if ($availability['available']) {
                    $nights = $checkIn->diffInDays($checkOut) ?: 1;
                    $propertyResults[] = [
                        'room_type_id' => $roomType->id,
                        'name' => $roomType->name,
                        'slug' => $roomType->slug,
                        'price_per_night' => (float) $roomType->price,
                        'total' => (float) $roomType->price * $nights,
                        'capacity' => $roomType->capacity,
                        'available_count' => $availability['available_count'],
                        'total_units' => $availability['total_units'],
                        'image_url' => $roomType->image_url,
                        'amenities' => $roomType->amenities ? $roomType->amenities->pluck('name') : collect(),
                    ];
                }
            }

            $results[$property->id] = [
                'property' => [
                    'id' => $property->id,
                    'name' => $property->name,
                    'slug' => $property->slug,
                    'city' => $property->city,
                    'currency' => $property->currency,
                    'currency_symbol' => $property->getCurrencySymbol(),
                ],
                'room_types' => $propertyResults,
                'total_available' => count($propertyResults),
            ];
        }

        if ($originalPropertyId) {
            $original = Property::find($originalPropertyId);
            if ($original) {
                $this->propertyService->setCurrent($original);
            }
        } else {
            $this->propertyService->clear();
        }

        return [
            'success' => true,
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
            'nights' => $checkIn->diffInDays($checkOut) ?: 1,
            'adults' => $adults,
            'children' => $children,
            'properties' => $results,
        ];
    }

    public function createBooking(BookingEngineRequest $request): BookingEngineResult
    {
        try {
            return DB::transaction(function () use ($request) {
                $this->validateRooms($request);

                $guest = $this->findOrCreateGuest($request);

                $bookings = collect();
                $bookingGroupId = $request->isMultiRoom() ? $this->generateGroupId() : null;
                $totalAmount = 0;

                foreach ($request->rooms as $roomReq) {
                    $quantity = $roomReq['quantity'] ?? 1;
                    for ($i = 0; $i < $quantity; $i++) {
                        $booking = $this->createSingleBooking($request, $roomReq, $guest, $bookingGroupId);
                        $bookings->push($booking);
                        $totalAmount += $booking->total_amount;
                    }
                }

                return BookingEngineResult::ok($bookings, $bookingGroupId, $totalAmount);
            });
        } catch (\Throwable $e) {
            Log::error('BookingEngine::createBooking failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return BookingEngineResult::fail($e->getMessage());
        }
    }

    public function confirmBooking(Booking $booking): bool
    {
        if ($booking->payment_status === 'paid') {
            return true;
        }

        return $booking->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);
    }

    public function cancelBooking(Booking $booking, ?string $reason = null): bool
    {
        return $booking->update([
            'status' => 'cancelled',
            'admin_notes' => $reason
                ? ($booking->admin_notes ? $booking->admin_notes."\n[Cancelled] $reason" : "[Cancelled] $reason")
                : $booking->admin_notes,
        ]);
    }

    public function createRegistration(array $data): Registration
    {
        $this->validateRegistrationAvailability($data);

        $guest = $this->findOrCreateGuestFromArray($data);

        $reservationCode = $this->generateReservationCode();

        $checkIn = Carbon::parse($data['check_in']);
        $isFuture = $checkIn->startOfDay()->gt(now()->startOfDay());

        $roomAllocation = $this->resolveRoomAllocation($data);

        return Registration::create([
            'guest_id' => $guest->id,
            'reservation_code' => $reservationCode,
            'full_name' => $data['full_name'] ?? $guest->full_name,
            'contact_number' => $data['contact_number'] ?? $guest->contact_number,
            'email' => $data['email'] ?? $guest->email,
            'gender' => $data['gender'] ?? null,

            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'no_of_guests' => $data['no_of_guests'] ?? 1,

            'room_id' => $data['room_id'] ?? null,
            'room_unit_id' => $data['room_unit_id'] ?? null,
            'room_type_id' => $data['room_type_id'] ?? null,
            'room_allocation' => $roomAllocation,
            'room_rate' => $data['room_rate'] ?? 0,

            'billing_type' => $data['billing_type'] ?? 'consolidate',
            'payment_method' => $data['payment_method'] ?? null,
            'bed_breakfast' => $data['bed_breakfast'] ?? false,

            'front_desk_agent' => $data['front_desk_agent'] ?? null,

            'discount_type' => $data['discount_type'] ?? null,
            'discount_value' => $data['discount_value'] ?? null,
            'discount_percent' => $data['discount_percent'] ?? null,
            'discount_reason' => $data['discount_reason'] ?? null,
            'deposit_required' => $data['deposit_required'] ?? false,
            'deposit_amount' => $data['deposit_amount'] ?? null,
            'deposit_deadline' => $data['deposit_deadline'] ?? null,

            'stay_status' => $isFuture ? 'reserved' : 'draft_by_guest',
            'registration_date' => now(),
        ]);
    }

    protected function validateRegistrationAvailability(array $data): void
    {
        $roomUnitId = $data['room_unit_id'] ?? null;
        $roomId = $data['room_id'] ?? null;

        if ($roomUnitId) {
            $unit = RoomUnit::find($roomUnitId);
            if ($unit && in_array($unit->status, ['maintenance', 'blocked'])) {
                throw new \RuntimeException('The selected room unit is currently unavailable.');
            }
            if ($unit && ! $this->availabilityService->isUnitAvailable($unit->id, $data['check_in'], $data['check_out'])) {
                throw new \RuntimeException('This room is not available for the selected dates.');
            }
        } elseif ($roomId) {
            $room = Room::find($roomId);
            if ($room && $room->status === 'maintenance') {
                throw new \RuntimeException('The selected room is under maintenance and cannot be assigned.');
            }
            $isOccupied = Registration::where('room_id', $roomId)
                ->where('stay_status', 'checked_in')
                ->where(function ($query) use ($data) {
                    $query->whereBetween('check_in', [$data['check_in'], $data['check_out']])
                        ->orWhereBetween('check_out', [$data['check_in'], $data['check_out']])
                        ->orWhere(function ($q) use ($data) {
                            $q->where('check_in', '<=', $data['check_in'])
                                ->where('check_out', '>=', $data['check_out']);
                        });
                })->exists();
            if ($isOccupied) {
                throw new \RuntimeException('The selected room is occupied for these dates.');
            }
        }
    }

    protected function findOrCreateGuestFromArray(array $data): Guest
    {
        if (! empty($data['guest_id'])) {
            $guest = Guest::find($data['guest_id']);
            if ($guest) {
                return $guest;
            }
        }

        $guest = Guest::where('contact_number', $data['contact_number'] ?? '')->first();

        if ($guest) {
            $guest->update(array_filter([
                'full_name' => $data['full_name'] ?? $guest->full_name,
                'email' => $data['email'] ?? $guest->email,
                'gender' => $data['gender'] ?? $guest->gender,
                'home_address' => $data['home_address'] ?? $guest->home_address,
                'nationality' => $data['nationality'] ?? $guest->nationality,
                'birthday' => $data['birthday'] ?? $guest->birthday,
                'title' => $data['title'] ?? $guest->title,
                'identification_type' => $data['identification_type'] ?? $guest->identification_type,
                'identification_number' => $data['identification_number'] ?? $guest->identification_number,
                'occupation' => $data['occupation'] ?? $guest->occupation,
                'company_name' => $data['company_name'] ?? $guest->company_name,
                'city' => $data['city'] ?? $guest->city,
                'state' => $data['state'] ?? $guest->state,
                'emergency_name' => $data['emergency_name'] ?? $guest->emergency_name,
                'emergency_relationship' => $data['emergency_relationship'] ?? $guest->emergency_relationship,
                'emergency_contact' => $data['emergency_contact'] ?? $guest->emergency_contact,
            ]));

            return $guest;
        }

        return Guest::create(array_filter([
            'title' => $data['title'] ?? null,
            'full_name' => $data['full_name'] ?? null,
            'email' => $data['email'] ?? null,
            'contact_number' => $data['contact_number'] ?? null,
            'gender' => $data['gender'] ?? null,
            'home_address' => $data['home_address'] ?? null,
            'identification_number' => $data['identification_number'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'identification_type' => $data['identification_type'] ?? null,
            'birthday' => $data['birthday'] ?? null,
            'occupation' => $data['occupation'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'emergency_name' => $data['emergency_name'] ?? null,
            'emergency_relationship' => $data['emergency_relationship'] ?? null,
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'zip_code' => $data['zip_code'] ?? null,
            'opt_in_data_save' => $data['opt_in_data_save'] ?? true,
        ]));
    }

    protected function resolveRoomAllocation(array $data): ?string
    {
        if (! empty($data['room_unit_id'])) {
            $unit = RoomUnit::find($data['room_unit_id']);
            if ($unit) {
                return 'Room '.$unit->room_number;
            }
        }
        if (! empty($data['room_id'])) {
            $room = Room::find($data['room_id']);
            if ($room) {
                return $room->name;
            }
        }

        return null;
    }

    protected function generateReservationCode(): string
    {
        do {
            $code = 'FD'.date('y').strtoupper(Str::random(4));
        } while (Registration::where('reservation_code', $code)->exists());

        return $code;
    }

    protected function validateRooms(BookingEngineRequest $request): void
    {
        foreach ($request->rooms as $roomReq) {
            $roomType = RoomType::find($roomReq['room_type_id']);
            if (! $roomType) {
                throw new \RuntimeException("Room type #{$roomReq['room_type_id']} not found.");
            }

            $checkIn = Carbon::parse($roomReq['check_in']);
            $checkOut = Carbon::parse($roomReq['check_out']);

            $result = $this->availabilityService->checkRoomTypeAvailability(
                $roomReq['room_type_id'],
                $checkIn,
                $checkOut,
                $roomReq['quantity'] ?? 1
            );

            if (! $result['available']) {
                throw new \RuntimeException(
                    $roomType->name.': '.$result['message']
                );
            }
        }
    }

    protected function findOrCreateGuest(BookingEngineRequest $request): Guest
    {
        if ($request->guestProfileId) {
            $guest = Guest::find($request->guestProfileId);
            if ($guest) {
                return $guest;
            }
        }

        $guest = Guest::where('email', $request->guestEmail)
            ->orWhere('contact_number', $request->guestPhone)
            ->first();

        if ($guest) {
            $guest->update(array_filter([
                'full_name' => $request->guestName,
                'gender' => $request->guestGender,
                'home_address' => $request->guestAddress,
                'nationality' => $request->guestNationality,
                'birthday' => $request->guestDob,
                'identification_type' => $request->guestIdType,
                'identification_number' => $request->guestIdNumber,
                'user_id' => $request->userId ?? $guest->user_id,
            ]));

            return $guest;
        }

        return Guest::create(array_filter([
            'user_id' => $request->userId,
            'full_name' => $request->guestName,
            'email' => $request->guestEmail,
            'contact_number' => $request->guestPhone,
            'gender' => $request->guestGender,
            'home_address' => $request->guestAddress,
            'nationality' => $request->guestNationality,
            'birthday' => $request->guestDob,
            'identification_type' => $request->guestIdType,
            'identification_number' => $request->guestIdNumber,
        ]));
    }

    protected function createSingleBooking(
        BookingEngineRequest $request,
        array $roomReq,
        Guest $guest,
        ?string $bookingGroupId,
    ): Booking {
        $checkIn = Carbon::parse($roomReq['check_in']);
        $checkOut = Carbon::parse($roomReq['check_out']);
        $nights = $checkIn->diffInDays($checkOut) ?: 1;

        $roomType = RoomType::findOrFail($roomReq['room_type_id']);

        $pricePerNight = $roomReq['price_per_night'] ?? (float) $roomType->price;
        $totalAmount = $pricePerNight * $nights;

        $reference = $this->generateReference();
        $selectedUnitId = $roomReq['room_unit_id'] ?? null;

        $bookingData = [
            'booking_reference' => $reference,
            'booking_group_id' => $bookingGroupId,
            'user_id' => $request->userId,
            'guest_profile_id' => $guest->id,
            'room_type_id' => $roomReq['room_type_id'],
            'room_unit_id' => $selectedUnitId,
            'guest_name' => $request->guestName,
            'guest_email' => $request->guestEmail,
            'guest_phone' => $request->guestPhone,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'adults' => $roomReq['adults'] ?? 1,
            'children' => $roomReq['children'] ?? 0,
            'total_amount' => $totalAmount,
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => $request->paymentMethod,
            'special_requests' => $request->specialRequests,
        ];

        if ($request->propertyId) {
            $bookingData['property_id'] = $request->propertyId;
        }

        return Booking::create($bookingData);
    }

    protected function generateReference(): string
    {
        do {
            $ref = 'BK'.date('y').strtoupper(Str::random(4));
        } while (Booking::where('booking_reference', $ref)->exists());

        return $ref;
    }

    protected function generateGroupId(): string
    {
        return 'GRP'.date('y').strtoupper(Str::random(6));
    }
}
