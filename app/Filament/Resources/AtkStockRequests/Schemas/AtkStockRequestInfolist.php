<?php

namespace App\Filament\Resources\AtkStockRequests\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AtkStockRequestInfolist
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
            // 2. Approval Progress section
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

                                    // Check if $state is a collection or array before calling isEmpty()
                                    $isEmpty = is_array($state) ? empty($state) : (method_exists($state, 'isEmpty') ? $state->isEmpty() : ! $state);

                                    if ($isEmpty) {
                                        return 'No approvers found matching role/division';
                                    }

                                    // If it's a collection, map it
                                    if (method_exists($state, 'map')) {
                                        return $state->map(function ($user) {
                                            $divisionInitial = $user->division?->initial ? "[{$user->division->initial}] " : '';

                                            return "{$divisionInitial}{$user->name}";
                                        })->implode(', ');
                                    }

                                    // If it's a single User object (Filament sometimes iterates)
                                    if ($state instanceof \App\Models\User) {
                                        $divisionInitial = $state->division?->initial ? "[{$state->division->initial}] " : '';

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
        ]);
    }
}
