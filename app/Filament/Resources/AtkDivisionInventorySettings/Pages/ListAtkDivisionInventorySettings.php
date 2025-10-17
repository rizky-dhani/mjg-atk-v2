<?php

namespace App\Filament\Resources\AtkDivisionInventorySettings\Pages;

use App\Filament\Resources\AtkDivisionInventorySettings\AtkDivisionInventorySettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAtkDivisionInventorySettings extends ListRecords
{
    protected static string $resource = AtkDivisionInventorySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
