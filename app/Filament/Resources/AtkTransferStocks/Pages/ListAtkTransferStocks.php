<?php

namespace App\Filament\Resources\AtkTransferStocks\Pages;

use Filament\Actions;
use Filament\Support\Enums\Width;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\AtkTransferStocks\AtkTransferStockResource;

class ListAtkTransferStocks extends ListRecords
{
    protected static string $resource = AtkTransferStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->successNotificationTitle('ATK Stock Transfer created')
                ->modalWidth(Width::SevenExtraLarge),
        ];
    }
}
