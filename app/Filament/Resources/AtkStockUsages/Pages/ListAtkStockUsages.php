<?php

namespace App\Filament\Resources\AtkStockUsages\Pages;

use App\Filament\Resources\AtkStockUsages\AtkStockUsageResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListAtkStockUsages extends ListRecords
{
    protected static string $resource = AtkStockUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data) {
                    $data['division_id'] = auth()->user()->division_id;
                    $data['requester_id'] = auth()->user()->id;

                    return $data;
                })
                ->visible(fn () => auth()->user()->hasRole('Admin'))
                ->modalWidth(Width::SevenExtraLarge)
                ->successNotification(
                    Notification::make()
                        ->title('Request Pengeluaran ATK berhasil dibuat!')
                        ->success()
                ),
        ];
    }
}
