<?php

namespace Modules\Frontdeskcrm\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Frontdeskcrm\Models\Channel;
use Modules\Frontdeskcrm\Services\ChannelSyncService;

class SyncChannelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public function __construct(
        public Channel $channel
    ) {}

    public function handle(ChannelSyncService $service): void
    {
        $service->sync($this->channel);
    }
}
