<?php

namespace App\Filament\Resources\AtkRequestFromFloatingStocks\Schemas;

use App\Models\AtkFloatingStock;
use App\Models\AtkItem;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AtkRequestFromFloatingStockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rejection Details')
                    ->visible(function ($record) {
                        if (! $record) {
                            return false;
                        }

                        return \App\Models\ApprovalHistory::where('approvable_type', get_class($record))
                            ->where('approvable_id', $record->id)
                            ->where('action', 'rejected')
                            ->exists();
                    })
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('rejection_reason')
                                    ->label('Rejection Reason')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function ($record) {
                                        if (! $record) {
                                            return null;
                                        }
                                        $rejection = \App\Models\ApprovalHistory::where('approvable_type', get_class($record))
                                            ->where('approvable_id', $record->id)
                                            ->where('action', 'rejected')
                                            ->latest('performed_at')
                                            ->first();

                                        return $rejection ? $rejection->rejection_reason : null;
                                    }),
                                TextInput::make('rejector_name')
                                    ->label('Rejected By')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function ($record) {
                                        if (! $record) {
                                            return null;
                                        }
                                        $rejection = \App\Models\ApprovalHistory::where('approvable_type', get_class($record))
                                            ->where('approvable_id', $record->id)
                                            ->where('action', 'rejected')
                                            ->latest('performed_at')
                                            ->first();

                                        return $rejection ? $rejection->user->name : null;
                                    }),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Request Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('request_number')
                                    ->label('Request Number')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('Auto-generated'),
                                Textarea::make('notes')
                                    ->label('Notes')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Requested Items')
                    ->schema([
                        Repeater::make('atkRequestFromFloatingStockItems')
                            ->relationship()
                            ->schema([
                                Select::make('item_id')
                                    ->label('Item')
                                    ->options(fn () => AtkItem::whereHas('atkFloatingStock')->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        if ($state) {
                                            $floatingStock = AtkFloatingStock::where('item_id', $state)->first();
                                            $set('available_stock', $floatingStock?->current_stock ?? 0);
                                        }
                                    }),
                                TextInput::make('available_stock')
                                    ->label('Available in Floating Stock')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->numeric(),
                                TextInput::make('quantity')
                                    ->label('Quantity Requested')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(fn ($get) => $get('available_stock') ?? 1000)
                                    ->reactive()
                                    ->live()
                                    ->afterStateUpdated(function ($get, $set, $state) {
                                        $available = $get('available_stock') ?? 0;
                                        if ($state > $available) {
                                            $set('quantity', $available);
                                            Notification::make()
                                                ->title('Quantity exceeds available stock')
                                                ->body("Maximum available stock is {$available}")
                                                ->warning()
                                                ->send();
                                        }
                                    }),
                            ])
                            ->columns(3)
                            ->minItems(1)
                            ->addActionLabel('Add Item'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
