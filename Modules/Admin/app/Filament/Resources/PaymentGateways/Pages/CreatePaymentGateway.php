<?php

namespace Modules\Admin\Filament\Resources\PaymentGateways\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Admin\Filament\Resources\PaymentGateways\PaymentGatewayResource;
use Modules\Website\Models\PaymentGateway;

class CreatePaymentGateway extends CreateRecord
{
    protected static string $resource = PaymentGatewayResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $code = $data['code'] ?? '';
        $data['driver'] = $code;
        $data['credentials'] = PaymentGatewayResource::filterCredentials($code, $data['credentials'] ?? []);

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var PaymentGateway $record */
        $record = $this->record;

        PaymentGatewayResource::syncDefault($record);
    }
}
