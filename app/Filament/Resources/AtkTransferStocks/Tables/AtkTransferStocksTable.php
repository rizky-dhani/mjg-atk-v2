<?php

namespace App\Filament\Resources\AtkTransferStocks\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class AtkTransferStocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function(Builder $query){
                $query->where('requesting_division_id', auth()->user()->division_id)->orderByDesc('created_at');
            })
            ->columns([
                TextColumn::make('transfer_number')
                    ->label('Nomor Transfer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('requester.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('requestingDivision.name')
                    ->label('Divisi Pemohon')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sourceDivision.name')
                    ->label('Divisi Sumber')
                    ->placeholder('Belum dipilih'),
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
                    )
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Tanggal Diupdate')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'completed' => 'Selesai',
                    ]),
                SelectFilter::make('requesting_division_id')
                    ->label('Divisi Pemohon')
                    ->relationship('requestingDivision', 'name'),
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from'),
                        \Filament\Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->modalWidth(Width::SevenExtraLarge)
                    ->visible(function ($record) {
                        $user = \Illuminate\Support\Facades\Auth::user();
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
                        
                        return $canEdit;
                    }),
                \Filament\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Transfer Stock')
                    ->modalDescription('Are you sure you want to approve this transfer stock request?')
                    ->action(function ($record) {
                        $approvalService = new \App\Services\TransferStockApprovalService();
                        
                        if ($approvalService->canApprove($record)) {
                            if ($approvalService->approve($record)) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Success')
                                    ->body('Transfer stock request has been approved successfully.')
                                    ->success()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Error')
                                    ->body('Failed to approve transfer stock request.')
                                    ->danger()
                                    ->send();
                            }
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Akses Ditolak')
                                ->body('Anda tidak memiliki hak untuk menyetujui langkah ini.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(function ($record) {
                        $approvalService = new \App\Services\TransferStockApprovalService();
                        return $approvalService->canApprove($record);
                    }),
                \Filament\Actions\Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Transfer Stock')
                    ->modalDescription('Are you sure you want to reject this transfer stock request?')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason for Rejection')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $approvalService = new \App\Services\TransferStockApprovalService();
                        
                        if ($approvalService->canApprove($record)) {
                            if ($approvalService->reject($record, $data['rejection_reason'])) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Success')
                                    ->body('Transfer stock request has been rejected successfully.')
                                    ->success()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Error')
                                    ->body('Failed to reject transfer stock request.')
                                    ->danger()
                                    ->send();
                            }
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Akses Ditolak')
                                ->body('Anda tidak memiliki hak untuk menolak langkah ini.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(function ($record) {
                        $approvalService = new \App\Services\TransferStockApprovalService();
                        return $approvalService->canApprove($record);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
