<?php

namespace App\Filament\Resources\AtkItemPrices\Pages;

use App\Filament\Resources\AtkItemPrices\AtkItemPriceResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAtkItemPrice extends ViewRecord
{
    protected static string $resource = AtkItemPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make()
                ->successNotificationTitle('ATK Item Price updated'),
        ];
    }
}
