<?php

namespace App\Filament\Resources\AtkBudgetings\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class AtkBudgetingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Budget Details')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Budget ID'),
                        TextEntry::make('division.name')
                            ->label('Division'),
                        TextEntry::make('budget_amount')
                            ->label('Total Budget')
                            ->money('IDR'),
                        TextEntry::make('used_amount')
                            ->label('Used Amount')
                            ->money('IDR'),
                        TextEntry::make('remaining_amount')
                            ->label('Remaining Amount')
                            ->money('IDR'),
                        TextEntry::make('fiscal_year')
                            ->label('Fiscal Year'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}