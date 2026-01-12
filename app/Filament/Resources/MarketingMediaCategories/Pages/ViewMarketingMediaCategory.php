<?php

namespace App\Filament\Resources\MarketingMediaCategories\Pages;

use App\Filament\Resources\MarketingMediaCategories\MarketingMediaCategoryResource;
use App\Filament\Resources\MarketingMediaCategories\Schemas\MarketingMediaCategoryInfolist;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewMarketingMediaCategory extends ViewRecord
{
    protected static string $resource = MarketingMediaCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return MarketingMediaCategoryInfolist::configure($schema);
    }
}
