<?php

namespace App\Events;

use App\Services\PropertyService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class BaseDomainEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ?int $propertyId;

    public ?int $userId;

    public string $timestamp;

    public function __construct(?int $propertyId = null, ?int $userId = null)
    {
        $this->propertyId = $propertyId ?? app(PropertyService::class)->id();
        $this->userId = $userId ?? auth()->id();
        $this->timestamp = now()->toIso8601String();
    }
}
