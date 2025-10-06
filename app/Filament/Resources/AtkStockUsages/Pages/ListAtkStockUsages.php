<?php

namespace App\Filament\Resources\AtkStockUsages\Pages;

use App\Filament\Resources\AtkStockUsages\AtkStockUsageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAtkStockUsages extends ListRecords
{
    protected static string $resource = AtkStockUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
