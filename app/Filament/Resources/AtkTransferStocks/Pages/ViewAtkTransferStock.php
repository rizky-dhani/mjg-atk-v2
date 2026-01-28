<?php

namespace App\Filament\Resources\AtkTransferStocks\Pages;

use App\Filament\Resources\AtkTransferStocks\AtkTransferStockResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;

class ViewAtkTransferStock extends ViewRecord
{
    protected static string $resource = AtkTransferStockResource::class;

    protected function getHeaderActions(): array
    {
        // Add approval buttons if the user has permission
        $record = $this->getRecord();

        // Check if user is authorized to edit (requester, users with GA initial, or Admin role)
        $user = Auth::user();
        if (! $user) {
            return [];
        }
        $isRequester = $user->id == $record->requester_id;
        $isGA = $user->isGA();
        $hasRole = $user->hasRole('Admin') || $user->hasRole('Super Admin');

        // Only allow editing if the request hasn't been approved yet and the user is authorized
        $canEdit = false;
        $approval = $record->approval;
        if ($approval && $approval->status === 'pending') {
            // Can edit if user is the requester, GA division user, or has admin role
            $canEdit = $isRequester || $isGA || $hasRole;
        }

        $actions = [];
        if ($canEdit) {
            $actions[] = \Filament\Actions\EditAction::make()
                ->successNotificationTitle('ATK Stock Transfer updated')
                ->modalWidth(Width::SevenExtraLarge);
        }

        $actions[] = \App\Filament\Actions\ApprovalAction::makeApprove()
            ->name('approve_request')
            ->successNotification(
                Notification::make()
                    ->title('Permintaan transfer stok berhasil disetujui')
                    ->success(),
            );

        $actions[] = \App\Filament\Actions\ApprovalAction::makeReject()
            ->name('reject_request')
            ->successNotification(
                Notification::make()
                    ->title('Permintaan transfer stok berhasil ditolak')
                    ->success(),
            );

        return $actions;
    }
}
