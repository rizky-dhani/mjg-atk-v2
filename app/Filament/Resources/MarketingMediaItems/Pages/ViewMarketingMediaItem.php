<?php

namespace App\Filament\Resources\MarketingMediaItems\Pages;

use App\Filament\Resources\MarketingMediaItems\MarketingMediaItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMarketingMediaItem extends ViewRecord
{
    protected static string $resource = MarketingMediaItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return \App\Filament\Resources\MarketingMediaItems\Schemas\MarketingMediaItemInfolist::configure($schema);
    }

    public function getBreadcrumbs(): array
    {
        return [
            $this->getResource()::getUrl('index') => $this->getResource()::getPluralModelLabel(),
            $this->getRecordTitle() => null,
        ];
    }
}
