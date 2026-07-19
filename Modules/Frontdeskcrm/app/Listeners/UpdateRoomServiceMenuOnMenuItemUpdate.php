<?php

namespace Modules\Frontdeskcrm\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Restaurant\Events\MenuItemUpdated;

class UpdateRoomServiceMenuOnMenuItemUpdate implements ShouldQueue
{
    public function handle(MenuItemUpdated $event): void
    {
        Log::info('Room service menu updated due to menu item change', [
            'menu_item_id' => $event->menuItem->id,
            'changed_attributes' => $event->changedAttributes,
            'property_id' => $event->propertyId,
        ]);
    }
}
