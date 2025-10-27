<?php

namespace App\Filament\Resources\AtkStockUsages\Tables;

use App\Filament\Actions\ApprovalAction;
use App\Filament\Actions\ResubmitAction;
use App\Filament\Resources\AtkStockUsages\Schemas\AtkStockUsageForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AtkStockUsagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->with(['requester', 'division'])
                    ->where('division_id', auth()->user()->division_id)
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
                TextColumn::make('approval_status')
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
                            ->with(['user', 'user.division'])
                            ->latest('approved_at')
                            ->first();

                        if ($latestApproval) {
                            $status = ucfirst($latestApproval->status);

                            if ($latestApproval->user && $latestApproval->user->division) {
                                // Get division's initial and user's first role name
                                $divisionInitial = $latestApproval->user->division->initial ?? 'N/A';
                                $roleNames = $latestApproval->user->getRoleNames();
                                $role = $roleNames->first() ?? 'N/A';

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
                        ->title('Penggunaan ATK berhasil disetujui!')
                        ->success(),
                ),
                ApprovalAction::makeReject()->successNotification(
                    Notification::make()
                        ->title('Penggunaan ATK berhasil ditolak!')
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
                        fn (Schema $schema) => AtkStockUsageForm::configure(
                            $schema,
                        ),
                    ),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
