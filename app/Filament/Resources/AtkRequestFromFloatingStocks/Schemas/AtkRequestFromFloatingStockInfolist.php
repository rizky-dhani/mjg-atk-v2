<?php

namespace App\Filament\Resources\AtkRequestFromFloatingStocks\Schemas;

use App\Models\User;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AtkRequestFromFloatingStockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Floating Stock Request Detail')
                ->schema([
                    TextEntry::make('request_number')->label('Request Number'),
                    TextEntry::make('requester.name')->label('Requester Name'),
                    TextEntry::make('division.name')->label('Division Name'),
                    TextEntry::make('created_at')->label('Created At')->dateTime(),
                ])
                ->columns(3)
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
        ]);
    }
}
