<?php

namespace App\Filament\Resources\AtkStockUsages\Schemas;

use App\Models\User;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AtkStockUsageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Stock Usage Detail')
                ->schema([
                    TextEntry::make('request_number')->label('Request Number')->placeholder('-'),
                    TextEntry::make('requester.name')->label('Requester')->placeholder('-'),
                    TextEntry::make('division.name')->label('Division')->placeholder('-'),
                    TextEntry::make('created_at')->label('Created At')->dateTime()->placeholder('-'),
                ])
                ->columns(3)
                ->columnSpanFull(),

            Section::make('Rejection Details')
                ->visible(function ($record) {
                    if (! $record) {
                        return false;
                    }

                    return $record->approvalHistory()
                        ->where('action', 'rejected')
                        ->exists();
                })
                ->schema([
                    TextEntry::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->getStateUsing(function ($record) {
                            $rejection = $record->approvalHistory()
                                ->where('action', 'rejected')
                                ->latest('performed_at')
                                ->first();

                            return $rejection?->rejection_reason;
                        }),
                    TextEntry::make('rejector_name')
                        ->label('Rejected By')
                        ->getStateUsing(function ($record) {
                            $rejection = $record->approvalHistory()
                                ->where('action', 'rejected')
                                ->latest('performed_at')
                                ->first();

                            return $rejection?->user?->name;
                        }),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Approval Progress')
                ->schema([
                    RepeatableEntry::make('approvalProgress')
                        ->label('Expected Approvers & Progress')
                        ->columns(4)
                        ->schema([
                            TextEntry::make('potential_approvers')
                                ->label('Approver(s)')
                                ->formatStateUsing(function ($state, $record) {
                                    if ($record['status'] === 'approved' || $record['status'] === 'rejected') {
                                        return $record['approver_name'] ?? 'Unknown';
                                    }

                                    $isEmpty = is_array($state) ? empty($state) : (method_exists($state, 'isEmpty') ? $state->isEmpty() : ! $state);

                                    if ($isEmpty) {
                                        return 'No approvers found matching role/division';
                                    }

                                    if (method_exists($state, 'map')) {
                                        return $state->map(function ($user) {
                                            $initials = $user->divisions->pluck('initial')->implode(',');
                                            $divisionInitial = $initials ? "[{$initials}] " : '';

                                            return "{$divisionInitial}{$user->name}";
                                        })->implode(', ');
                                    }

                                    if ($state instanceof User) {
                                        $initials = $state->divisions->pluck('initial')->implode(',');
                                        $divisionInitial = $initials ? "[{$initials}] " : '';

                                        return "{$divisionInitial}{$state->name}";
                                    }

                                    return $state;
                                }),
                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->formatStateUsing(fn ($state) => ucfirst($state))
                                ->color(fn ($state) => match ($state) {
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'pending' => 'warning',
                                    'blocked' => 'gray',
                                    default => 'gray',
                                }),
                            TextEntry::make('approved_at')
                                ->label('Approved/Rejected At')
                                ->dateTime(),
                            TextEntry::make('notes')
                                ->label('Rejection Reason')
                                ->hidden(fn ($record) => $record['status'] !== 'rejected'),
                        ])
                        ->state(function ($record) {
                            return $record->approval?->getApprovalProgress() ?? collect();
                        })
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            Section::make('Stock Usage Items')
                ->columnSpanFull()
                ->schema([
                    RepeatableEntry::make('atkStockUsageItems')
                        ->label('Items Used')
                        ->columns(4)
                        ->schema([
                            TextEntry::make('category.name')->label('Category')->placeholder('-'),
                            TextEntry::make('item.name')->label('Item')->placeholder('-'),
                            TextEntry::make('quantity')->label('Quantity')->numeric()->placeholder('-'),
                            TextEntry::make('moving_average_cost')
                                ->label('Average Cost')
                                ->money('IDR')
                                ->placeholder('-'),
                        ]),
                ]),

            Section::make('Budget Summary')
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('potential_cost')
                        ->label('Total Potential Cost')
                        ->money('IDR')
                        ->placeholder('-'),
                ])
                ->columns(1),
        ]);
    }
}
