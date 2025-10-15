<?php

namespace App\Filament\Resources\AtkStockRequests\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Actions\ApprovalAction;
use Illuminate\Database\Eloquent\Builder;

class AtkStockRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request_number')
                    ->label('Request Number')
                    ->searchable(),
                TextColumn::make('requester.name')
                    ->label('Requester')
                    ->searchable(),
                TextColumn::make('division.name')
                    ->label('Division')
                    ->searchable(),
                TextColumn::make('approval_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        $approval = $record->approval;
                        if (!$approval) {
                            return 'Pending';
                        }
                        
                        // Get the latest approval step approval
                        $latestApproval = $approval->approvalStepApprovals()
                            ->with('user')
                            ->latest('approved_at')
                            ->first();
                        
                        if ($latestApproval) {
                            $status = ucfirst($latestApproval->status);
                            $approver = $latestApproval->user ? $latestApproval->user->name : 'Unknown';
                            return "{$status} by {$approver}";
                        }
                        
                        return $approval->status ? ucfirst($approval->status) : 'Pending';
                    })
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'approved') => 'success',
                        str_contains($state, 'rejected') => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Request')
                    ->modalDescription('Are you sure you want to approve this request?')
                    ->modalSubmitActionLabel('Approve')
                    ->visible(function ($record) {
                        $approvalService = app(\App\Services\ApprovalService::class);
                        $user = auth()->user();
                        
                        // Check if the user can approve this specific record
                        return $approvalService->canUserApprove($record, $user);
                    })
                    ->action(function ($record) {
                        $approvalService = app(\App\Services\ApprovalService::class);
                        $user = auth()->user();
                        
                        // Find the active approval flow for this model type
                        $approvalFlow = \App\Models\ApprovalFlow::where('model_type', get_class($record))
                            ->where('is_active', true)
                            ->first();
                        
                        if (!$approvalFlow) {
                            throw new \Exception('No active approval flow found for this record type.');
                        }
                        
                        // Create an approval record if one doesn't exist
                        $approval = $record->approval;
                        if (!$approval) {
                            $approval = $record->approval()->create([
                                'flow_id' => $approvalFlow->id,
                                'current_step' => 1,
                                'status' => 'pending',
                            ]);
                        }
                        
                        // Get eligible approval steps for this user
                        $eligibleSteps = $approvalService->getEligibleApprovalSteps($record, $user);
                        
                        if ($eligibleSteps->isEmpty()) {
                            throw new \Exception('No eligible approval steps found for this user.');
                        }
                        
                        // Process the first eligible step (in case there are multiple)
                        $step = $eligibleSteps->first();
                        
                        // Create the approval step record
                        \App\Models\ApprovalStepApproval::create([
                            'approval_id' => $approval->id,
                            'step_id' => $step->id,
                            'user_id' => $user->id,
                            'status' => 'approved',
                            'approved_at' => now(),
                        ]);
                        
                        // Check if all required steps are now approved
                        $allSteps = $approval->approvalFlow->approvalFlowSteps->sortBy('step_number');
                        $approvedSteps = $approval->approvalStepApprovals->pluck('step_id');
                        
                        $unapprovedSteps = $allSteps->filter(function ($step) use ($approvedSteps) {
                            return !$approvedSteps->contains($step->id);
                        });
                        
                        // If no unapproved steps remain, mark the overall approval as approved
                        if ($unapprovedSteps->isEmpty()) {
                            $approval->update([
                                'status' => 'approved',
                                'current_step' => $allSteps->last()?->step_number ?? null,
                            ]);
                        } else {
                            // Update to the next step number
                            $nextStep = $unapprovedSteps->first();
                            $approval->update([
                                'current_step' => $nextStep?->step_number ?? $approval->current_step,
                            ]);
                        }
                        
                        return 'Request approved successfully.';
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
