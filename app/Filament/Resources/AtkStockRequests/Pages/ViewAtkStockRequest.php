<?php

namespace App\Filament\Resources\AtkStockRequests\Pages;

use App\Exports\AtkStockRequestExport;
use App\Filament\Actions\ApprovalAction;
use App\Filament\Actions\ResubmitAction;
use App\Filament\Resources\AtkStockRequests\AtkStockRequestResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class ViewAtkStockRequest extends ViewRecord
{
    protected static string $resource = AtkStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->successNotificationTitle('Permintaan stok ATK berhasil diperbarui'),
            Action::make('export')
                ->label('Export')
                ->icon(Heroicon::ArrowDownTray)
                ->color('success')
                ->action(fn () => Excel::download(
                    new AtkStockRequestExport($this->record->id),
                    'atk_stock_request_'.$this->record->request_number.'.xlsx'
                )),
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
