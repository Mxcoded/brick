<?php

namespace App\Values;

use Illuminate\Support\Carbon;

class BookingEngineRequest
{
    public ?int $propertyId;
    public string $guestName;
    public string $guestEmail;
    public string $guestPhone;
    public ?string $guestGender;
    public ?string $guestAddress;
    public ?string $guestNationality;
    public ?string $guestDob;
    public ?string $guestIdType;
    public ?string $guestIdNumber;
    public ?int $userId;
    public ?int $guestProfileId;
    public string $paymentMethod;
    public ?string $specialRequests;
    public array $rooms;
    public ?string $bookingGroupId;

    public function __construct(array $data)
    {
        $this->propertyId = $data['property_id'] ?? null;
        $this->guestName = $data['guest_name'];
        $this->guestEmail = $data['guest_email'];
        $this->guestPhone = $data['guest_phone'];
        $this->guestGender = $data['guest_gender'] ?? null;
        $this->guestAddress = $data['guest_address'] ?? null;
        $this->guestNationality = $data['guest_nationality'] ?? null;
        $this->guestDob = $data['guest_dob'] ?? null;
        $this->guestIdType = $data['guest_id_type'] ?? null;
        $this->guestIdNumber = $data['guest_id_number'] ?? null;
        $this->userId = $data['user_id'] ?? null;
        $this->guestProfileId = $data['guest_profile_id'] ?? null;
        $this->paymentMethod = $data['payment_method'] ?? 'pay_on_arrival';
        $this->specialRequests = $data['special_requests'] ?? null;
        $this->rooms = $data['rooms'];
        $this->bookingGroupId = $data['booking_group_id'] ?? null;
    }

    public function isMultiRoom(): bool
    {
        return count($this->rooms) > 1 || array_sum(array_column($this->rooms, 'quantity')) > 1;
    }
}
