<?php

namespace App\Filament\Resources\AtkTransferStocks\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AtkTransferStockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Transfer Stok')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('transfer_number')
                            ->label('Nomor Transfer'),
                        TextEntry::make('requester.name')
                            ->label('Pemohon'),
                        TextEntry::make('requestingDivision.name')
                            ->label('Divisi Pemohon'),
                        TextEntry::make('source_divisions_unique_count')
                            ->label('Divisi Sumber')
                            ->placeholder('Belum dipilih')
                            ->formatStateUsing(function ($record) {
                                // Get unique source division IDs from transfer stock items
                                $uniqueSourceDivisionIds = $record->transferStockItems
                                    ->pluck('source_division_id')
                                    ->unique()
                                    ->toArray();
                                
                                if (empty($uniqueSourceDivisionIds)) {
                                    return 'Belum dipilih';
                                } elseif (count($uniqueSourceDivisionIds) === 1) {
                                    // Return the name of the single source division
                                    $division = \App\Models\UserDivision::find($uniqueSourceDivisionIds[0]);
                                    return $division ? $division->name : $uniqueSourceDivisionIds[0]; // Show ID as fallback
                                } else {
                                    return count($uniqueSourceDivisionIds) . ' Divisi';
                                }
                            }),
                        TextEntry::make('approval_status')
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
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('-'),
                    ])
                    ->columns(3),
            ]);
    }
}