<?php

namespace App\Filament\Resources\AtkItemPrices\Pages;

use App\Filament\Resources\AtkItemPrices\AtkItemPriceResource;
use Filament\Resources\Pages\EditRecord;

class EditAtkItemPrice extends EditRecord
{
    protected static string $resource = AtkItemPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ViewAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}