<?php

namespace App\Filament\Resources\AtkFloatingStocks\Pages;

use App\Filament\Resources\AtkFloatingStocks\AtkFloatingStockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAtkFloatingStocks extends ListRecords
{
    protected static string $resource = AtkFloatingStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->successNotificationTitle('ATK Floating Stock created'),
        ];
    }
}
