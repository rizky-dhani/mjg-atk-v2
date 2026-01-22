<?php

namespace App\Filament\Resources\AtkFulfillments\Pages;

use App\Filament\Resources\AtkFulfillments\AtkFulfillmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAtkFulfillment extends ViewRecord
{
    protected static string $resource = AtkFulfillmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
