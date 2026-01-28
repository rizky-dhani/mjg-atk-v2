<?php

namespace App\Filament\Resources\MarketingMediaStockRequests\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MarketingMediaStockRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // 1. Stock Request Detail section
            Section::make('Stock Request Detail')
                ->schema([
                    TextEntry::make('request_number')->label('Request Number'),
                    TextEntry::make('requester.name')->label('Requester Name'),
                    TextEntry::make('division.name')->label('Division Name'),
                ])
                ->columns(3)
                ->columnSpanFull(),
            // 2. Stock Request Approval section
            Section::make('Stock Request Approval')
                ->schema([
                    RepeatableEntry::make('approvalHistory')
                        ->table([
                            TableColumn::make('Approver'),
                            TableColumn::make('Approval Type'),
                            TableColumn::make('Performed At'),
                            TableColumn::make('Rejection Reason'),
                        ])
                        ->schema([
                            TextEntry::make('user.name')
                                ->label('Approver')
                                ->formatStateUsing(function ($record) {
                                    $user = $record?->user;
                                    $step = $record?->step;
                                    $division = $step?->division ?? $user?->divisions->first();

                                    if ($division) {
                                        $divisionInitial =
                                            $division->initial ?? 'N/A'; // Use division initial
                                        $approverName =
                                            $user?->name ?? 'Unknown';

                                        return "{$divisionInitial} {$approverName}";
                                    } else {
                                        $approverName =
                                            $user?->name ?? 'Unknown';

                                        return $approverName;
                                    }
                                }),
                            TextEntry::make('action')
                                ->label('Approval Type')
                                ->badge()
                                ->formatStateUsing(function ($state) {
                                    return ucfirst($state);
                                })
                                ->color(function ($state) {
                                    return match ($state) {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    };
                                }),
                            TextEntry::make('performed_at')
                                // ->label(function ($state, $get) {
                                //     $action = $get('action');

                                //     return $action === 'approved'
                                //         ? 'Approved At'
                                //         : ($action === 'rejected'
                                //             ? 'Rejected At'
                                //             : 'Performed At');
                                // })
                                ->dateTime(),
                            TextEntry::make('rejection_reason')
                                // ->label('Rejection Reason')
                                ->hidden(function ($get) {
                                    $action = $get('action');

                                    return $action !== 'rejected';
                                }),
                        ])
                        ->state(function ($record) {
                            if ($record) {
                                return $record
                                    ->approvalHistory()
                                    ->with(['user.divisions', 'step.division']) // Include division information for the user and step
                                    ->whereIn('action', [
                                        'approved',
                                        'rejected',
                                    ]) // Only show approved and rejected
                                    ->orderByDesc('performed_at')
                                    ->get();
                            }

                            return collect();
                        }),
                ])
                ->columnSpanFull(),
        ]);
    }
}
