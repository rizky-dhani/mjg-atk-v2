<?php

namespace App\Filament\Resources\AtkDivisionStocks\Pages;

use App\Filament\Resources\AtkDivisionStocks\AtkDivisionStockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAtkDivisionStocks extends ListRecords
{
    protected static string $resource = AtkDivisionStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
