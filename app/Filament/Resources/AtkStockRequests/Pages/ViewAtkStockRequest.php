<?php

namespace App\Filament\Resources\AtkStockRequests\Pages;

use App\Filament\Actions\ApprovalAction;
use App\Filament\Actions\ResubmitAction;
use Filament\Notifications\Notification;
use App\Filament\Resources\AtkStockRequests\AtkStockRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAtkStockRequest extends ViewRecord
{
    protected static string $resource = AtkStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            ApprovalAction::makeApprove()->successNotification(
                Notification::make()
                    ->title("Permintaan ATK berhasil disetujui!")
                    ->success(),
            ),
            ApprovalAction::makeReject()->successNotification(
                Notification::make()
                    ->title("Permintaan ATK berhasil ditolak!")
                    ->success(),
            ),
            ResubmitAction::make(),
        ];
    }
}
