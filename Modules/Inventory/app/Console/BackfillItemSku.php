<?php

namespace Modules\Inventory\Console;

use Illuminate\Console\Command;
use Modules\Inventory\Models\Item;

class BackfillItemSku extends Command
{
    protected $signature = 'inventory:backfill-sku';

    protected $description = 'Generate SKU for items that are missing one.';

    public function handle()
    {
        $items = Item::whereNull('sku')->get();

        if ($items->isEmpty()) {
            $this->info('All items already have a SKU.');

            return 0;
        }

        $progressBar = $this->output->createProgressBar($items->count());
        $progressBar->start();

        foreach ($items as $item) {
            $item->sku = Item::generateNextSku();
            $item->saveQuietly();
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Generated SKUs for {$items->count()} items.");

        return 0;
    }
}
