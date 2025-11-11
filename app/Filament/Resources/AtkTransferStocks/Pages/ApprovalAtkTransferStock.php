<?php

namespace App\Filament\Resources\AtkTransferStocks\Pages;

use UnitEnum;
use BackedEnum;
use Filament\Tables;
use App\Models\Approval;
use Filament\Tables\Table;
use App\Models\ApprovalFlow;
use App\Models\UserDivision;
use Filament\Actions\Action;
use App\Models\ApprovalFlowStep;
use App\Models\AtkTransferStock;
use Filament\Resources\Pages\Page;
use App\Models\ApprovalStepApproval;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use App\Filament\Actions\ApprovalAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AtkTransferStocks\AtkTransferStockResource;

class ApprovalAtkTransferStock extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = AtkTransferStockResource::class;

    protected static ?string $slug = 'atk/transfer-stocks/approval';

    protected static ?string $navigationLabel = 'Transfer Stok ATK';

    protected static string|UnitEnum|null $navigationGroup = 'Approval Permintaan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowsRightLeft;

    protected static ?string $title = 'Transfer Stok ATK';

    public function table(Table $table): Table
    {
        return $table
            ->query(
            AtkTransferStock::query()
                    ->whereHas('approval', function (Builder $query) {
                        $user = Auth::user();
                        
                        // Get approval steps that the current user can approve
                        $query->whereHas('approvalFlow.approvalFlowSteps', function (Builder $stepQuery) use ($user) {
                            // Filter by the roles the user has (using Spatie roles)
                            $userRoleNames = $user->roles->pluck('name');
                            $stepQuery->whereHas('role', function (Builder $roleQuery) use ($userRoleNames) {
                                $roleQuery->whereIn('name', $userRoleNames);
                            });
                            
                            // If the step has a specific division, check if the user belongs to that division
                            if ($user->division_id) {
                                $stepQuery->where(function (Builder $subQuery) use ($user) {
                                    $subQuery->where('division_id', $user->division_id)
                                            ->orWhere(function (Builder $subSubQuery) use ($user) {
                                                // For steps with null division_id (like Source Division Head), 
                                                // check if user's division matches any of the item's source divisions
                                                $subSubQuery->whereNull('division_id')
                                                            ->whereHas('approvable.transferStockItems', function (Builder $itemQuery) use ($user) {
                                                                $itemQuery->where('source_division_id', $user->division_id);
                                                            });
                                            });
                                });
                            }
                        })
                        ->where(function (Builder $subQuery) {
                            $subQuery->where('approvals.status', 'pending')
                                    ->orWhere('approvals.status', 'partially_approved');
                        });
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('transfer_number')
                    ->label('Nomor Transfer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requestingDivision.name')
                    ->label('Divisi Pemohon')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sourceDivisionsCount')
                    ->label('Divisi Sumber')
                    ->placeholder('Belum dipilih')
                    ->formatStateUsing(function ($record) {
                        $sourceDivisions = $record->sourceDivisions;
                        if ($sourceDivisions->count() === 0) {
                            return 'Belum dipilih';
                        } elseif ($sourceDivisions->count() === 1) {
                            return $sourceDivisions->first()->name;
                        } else {
                            return $sourceDivisions->count() . ' Divisi';
                        }
                    }),
                Tables\Columns\TextColumn::make('approval_status')
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->action(function (AtkTransferStock $record) {
                        $approvalService = new \App\Services\TransferStockApprovalService();
                        
                        if ($approvalService->canApprove($record)) {
                            if ($approvalService->approve($record)) {
                                Notification::make()
                                    ->title('Berhasil')
                                    ->body('Permintaan transfer stok berhasil disetujui.')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Gagal menyetujui permintaan transfer stok.')
                                    ->danger()
                                    ->send();
                            }
                        } else {
                            Notification::make()
                                ->title('Akses Ditolak')
                                ->body('Anda tidak memiliki hak untuk menyetujui langkah ini.')
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Permintaan Transfer Stok')
                    ->modalDescription('Apakah Anda yakin ingin menolak permintaan transfer stok ini?')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function (AtkTransferStock $record, array $data) {
                        $approvalService = new \App\Services\TransferStockApprovalService();
                        
                        if ($approvalService->canApprove($record)) {
                            if ($approvalService->reject($record, $data['rejection_reason'])) {
                                Notification::make()
                                    ->title('Berhasil')
                                    ->body('Permintaan transfer stok berhasil ditolak.')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Gagal menolak permintaan transfer stok.')
                                    ->danger()
                                    ->send();
                            }
                        } else {
                            Notification::make()
                                ->title('Akses Ditolak')
                                ->body('Anda tidak memiliki hak untuk menolak langkah ini.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}