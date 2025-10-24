<?php

namespace App\Filament\Resources\MarketingMediaCategories\Pages;

use App\Filament\Resources\MarketingMediaCategories\MarketingMediaCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingMediaCategories extends ListRecords
{
    protected static string $resource = MarketingMediaCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}