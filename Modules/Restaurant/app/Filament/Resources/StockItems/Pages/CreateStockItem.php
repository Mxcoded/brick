<?php

namespace Modules\Restaurant\Filament\Resources\StockItems\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Restaurant\Filament\Resources\StockItems\StockItemResource;
use Modules\Restaurant\Models\StockItem;

class CreateStockItem extends CreateRecord
{
    protected static string $resource = StockItemResource::class;

    protected function afterCreate(): void
    {
        /** @var StockItem $record */
        $record = $this->getRecord();

        $opening = (float) $record->stock_quantity;

        if ($opening <= 0) {
            return;
        }

        // The form's quantity is an opening balance, not a purchase, but it
        // still has to appear in the ledger so that the running total on the
        // item reconciles with the sum of its movements. Writing the movement
        // at the same opening figure keeps those two in step from the first row.
        $record->movements()->create([
            'type' => 'purchase',
            'quantity' => $opening,
            'unit_cost' => $record->unit_cost,
            'reference' => 'Opening balance',
            'notes' => 'Recorded when the ingredient was added.',
            'user_id' => auth()->id(),
        ]);
    }
}
