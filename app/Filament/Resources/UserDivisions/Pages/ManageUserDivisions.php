<?php

namespace App\Filament\Resources\UserDivisions\Pages;

use App\Exports\UserDivisionExport;
use App\Filament\Resources\UserDivisions\UserDivisionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Maatwebsite\Excel\Facades\Excel;

class ManageUserDivisions extends ManageRecords
{
    protected static string $resource = UserDivisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => Excel::download(
                    new UserDivisionExport,
                    'user_divisions_'.now()->format('Y_m_d_H_i_s').'.xlsx',
                )),
            CreateAction::make()
                ->successNotificationTitle('User Division created'),
        ];
    }
}
