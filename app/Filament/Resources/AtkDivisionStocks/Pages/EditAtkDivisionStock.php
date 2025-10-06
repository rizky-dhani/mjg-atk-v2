<?php

namespace App\Filament\Resources\AtkDivisionStocks\Pages;

use App\Filament\Resources\AtkDivisionStocks\AtkDivisionStockResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAtkDivisionStock extends EditRecord
{
    protected static string $resource = AtkDivisionStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
