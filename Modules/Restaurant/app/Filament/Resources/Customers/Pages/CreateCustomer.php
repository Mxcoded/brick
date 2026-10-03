<?php

namespace Modules\Restaurant\Filament\Resources\Customers\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Restaurant\Filament\Resources\Customers\CustomerResource;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Loyalty counters start here rather than being edited later; the POS and
        // OrderPaymentService maintain them from then on.
        return array_merge($data, [
            'visit_count' => 0,
            'total_spent' => 0,
            'loyalty_points' => 0,
        ]);
    }
}
