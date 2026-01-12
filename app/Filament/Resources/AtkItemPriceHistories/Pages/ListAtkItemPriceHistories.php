<?php

namespace App\Filament\Resources\AtkItemPriceHistories\Pages;

use App\Filament\Resources\AtkItemPriceHistories\AtkItemPriceHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListAtkItemPriceHistories extends ListRecords
{
    protected static string $resource = AtkItemPriceHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action as history is automatically generated
        ];
    }
}
