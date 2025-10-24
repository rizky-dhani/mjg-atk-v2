<?php

namespace App\Filament\Resources\AtkCategories\Pages;

use App\Filament\Resources\AtkCategories\AtkCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAtkCategories extends ListRecords
{
    protected static string $resource = AtkCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}