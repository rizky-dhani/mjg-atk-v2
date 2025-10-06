<?php

namespace App\Filament\Resources\AtkStockRequests\Pages;

use App\Filament\Resources\AtkStockRequests\AtkStockRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAtkStockRequests extends ListRecords
{
    protected static string $resource = AtkStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
