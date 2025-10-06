<?php

namespace App\Filament\Resources\AtkStockUsages\Pages;

use App\Filament\Resources\AtkStockUsages\AtkStockUsageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAtkStockUsage extends EditRecord
{
    protected static string $resource = AtkStockUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
