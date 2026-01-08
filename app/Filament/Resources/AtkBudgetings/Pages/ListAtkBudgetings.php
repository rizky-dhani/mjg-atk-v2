<?php

namespace App\Filament\Resources\AtkBudgetings\Pages;

use App\Filament\Resources\AtkBudgetings\AtkBudgetingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAtkBudgetings extends ListRecords
{
    protected static string $resource = AtkBudgetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->successNotificationTitle('ATK Budgeting created'),
        ];
    }
}