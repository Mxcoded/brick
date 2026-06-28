<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Modules\Frontdeskcrm\Models\Channel;
use Modules\Frontdeskcrm\Models\ChannelRoomMapping;
use Modules\Frontdeskcrm\Services\ChannelSyncService;
use Modules\Frontdeskcrm\Tests\ModuleTestCase;

class ChannelSyncTest extends ModuleTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createAuthenticatedUser();
    }

    public function test_can_create_channel()
    {
        $response = $this->post(route('frontdesk.channels.store'), [
            'name' => 'Booking.com',
            'provider' => 'booking.com',
            'api_endpoint' => 'https://api.booking.com/v1',
            'api_key' => 'test-key-123',
            'is_active' => true,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('channels', ['name' => 'Booking.com']);
    }

    public function test_sync_service_handles_no_api_endpoint()
    {
        $channel = Channel::factory()->create([
            'api_endpoint' => null,
            'api_key' => null,
            'is_active' => true,
        ]);

        $service = app(ChannelSyncService::class);
        $results = $service->sync($channel);

        $this->assertFalse($results['availability_pushed']);
        $this->assertEquals(0, $results['bookings_pulled']);
        $this->assertEmpty($results['errors']);
    }

    public function test_channel_has_room_mappings()
    {
        $channel = Channel::factory()->create();
        $mapping = ChannelRoomMapping::factory()->create(['channel_id' => $channel->id]);

        $this->assertTrue($channel->roomMappings->contains($mapping));
    }

    public function test_can_view_channels_index()
    {
        Channel::factory()->count(3)->create();

        $response = $this->get(route('frontdesk.channels.index'));

        $response->assertOk();
        $response->assertViewHas('channels');
    }
}
