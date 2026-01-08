<?php

namespace App\Filament\Resources\AtkTransferStocks\Pages;

use App\Filament\Resources\AtkTransferStocks\AtkTransferStockResource;
use App\Services\TransferStockApprovalService;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ViewAtkTransferStock extends ViewRecord
{
    protected static string $resource = AtkTransferStockResource::class;

    protected function getHeaderActions(): array
    {
        // Add approval buttons if the user has permission
        $record = $this->getRecord();
        $approvalService = new TransferStockApprovalService();
        
        // Check if user is authorized to edit (requester, users with GA initial, or Admin role)
        $user = Auth::user();
        $isRequester = $user->id == $record->requester_id;
        $hasDivision = $user->division && (strtolower($user->division->initial) === 'GA' || strtolower($user->division->name) === 'General Affair' || strtolower($user->division->name) === 'General Affairs');
        $hasRole = $user->hasRole('Admin') || $user->hasRole('Super Admin');

        // Only allow editing if the request hasn't been approved yet and the user is authorized
        $canEdit = false;
        $approval = $record->approval;
        if ($approval && $approval->status === 'pending') {
            // Can edit if user is the requester, GA division user, or has admin role
            $canEdit = $isRequester || $hasDivision || $hasRole;
        }

        $actions = [];
        if ($canEdit) {
            $actions[] = \Filament\Actions\EditAction::make()
                ->successNotificationTitle('ATK Stock Transfer updated')
                ->modalWidth(Width::SevenExtraLarge);
        }
        
        if ($approvalService->canApprove($record)) {
            // Add approve action
            $actions[] = Action::make('approve')
                ->label('Approve')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Transfer Stock')
                ->modalDescription('Are you sure you want to approve this transfer stock request?')
                ->action(function () use ($record, $approvalService) {
                    if ($approvalService->approve($record)) {
                        Notification::make()
                            ->title('Success')
                            ->body('Transfer stock request has been approved successfully.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Error')
                            ->body('Failed to approve transfer stock request.')
                            ->danger()
                            ->send();
                    }
                });

            // Add reject action
            $actions[] = Action::make('reject')
                ->label('Reject')
                ->color('danger')
                ->requiresConfirmation()
                ->modalWidth(Width::SevenExtraLarge)
                ->modalHeading('Reject Transfer Stock')
                ->modalDescription('Are you sure you want to reject this transfer stock request?')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Reason for Rejection')
                        ->required()
                        ->maxLength(65535),
                ])
                ->action(function (array $data) use ($record, $approvalService) {
                    if ($approvalService->reject($record, $data['rejection_reason'])) {
                        Notification::make()
                            ->title('Success')
                            ->body('Transfer stock request has been rejected successfully.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Error')
                            ->body('Failed to reject transfer stock request.')
                            ->danger()
                            ->send();
                    }
                });
        }

        return $actions;
    }
}