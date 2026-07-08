<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Models\Channel;
use Modules\Frontdeskcrm\Services\ChannelSyncService;

class ChannelWebhookController extends Controller
{
    public function __invoke(Request $request, Channel $channel): JsonResponse
    {
        if (! $channel->is_active) {
            Log::warning("Webhook received for inactive channel {$channel->id}");

            return response()->json(['error' => 'Channel is inactive'], 422);
        }

        $payload = $request->all();
        Log::info("Webhook received for channel {$channel->name} ({$channel->provider})");

        $service = app(ChannelSyncService::class);
        $result = $service->handleWebhook($channel, $payload);

        $channel->update([
            'last_sync_at' => now(),
            'last_sync_status' => 'success',
        ]);

        return response()->json([
            'status' => 'ok',
            'result' => $result,
        ]);
    }
}
