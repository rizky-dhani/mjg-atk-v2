<?php

namespace App\Filament\Resources\AtkFulfillments\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AtkFulfillmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Permintaan')
                ->schema([
                    TextEntry::make('request_number')->label('Nomor Permintaan'),
                    TextEntry::make('requester.name')->label('Pemohon'),
                    TextEntry::make('division.name')->label('Divisi'),
                    TextEntry::make('fulfillment_status')
                        ->label('Status Pemenuhan')
                        ->badge()
                        ->formatStateUsing(fn (\App\Enums\FulfillmentStatus $state): string => $state->getLabel())
                        ->color(fn (\App\Enums\FulfillmentStatus $state): string => $state->getColor()),
                ])
                ->columns(4)
                ->columnSpanFull(),
                
            Section::make('Progress Approval')
                ->schema([
                    RepeatableEntry::make('approvalProgress')
                        ->label('Penyetuju & Progress')
                        ->columns(4)
                        ->schema([
                            TextEntry::make('potential_approvers')
                                ->label('Penyetuju')
                                ->formatStateUsing(function ($state, $record) {
                                    if ($record['status'] === 'approved' || $record['status'] === 'rejected') {
                                        return $record['approver_name'] ?? 'Unknown';
                                    }

                                    $isEmpty = is_array($state) ? empty($state) : (method_exists($state, 'isEmpty') ? $state->isEmpty() : ! $state);

                                    if ($isEmpty) {
                                        return 'Tidak ada penyetuju yang cocok';
                                    }

                                    if (method_exists($state, 'map')) {
                                        return $state->map(function ($user) {
                                            $divisionInitial = $user->division?->initial ? "[{$user->division->initial}] " : '';
                                            return "{$divisionInitial}{$user->name}";
                                        })->implode(', ');
                                    }

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
                                ->label('Waktu')
                                ->dateTime(),
                            TextEntry::make('notes')
                                ->label('Catatan Penolakan')
                                ->hidden(fn ($record) => $record['status'] !== 'rejected'),
                        ])
                        ->state(function ($record) {
                            return $record->approval?->getApprovalProgress() ?? collect();
                        })
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->collapsed(),
        ]);
    }
}