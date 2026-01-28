<?php

namespace App\Filament\Resources\MarketingMediaStockRequests\Pages;

use App\Filament\Resources\MarketingMediaStockRequests\MarketingMediaStockRequestResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListMarketingMediaStockRequests extends ListRecords
{
    protected static string $resource = MarketingMediaStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data) {
                    $data['division_id'] = $data['division_id'] ?? auth()->user()->divisions->first()?->id;
                    $data['requester_id'] = auth()->user()->id;

                    return $data;
                })
                ->visible(fn () => auth()->user()->hasRole('Admin'))
                ->modalWidth(Width::SevenExtraLarge)
                ->successNotification(
                    Notification::make()
                        ->title('Request Permintaan Marketing Media berhasil dibuat!')
                        ->success()
                ),
        ];
    }
}
