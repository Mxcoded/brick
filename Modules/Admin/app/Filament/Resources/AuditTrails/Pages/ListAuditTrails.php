<?php

namespace Modules\Admin\Filament\Resources\AuditTrails\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\AuditTrails\AuditTrailResource;

class ListAuditTrails extends ListRecords
{
    protected static string $resource = AuditTrailResource::class;
}
