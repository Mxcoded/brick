<?php

namespace Modules\Contracts\Enums;

enum AgreementType: string
{
    case CORPORATE_ACCOMMODATION = 'corporate_accommodation';
    case EVENT_BANQUET = 'event_banquet';
    case LONG_STAY = 'long_stay';
    case ROOM_BLOCK = 'room_block';
    case VENDOR = 'vendor';
    case TRAVEL_AGENT = 'travel_agent';
    case OTA = 'ota';
    case CONFERENCE = 'conference';
    case CATERING = 'catering';
    case MAINTENANCE = 'maintenance';
    case HR = 'hr';
    case LEASE = 'lease';
    case PARTNERSHIP = 'partnership';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::CORPORATE_ACCOMMODATION => 'Corporate Accommodation',
            self::EVENT_BANQUET => 'Event / Banquet',
            self::LONG_STAY => 'Long Stay',
            self::ROOM_BLOCK => 'Room Block',
            self::VENDOR => 'Vendor / Supplier',
            self::TRAVEL_AGENT => 'Travel Agent',
            self::OTA => 'OTA / Partner',
            self::CONFERENCE => 'Conference',
            self::CATERING => 'Restaurant / Catering',
            self::MAINTENANCE => 'Maintenance Contract',
            self::HR => 'Staff / HR Agreement',
            self::LEASE => 'Lease',
            self::PARTNERSHIP => 'Partnership',
            self::CUSTOM => 'Custom Agreement',
        };
    }
}
