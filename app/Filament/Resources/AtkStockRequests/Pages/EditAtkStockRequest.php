<?php

namespace App\Filament\Resources\AtkStockRequests\Pages;

use App\Filament\Resources\AtkStockRequests\AtkStockRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAtkStockRequest extends EditRecord
{
    protected static string $resource = AtkStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
