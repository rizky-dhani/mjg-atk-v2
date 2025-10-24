<?php

namespace App\Filament\Resources\MarketingMediaStockRequests\Pages;

use App\Filament\Resources\MarketingMediaStockRequests\MarketingMediaStockRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMarketingMediaStockRequest extends ViewRecord
{
    protected static string $resource = MarketingMediaStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
