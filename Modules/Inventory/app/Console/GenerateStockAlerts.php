<?php

namespace Modules\Inventory\Console;

use Illuminate\Console\Command;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\StockAlert;
use Modules\Inventory\Models\StoreItem;

class GenerateStockAlerts extends Command
{
    protected $signature = 'inventory:generate-alerts';

    protected $description = 'Generate stock alerts for low stock, expired, and expiring items';

    public function handle(): int
    {
        $this->info('Generating stock alerts...');
        $now = now()->startOfDay();
        $generated = 0;

        StockAlert::where('resolved', false)->where('created_at', '<', $now->copy()->subDay())->update(['resolved' => true, 'resolved_at' => now()]);

        foreach (Item::lowStock()->with('storeItems.store')->cursor() as $item) {
            foreach ($item->storeItems->groupBy('store_id') as $storeId => $storeItems) {
                $totalQty = $storeItems->sum('quantity');
                if ($totalQty < ($item->min_stock ?? 0)) {
                    StockAlert::create([
                        'item_id' => $item->id,
                        'store_id' => $storeId,
                        'type' => 'low_stock',
                        'severity' => 'warning',
                        'message' => "{$item->description} is low ({$totalQty} units, min {$item->min_stock}).",
                    ]);
                    $generated++;
                }
            }
        }

        foreach (StoreItem::with('item')->whereNotNull('expiry_date')->where('quantity', '>', 0)->where('expiry_date', '<', $now)->cursor() as $si) {
            StockAlert::create([
                'item_id' => $si->item_id,
                'store_id' => $si->store_id,
                'type' => 'expired',
                'severity' => 'danger',
                'message' => "{$si->item->description} expired on {$si->expiry_date->format('d M Y')}.",
            ]);
            $generated++;
        }

        foreach (StoreItem::with('item')->whereNotNull('expiry_date')->where('quantity', '>', 0)->whereBetween('expiry_date', [$now, $now->copy()->addDays(30)])->cursor() as $si) {
            StockAlert::create([
                'item_id' => $si->item_id,
                'store_id' => $si->store_id,
                'type' => 'expiring',
                'severity' => 'info',
                'message' => "{$si->item->description} expires on {$si->expiry_date->format('d M Y')}.",
            ]);
            $generated++;
        }

        $this->info("Generated {$generated} alert(s).");

        return Command::SUCCESS;
    }
}
