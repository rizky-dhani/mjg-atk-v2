<?php

namespace App\Filament\Resources\MarketingMediaItems\Pages;

use App\Filament\Resources\MarketingMediaItems\MarketingMediaItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingMediaItems extends ListRecords
{
    protected static string $resource = MarketingMediaItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}