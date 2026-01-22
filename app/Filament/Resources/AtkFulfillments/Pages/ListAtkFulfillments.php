<?php

namespace App\Filament\Resources\AtkFulfillments\Pages;

use App\Filament\Resources\AtkFulfillments\AtkFulfillmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAtkFulfillments extends ListRecords
{
    protected static string $resource = AtkFulfillmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
