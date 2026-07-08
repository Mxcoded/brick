<?php

namespace Modules\Frontdeskcrm\Database\Factories;

use App\Models\RoomUnit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Frontdeskcrm\Models\Channel;
use Modules\Frontdeskcrm\Models\ChannelRoomMapping;

class ChannelRoomMappingFactory extends Factory
{
    protected $model = ChannelRoomMapping::class;

    public function definition(): array
    {
        return [
            'channel_id' => Channel::factory(),
            'room_unit_id' => RoomUnit::factory(),
            'external_room_id' => fake()->unique()->numerify('EXT-#####'),
            'external_room_name' => fake()->word.' Room',
        ];
    }
}
