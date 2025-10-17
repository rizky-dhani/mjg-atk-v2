<?php

namespace App\Filament\Resources\AtkStockRequests\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\AtkStockRequests\AtkStockRequestResource;

class ListAtkStockRequests extends ListRecords
{
    protected static string $resource = AtkStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data) {
                    $data['division_id'] = auth()->user()->division_id;
                    $data['requester_id'] = auth()->user()->id;
                    return $data;
                })
                ->visible(fn() => auth()->user()->hasRole('Admin'))
                ->modalWidth(Width::SevenExtraLarge)
                ->successNotification(
                    Notification::make()
                        ->title('Permintaan ATK berhasil dibuat!')
                        ->success()
                ),
        ];
    }
}
