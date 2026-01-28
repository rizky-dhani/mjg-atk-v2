<?php

namespace App\Filament\Resources\MarketingMediaStockUsages\Pages;

use App\Filament\Resources\MarketingMediaStockUsages\MarketingMediaStockUsageResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListMarketingMediaStockUsages extends ListRecords
{
    protected static string $resource = MarketingMediaStockUsageResource::class;

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
                        ->title('Request Pengeluaran Marketing Media berhasil dibuat!')
                        ->success()
                ),
        ];
    }
}
