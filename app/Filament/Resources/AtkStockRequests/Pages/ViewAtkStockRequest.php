<?php

namespace App\Filament\Resources\AtkStockRequests\Pages;

use App\Filament\Actions\ApprovalAction;
use App\Filament\Actions\ResubmitAction;
use App\Filament\Resources\AtkStockRequests\AtkStockRequestResource;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewAtkStockRequest extends ViewRecord
{
    protected static string $resource = AtkStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->successNotificationTitle('Permintaan stok ATK berhasil diperbarui'),
            ApprovalAction::makeApprove()->successNotification(
                Notification::make()
                    ->title('Permintaan stok ATK berhasil disetujui')
                    ->success(),
            ),
            ApprovalAction::makeReject()->successNotification(
                Notification::make()
                    ->title('Permintaan stok ATK berhasil ditolak')
                    ->success(),
            ),
            ResubmitAction::make(),
        ];
    }
}
