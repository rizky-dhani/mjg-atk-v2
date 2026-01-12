<?php

namespace App\Filament\Resources\AtkTransferStocks\Pages;

use App\Filament\Resources\AtkTransferStocks\AtkTransferStockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

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
