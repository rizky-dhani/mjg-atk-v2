<?php

namespace App\Filament\Resources\AtkDivisionStocks\Pages;

use App\Filament\Resources\AtkDivisionStocks\AtkDivisionStockResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAtkDivisionStock extends ViewRecord
{
    protected static string $resource = AtkDivisionStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->successNotificationTitle('ATK Division Stock updated'),
        ];
    }
}
