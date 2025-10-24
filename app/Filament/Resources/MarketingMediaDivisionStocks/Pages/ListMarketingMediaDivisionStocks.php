<?php

namespace App\Filament\Resources\MarketingMediaDivisionStocks\Pages;

use App\Filament\Resources\MarketingMediaDivisionStocks\MarketingMediaDivisionStockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMarketingMediaDivisionStocks extends ListRecords
{
    protected static string $resource = MarketingMediaDivisionStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}