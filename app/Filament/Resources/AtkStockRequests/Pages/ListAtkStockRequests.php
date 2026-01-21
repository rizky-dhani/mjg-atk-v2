<?php

namespace App\Filament\Resources\AtkStockRequests\Pages;

use App\Enums\AtkStockRequestStatus;
use App\Filament\Resources\AtkStockRequests\AtkStockRequestResource;
use App\Models\AtkStockRequest;
use App\Services\ApprovalProcessingService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListAtkStockRequests extends ListRecords
{
    protected static string $resource = AtkStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('create_draft')
                ->label('Buat Draft')
                ->modalHeading('Buat Draft Permintaan Stok ATK')
                ->mutateFormDataUsing(function (array $data) {
                    $data['division_id'] = $data['division_id'] ?? auth()->user()->division_id;
                    $data['requester_id'] = auth()->user()->id;
                    $data['status'] = AtkStockRequestStatus::Draft;

                    return $data;
                })
                ->visible(fn () => auth()->user()->can('create atk-stock-request'))
                ->modalWidth(Width::SevenExtraLarge)
                ->successNotificationTitle('Draft permintaan stok ATK berhasil dibuat'),
            CreateAction::make('publish')
                ->label('Buat & Publish')
                ->modalHeading('Buat & Publish Permintaan Stok ATK')
                ->color('success')
                ->mutateFormDataUsing(function (array $data) {
                    $data['division_id'] = $data['division_id'] ?? auth()->user()->division_id;
                    $data['requester_id'] = auth()->user()->id;
                    $data['status'] = AtkStockRequestStatus::Published;

                    return $data;
                })
                ->after(function (AtkStockRequest $record) {
                    app(ApprovalProcessingService::class)->createApproval($record, AtkStockRequest::class);
                })
                ->visible(fn () => auth()->user()->can('create atk-stock-request'))
                ->modalWidth(Width::SevenExtraLarge)
                ->successNotificationTitle('Permintaan stok ATK berhasil dipublikasikan'),
        ];
    }
}
