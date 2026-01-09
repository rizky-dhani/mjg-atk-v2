<?php

namespace App\Filament\Resources\AtkStockRequests\Pages;

use App\Filament\Resources\AtkStockRequests\AtkStockRequestResource;
use App\Models\AtkStockRequest;
use App\Services\ApprovalProcessingService;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

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
                ->after(function (AtkStockRequest $record) {
                    app(ApprovalProcessingService::class)->createApproval($record, AtkStockRequest::class);
                })
                ->visible(fn () => auth()->user()->hasRole('Admin'))
                ->modalWidth(Width::SevenExtraLarge)
                ->successNotification(
                    Notification::make()
                        ->title('ATK stock request created successfully')
                        ->success()
                ),
        ];
    }
}
