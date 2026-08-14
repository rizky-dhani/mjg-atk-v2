<?php

namespace App\Filament\Resources\AtkRequestFromFloatingStocks\Pages;

use App\Filament\Resources\AtkRequestFromFloatingStocks\AtkRequestFromFloatingStockResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAtkRequestFromFloatingStock extends EditRecord
{
    protected static string $resource = AtkRequestFromFloatingStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
