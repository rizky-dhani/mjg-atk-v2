<?php

namespace App\Filament\Resources\AtkFloatingStocks\Pages;

use App\Filament\Resources\AtkFloatingStocks\AtkFloatingStockResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAtkFloatingStock extends EditRecord
{
    protected static string $resource = AtkFloatingStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
