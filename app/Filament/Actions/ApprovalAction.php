<?php

namespace App\Filament\Actions;

use App\Models\Approval;
use App\Models\User;
use App\Services\ApprovalHistoryService;
use App\Services\ApprovalProcessingService;
use App\Services\ApprovalService;
use App\Services\ApprovalValidationService;
use App\Services\StockUpdateService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class ApprovalAction
{
    public static function make(): Action
    {
        return Action::make('approve')
            ->label('Setujui Permintaan')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Setujui Permintaan')
            ->modalDescription('Apakah Anda yakin ingin menyetujui permintaan ini?')
            ->modalSubmitActionLabel('Setujui')
            ->visible(function (Model $record) {
                $validationService = new ApprovalValidationService;
                $user = auth()->user();

                // Check if the user can approve this specific record
                return $validationService->canUserApprove($record, $user);
            })
            ->action(function (Model $record) {
                $validationService = new ApprovalValidationService;
                $historyService = new ApprovalHistoryService;
                $stockUpdateService = app(StockUpdateService::class);
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
                $approvalService->processApprovalStep($approval, $user, 'approve', 'Permintaan disetujui');

                // Synchronize approval status
                $approvalService->syncApprovalStatus($record);

                return 'Permintaan berhasil disetujui.';
            });
    }

    public static function makeApprove(): Action
    {
        return Action::make('approve')
            ->label('Setujui Permintaan')
            ->color('success')
            ->icon(fn () => Heroicon::CheckCircle)
            ->requiresConfirmation()
            ->modalHeading('Setujui Permintaan')
            ->modalSubmitActionLabel('Setujui')
            ->modalWidth(\Filament\Support\Enums\Width::Large)
            ->schema(fn (Model $record) => [
                \Filament\Forms\Components\Section::make('Ringkasan Permintaan')
                    ->compact()
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('requester')
                            ->label('Pemohon')
                            ->content($record->requester->name.' ('.$record->division->name.')'),
                        \Filament\Forms\Components\Placeholder::make('items')
                            ->label('Barang')
                            ->content(function () use ($record) {
                                return $record->items->map(fn ($item) => "{$item->item->name} ({$item->quantity})")->implode(', ');
                            }),
                    ]),
                \Filament\Forms\Components\Placeholder::make('confirmation')
                    ->content('Apakah Anda yakin ingin menyetujui permintaan ini?'),
            ])
            ->visible(function (Model $record) {
                $validationService = new ApprovalValidationService;
                $user = auth()->user();

                // Check if the user can approve this specific record
                return $validationService->canUserApprove($record, $user);
            })
            ->action(function (Model $record) {
                $validationService = new ApprovalValidationService;
                $historyService = new ApprovalHistoryService;
                $stockUpdateService = app(StockUpdateService::class);
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

                $approvalService->processApprovalStep($approval, $user, 'approve', 'Permintaan disetujui');

                // Synchronize approval status
                $approvalService->syncApprovalStatus($record);

                return 'Permintaan berhasil disetujui.';
            });
    }

    public static function makeReject(): Action
    {
        return Action::make('reject')
            ->label('Tolak Permintaan')
            ->color('danger')
            ->icon(fn () => Heroicon::XCircle)
            ->requiresConfirmation()
            ->modalHeading('Tolak Permintaan')
            ->modalSubmitActionLabel('Tolak')
            ->modalWidth(\Filament\Support\Enums\Width::Large)
            ->schema(fn (Model $record) => [
                \Filament\Forms\Components\Section::make('Ringkasan Permintaan')
                    ->compact()
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('requester')
                            ->label('Pemohon')
                            ->content($record->requester->name.' ('.$record->division->name.')'),
                        \Filament\Forms\Components\Placeholder::make('items')
                            ->label('Barang')
                            ->content(function () use ($record) {
                                return $record->items->map(fn ($item) => "{$item->item->name} ({$item->quantity})")->implode(', ');
                            }),
                    ]),
                \Filament\Forms\Components\Textarea::make('rejection_notes')
                    ->label('Alasan Penolakan')
                    ->placeholder('Berikan alasan penolakan permintaan ini...')
                    ->required(),
            ])
            ->visible(function (Model $record) {
                $validationService = new ApprovalValidationService;
                $user = auth()->user();

                // Check if the user can approve this specific record (same check for rejection)
                return $validationService->canUserApprove($record, $user);
            })
            ->action(function (array $data, Model $record) {
                $validationService = new ApprovalValidationService;
                $historyService = new ApprovalHistoryService;
                $stockUpdateService = app(StockUpdateService::class);
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

                return 'Permintaan berhasil ditolak.';
            });
    }

    // Keep the original makeWithRejection method for backward compatibility
    public static function makeWithRejection(): Action
    {
        return Action::make('approve_with_rejection')
            ->label('Setujui/Tolak Permintaan')
            ->color('success')
            ->visible(function (Model $record) {
                $validationService = new ApprovalValidationService;
                $user = auth()->user();

                // Check if the user can approve this specific record
                return $validationService->canUserApprove($record, $user);
            })
            ->form([
                \Filament\Forms\Components\Select::make('action')
                    ->label('Tindakan')
                    ->options([
                        'approve' => 'Setujui',
                        'reject' => 'Tolak',
                    ])
                    ->required()
                    ->default('approve'),
                \Filament\Forms\Components\Textarea::make('notes')
                    ->label('Catatan (Opsional)')
                    ->placeholder('Tambahkan catatan terkait keputusan Anda...'),
            ])
            ->action(function (array $data, Model $record) {
                $validationService = new ApprovalValidationService;
                $historyService = new ApprovalHistoryService;
                $stockUpdateService = app(StockUpdateService::class);
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
                    $approvalService->processApprovalStep($approval, $user, 'approve', $notes ?: 'Permintaan disetujui');
                } else {
                    $approvalService->processApprovalStep($approval, $user, 'reject', $notes);
                }

                // Synchronize approval status
                $approvalService->syncApprovalStatus($record);

                $message = $action === 'approve' ? 'Permintaan berhasil disetujui.' : 'Permintaan berhasil ditolak.';

                return $message;
            });
    }
}
