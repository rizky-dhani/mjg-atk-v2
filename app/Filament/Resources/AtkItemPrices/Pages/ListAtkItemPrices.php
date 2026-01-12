<?php

namespace App\Filament\Resources\AtkItemPrices\Pages;

use App\Filament\Resources\AtkItemPrices\AtkItemPriceResource;
use Filament\Resources\Pages\ListRecords;

class ListAtkItemPrices extends ListRecords
{
    protected static string $resource = AtkItemPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->successNotificationTitle('ATK Item Price created'),
        ];
    }
}
