<?php

namespace Modules\Admin\Filament\Resources\PaymentGateways\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Admin\Filament\Resources\PaymentGateways\PaymentGatewayResource;
use Modules\Website\Models\PaymentGateway;

class EditPaymentGateway extends EditRecord
{
    protected static string $resource = PaymentGatewayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Start credential fields blank so existing secrets are never re-exposed
        // in the edit form. Blank fields are preserved on save (see below).
        $code = $this->record->code;
        $data['credentials'] = array_fill_keys(array_keys(PaymentGatewayResource::$credentialFields[$code] ?? []), '');

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var PaymentGateway $record */
        $record = $this->record;
        $code = $record->code;

        // Keep existing credential values and only overwrite the fields the
        // admin actually filled in on this edit. Blank fields are preserved.
        $data['credentials'] = array_merge(
            $record->credentials ?? [],
            PaymentGatewayResource::filterCredentials($code, $data['credentials'] ?? []),
        );

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var PaymentGateway $record */
        $record = $this->record;

        PaymentGatewayResource::syncDefault($record);
    }
}
