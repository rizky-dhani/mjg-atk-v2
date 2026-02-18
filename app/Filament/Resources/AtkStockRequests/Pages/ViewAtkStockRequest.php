<?php

namespace App\Filament\Resources\AtkStockRequests\Pages;

use App\Enums\AtkStockRequestStatus;
use App\Exports\AtkStockRequestExport;
use App\Filament\Actions\ApprovalAction;
use App\Filament\Actions\ResubmitAction;
use App\Filament\Resources\AtkStockRequests\AtkStockRequestResource;
use App\Models\AtkStockRequest;
use App\Services\ApprovalProcessingService;
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
                ->successNotificationTitle('Permintaan stok ATK berhasil diperbarui')
                ->extraModalFooterActions([
                    Action::make('publish')
                        ->label('Publish')
                        ->color('success')
                        ->visible(fn () => $this->record->status === AtkStockRequestStatus::Draft
                            && auth()->id() === $this->record->requester_id)
                        ->requiresConfirmation()
                        ->action(function () {
                            $this->record->update(['status' => AtkStockRequestStatus::Published]);
                            app(ApprovalProcessingService::class)->createApproval($this->record, AtkStockRequest::class);
                            Notification::make()
                                ->title('Permintaan stok ATK berhasil dipublikasikan')
                                ->success()
                                ->send();
                        }),
                ]),
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
