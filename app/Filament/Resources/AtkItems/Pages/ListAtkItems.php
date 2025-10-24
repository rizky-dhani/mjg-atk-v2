<?php

namespace App\Filament\Resources\AtkItems\Pages;

use App\Filament\Resources\AtkItems\AtkItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAtkItems extends ListRecords
{
    protected static string $resource = AtkItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}