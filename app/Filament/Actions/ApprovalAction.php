<?php

namespace App\Filament\Actions;

use App\Models\Approval;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\ApprovalValidationService;
use App\Services\ApprovalProcessingService;
use App\Services\ApprovalHistoryService;
use App\Services\StockUpdateService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class ApprovalAction
{
    public static function make(): Action
    {
        return Action::make('approve')
            ->label('Approve Request')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Approve Request')
            ->modalDescription('Are you sure you want to approve this request?')
            ->modalSubmitActionLabel('Approve')
            ->visible(function (Model $record) {
                $validationService = new ApprovalValidationService();
                $user = auth()->user();

                // Check if the user can approve this specific record
                return $validationService->canUserApprove($record, $user);
            })
            ->action(function (Model $record) {
                $validationService = new ApprovalValidationService();
                $historyService = new ApprovalHistoryService();
                $stockUpdateService = new StockUpdateService();
                $processingService = new ApprovalProcessingService($validationService, $historyService, $stockUpdateService);
                $approvalService = new ApprovalService($validationService, $processingService, $historyService, $stockUpdateService);
                
                $user = auth()->user();

                // Find the active approval flow for this model type
                $approvalFlow = \App\Models\ApprovalFlow::where('model_type', get_class($record))
                    ->where('is_active', true)
                    ->first();

                if (! $approvalFlow) {
                    throw new \Exception('No active approval flow found for this record type.');
                }

                // Create an approval record if one doesn't exist
                $approval = $record->approval;
                if (! $approval) {
                    $approval = $record->approval()->create([
                        'flow_id' => $approvalFlow->id,
                        'current_step' => 1,
                        'status' => 'pending',
                    ]);

                    // Log initial submission to history
                    $approvalService->logNewApproval($record, $user);
                }

                // Process the approval step using the service method
                $approvalService->processApprovalStep($approval, $user, 'approve', 'Request approved');

                // Synchronize approval status
                $approvalService->syncApprovalStatus($record);

                return 'Request approved successfully.';
            });
    }

    public static function makeApprove(): Action
    {
        return Action::make('approve')
            ->label('Approve Request')
            ->color('success')
            ->icon(fn () => Heroicon::CheckCircle)
            ->requiresConfirmation()
            ->modalHeading('Approve Request')
            ->modalDescription('Are you sure you want to approve this request?')
            ->modalSubmitActionLabel('Approve')
            ->visible(function (Model $record) {
                $validationService = new ApprovalValidationService();
                $user = auth()->user();

                // Check if the user can approve this specific record
                return $validationService->canUserApprove($record, $user);
            })
            ->action(function (Model $record) {
                $validationService = new ApprovalValidationService();
                $historyService = new ApprovalHistoryService();
                $stockUpdateService = new StockUpdateService();
                $processingService = new ApprovalProcessingService($validationService, $historyService, $stockUpdateService);
                $approvalService = new ApprovalService($validationService, $processingService, $historyService, $stockUpdateService);
                
                $user = auth()->user();

                // Find the active approval flow for this model type
                $approvalFlow = \App\Models\ApprovalFlow::where('model_type', get_class($record))
                    ->where('is_active', true)
                    ->first();

                if (! $approvalFlow) {
                    throw new \Exception('No active approval flow found for this record type.');
                }

                // Process the approval step using the service method
                $approval = $record->approval;
                if (! $approval) {
                    $approval = $record->approval()->create([
                        'flow_id' => $approvalFlow->id,
                        'current_step' => 1,
                        'status' => 'pending',
                    ]);
                }

                $approvalService->processApprovalStep($approval, $user, 'approve', 'Request approved');

                // Synchronize approval status
                $approvalService->syncApprovalStatus($record);

                return 'Request approved successfully.';
            });
    }

    public static function makeReject(): Action
    {
        return Action::make('reject')
            ->label('Reject Request')
            ->color('danger')
            ->icon(fn () => Heroicon::XCircle)
            ->requiresConfirmation()
            ->modalHeading('Reject Request')
            ->modalDescription('Are you sure you want to reject this request?')
            ->modalSubmitActionLabel('Reject')
            ->schema([
                \Filament\Forms\Components\Textarea::make('rejection_notes')
                    ->label('Rejection Reason')
                    ->placeholder('Provide a reason for rejecting this request...')
                    ->required(),
            ])
            ->visible(function (Model $record) {
                $validationService = new ApprovalValidationService();
                $user = auth()->user();

                // Check if the user can approve this specific record (same check for rejection)
                return $validationService->canUserApprove($record, $user);
            })
            ->action(function (array $data, Model $record) {
                $validationService = new ApprovalValidationService();
                $historyService = new ApprovalHistoryService();
                $stockUpdateService = new StockUpdateService();
                $processingService = new ApprovalProcessingService($validationService, $historyService, $stockUpdateService);
                $approvalService = new ApprovalService($validationService, $processingService, $historyService, $stockUpdateService);
                
                $user = auth()->user();

                // Find the active approval flow for this model type
                $approvalFlow = \App\Models\ApprovalFlow::where('model_type', get_class($record))
                    ->where('is_active', true)
                    ->first();

                if (! $approvalFlow) {
                    throw new \Exception('No active approval flow found for this record type.');
                }

                // Process the approval step using the service method
                $approval = $record->approval;
                if (! $approval) {
                    $approval = $record->approval()->create([
                        'flow_id' => $approvalFlow->id,
                        'current_step' => 1,
                        'status' => 'pending',
                    ]);
                }

                $approvalService->processApprovalStep($approval, $user, 'reject', $data['rejection_notes'] ?? null);

                return 'Request rejected successfully.';
            });
    }

    // Keep the original makeWithRejection method for backward compatibility
    public static function makeWithRejection(): Action
    {
        return Action::make('approve_with_rejection')
            ->label('Approve/Reject Request')
            ->color('success')
            ->visible(function (Model $record) {
                $validationService = new ApprovalValidationService();
                $user = auth()->user();

                // Check if the user can approve this specific record
                return $validationService->canUserApprove($record, $user);
            })
            ->form([
                \Filament\Forms\Components\Select::make('action')
                    ->options([
                        'approve' => 'Approve',
                        'reject' => 'Reject',
                    ])
                    ->required()
                    ->default('approve'),
                \Filament\Forms\Components\Textarea::make('notes')
                    ->label('Notes (Optional)')
                    ->placeholder('Add any notes regarding your decision...'),
            ])
            ->action(function (array $data, Model $record) {
                $validationService = new ApprovalValidationService();
                $historyService = new ApprovalHistoryService();
                $stockUpdateService = new StockUpdateService();
                $processingService = new ApprovalProcessingService($validationService, $historyService, $stockUpdateService);
                $approvalService = new ApprovalService($validationService, $processingService, $historyService, $stockUpdateService);
                
                $user = auth()->user();

                // Find the active approval flow for this model type
                $approvalFlow = \App\Models\ApprovalFlow::where('model_type', get_class($record))
                    ->where('is_active', true)
                    ->first();

                if (! $approvalFlow) {
                    throw new \Exception('No active approval flow found for this record type.');
                }

                // Process the approval step using the service method
                $approval = $record->approval;
                if (! $approval) {
                    $approval = $record->approval()->create([
                        'flow_id' => $approvalFlow->id,
                        'current_step' => 1,
                        'status' => 'pending',
                    ]);
                }

                $action = $data['action'] ?? 'approve';
                $notes = $data['notes'] ?? null;

                if ($action === 'approve') {
                    $approvalService->processApprovalStep($approval, $user, 'approve', $notes ?: 'Request approved');
                } else {
                    $approvalService->processApprovalStep($approval, $user, 'reject', $notes);
                }

                // Synchronize approval status
                $approvalService->syncApprovalStatus($record);

                $message = $action === 'approve' ? 'Request approved successfully.' : 'Request rejected.';

                return $message;
            });
    }
}