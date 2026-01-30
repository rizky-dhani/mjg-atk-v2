<?php

namespace App\Filament\Resources\AtkBudgetings\Pages;

use App\Filament\Resources\AtkBudgetings\AtkBudgetingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAtkBudgeting extends EditRecord
{
    protected static string $resource = AtkBudgetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
