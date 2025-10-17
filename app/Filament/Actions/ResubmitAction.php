<?php

namespace App\Filament\Actions;

use App\Models\Approval;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Services\ApprovalService;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\EditAction as BaseEditAction;
use App\Filament\Resources\AtkStockRequests\Schemas\AtkStockRequestForm;

class ResubmitAction
{
    public static function make(): Action
    {
        return Action::make('resubmit')
            ->label('Resubmit for Approval')
            ->color('warning')
            ->modalWidth(Width::SevenExtraLarge)
            ->icon(fn() => Heroicon::ArrowPath)
            ->visible(function ($record) {
                // Only show the resubmit action if the record has been rejected and has not been resubmitted since the last rejection and the current user is the requester
                if (!$record) {
                    return false;
                }
                
                // Get all approval history records for this model, ordered by performed_at (oldest first)
                $approvalHistory = \App\Models\ApprovalHistory::where('approvable_type', get_class($record))
                    ->where('approvable_id', $record->id)
                    ->orderBy('performed_at', 'asc')
                    ->get();
                
                $lastRejection = null;
                $lastSubmissionAfterRejection = null;
                
                // Iterate through history records from oldest to newest
                foreach ($approvalHistory as $history) {
                    if ($history->action === 'rejected') {
                        $lastRejection = $history;
                        $lastSubmissionAfterRejection = null; // Reset submission after this rejection
                    } else if ($history->action === 'submitted' && $lastRejection) {
                        // This submission happened after the last rejection
                        $lastSubmissionAfterRejection = $history;
                    }
                }
                
                $requester = auth()->user()->id === $record->requester_id;
                
                // Show resubmit button only if there was a rejection, no submission after that rejection,
                // and the current user is the requester
                return ($lastRejection !== null && $lastSubmissionAfterRejection === null) && $requester;
            })
            ->modalHeading('Resubmit Request')
            ->modalDescription('Edit the information below if needed, then resubmit for approval.')
            ->modalSubmitActionLabel('Resubmit for Approval')
            ->action(function (array $data, Model $record): void {
                // Update the record with form data
                $record->update($data);

                $user = Auth::user();
                $approval = $record->approval;

                if (! $approval) {
                    throw new \Exception('No approval record found for this request.');
                }

                // Resubmit via service
                $approvalService = new ApprovalService();
                $approvalService->resubmitApproval($approval, $user);

                // Optional: Add notification or redirect
                // notification()->success('Request resubmitted successfully.');
            });
    }
}