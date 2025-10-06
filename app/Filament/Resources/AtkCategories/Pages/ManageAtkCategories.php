<?php

namespace App\Filament\Resources\AtkCategories\Pages;

use App\Filament\Resources\AtkCategories\AtkCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAtkCategories extends ManageRecords
{
    protected static string $resource = AtkCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
