<?php

namespace App\Filament\Resources\AtkCategories\Pages;

use App\Filament\Resources\AtkCategories\AtkCategoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAtkCategory extends ViewRecord
{
    protected static string $resource = AtkCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->successNotificationTitle('ATK Category updated'),
        ];
    }
}
