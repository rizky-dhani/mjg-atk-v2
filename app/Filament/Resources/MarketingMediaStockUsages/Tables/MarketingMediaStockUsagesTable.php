<?php

namespace App\Filament\Resources\MarketingMediaStockUsages\Tables;

use App\Filament\Actions\ApprovalAction;
use App\Filament\Actions\ResubmitAction;
use App\Filament\Resources\MarketingMediaStockUsages\Schemas\MarketingMediaStockUsageForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MarketingMediaStockUsagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->with(['requester', 'division'])
                    ->when(! auth()->user()->isSuperAdmin(), fn ($q) => $q->whereIn('division_id', auth()->user()->divisions->pluck('id')))
                    ->orderByDesc('created_at'),
            )
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
                TextColumn::make('approval.status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        $approval = $record->approval;
                        if (! $approval) {
                            return 'Pending';
                        }

                        // Get the latest approval step approval
                        $latestApproval = $approval
                            ->approvalStepApprovals()
                            ->with(['user.divisions', 'step.division'])
                            ->latest('approved_at')
                            ->first();

                        if ($latestApproval) {
                            $status = ucfirst($latestApproval->status);
                            $user = $latestApproval->user;
                            $step = $latestApproval->step;
                            $division = $step?->division ?? $user?->divisions->first();

                            if ($user && $division) {
                                // Get division's initial and user's role
                                $divisionInitial = $division->initial ?? 'N/A';
                                $role = $user->roles->first()->name ?? 'N/A';

                                return "{$status} by {$divisionInitial} {$role}";
                            } else {
                                return $status;
                            }
                        }

                        return $approval->status
                            ? ucfirst($approval->status)
                            : 'Pending';
                    })
                    ->color(
                        fn (string $state): string => match (true) {
                            str_contains($state, 'approved') => 'success',
                            str_contains($state, 'rejected') => 'danger',
                            default => 'warning',
                        },
                    ),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->authorize(static function ($record) {
                    $user = auth()->user();

                    return $user && $user->id === $record->requester_id;
                }),
                ApprovalAction::makeApprove()->successNotification(
                    Notification::make()
                        ->title('Penggunaan Marketing Media berhasil disetujui!')
                        ->success(),
                ),
                ApprovalAction::makeReject()->successNotification(
                    Notification::make()
                        ->title('Penggunaan Marketing Media berhasil ditolak!')
                        ->success(),
                ),
                ResubmitAction::make()
                    // Use mountUsing() to fill the form with the record's attributes
                    ->mountUsing(
                        fn (Schema $schema, $record) => $schema->fill(
                            $record->toArray(),
                        ),
                    )
                    ->form(
                        fn (Schema $schema) => MarketingMediaStockUsageForm::configure(
                            $schema,
                        ),
                    ),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
