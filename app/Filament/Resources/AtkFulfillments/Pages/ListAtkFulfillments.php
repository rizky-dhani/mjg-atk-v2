<?php

namespace App\Filament\Resources\AtkFulfillments\Pages;

use App\Exports\AtkFulfillmentExport;
use App\Filament\Resources\AtkFulfillments\AtkFulfillmentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class ListAtkFulfillments extends ListRecords
{
    protected static string $resource = AtkFulfillmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export')
                ->icon(Heroicon::ArrowDownTray)
                ->color('success')
                ->action(fn () => Excel::download(
                    new AtkFulfillmentExport($this->getFilteredTableQuery()->pluck('id')->toArray()),
                    'atk_fulfillments_'.now()->format('Y-m-d_H-i-s').'.xlsx'
                )),
        ];
    }
}
