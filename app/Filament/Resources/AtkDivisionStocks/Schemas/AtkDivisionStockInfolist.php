<?php

namespace App\Filament\Resources\AtkDivisionStocks\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AtkDivisionStockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ATK Stock Inventory Details')
                    ->schema([
                        TextEntry::make('division.name')
                            ->label('Division')
                            ->placeholder('-'),
                        TextEntry::make('item.name')
                            ->label('Item')
                            ->placeholder('-'),
                        TextEntry::make('category.name')
                            ->label('Category')
                            ->placeholder('-'),
                        TextEntry::make('current_stock')
                            ->label('Current Stock')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('moving_average_cost')
                            ->label('Average Cost')
                            ->money('IDR')
                            ->placeholder('-'),
                        TextEntry::make('setting_max_limit')
                            ->label('Max Stock Limit')
                            ->getStateUsing(function ($record) {
                                $setting = $record->getSetting();
                                return $setting ? $setting->max_limit : 'N/A';
                            })
                            ->numeric()
                            ->placeholder('N/A'),
                        TextEntry::make('stock_status')
                            ->label('Stock Status')
                            ->getStateUsing(function ($record) {
                                $currentStock = $record->current_stock;
                                $setting = $record->getSetting();
                                $maxLimit = $setting ? $setting->max_limit : 0;

                                if ($maxLimit == 0) {
                                    return 'No Limit Set';
                                } elseif ($currentStock >= $maxLimit) {
                                    return 'At Limit';
                                } elseif ($currentStock > $maxLimit * 0.8) {
                                    return 'High Stock';
                                } elseif ($currentStock == 0) {
                                    return 'Empty';
                                } else {
                                    return 'Within Limit';
                                }
                            })
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'At Limit', 'High Stock' => 'warning',
                                'Empty' => 'danger',
                                'No Limit Set' => 'info',
                                'Within Limit' => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('total_value')
                            ->label('Total Stock Value')
                            ->getStateUsing(function ($record) {
                                return $record->getTotalStockValue();
                            })
                            ->money('IDR')
                            ->placeholder('-'),
                    ])
                    ->columns(2),
                Section::make('System Information')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }
}
