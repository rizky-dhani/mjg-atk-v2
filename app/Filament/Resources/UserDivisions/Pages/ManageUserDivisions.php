<?php

namespace App\Filament\Resources\UserDivisions\Pages;

use App\Filament\Resources\UserDivisions\UserDivisionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageUserDivisions extends ManageRecords
{
    protected static string $resource = UserDivisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->successNotificationTitle('User Division created'),
        ];
    }
}
