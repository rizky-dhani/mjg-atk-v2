<?php

namespace App\Filament\Resources\AtkItems\Pages;

use App\Filament\Resources\AtkItems\AtkItemResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewAtkItem extends ViewRecord
{
    protected static string $resource = AtkItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return \App\Filament\Resources\AtkItems\Schemas\AtkItemInfolist::configure($schema);
    }
}