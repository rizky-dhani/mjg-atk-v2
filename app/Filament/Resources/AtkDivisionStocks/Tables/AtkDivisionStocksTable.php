<?php

namespace App\Filament\Resources\AtkDivisionStocks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AtkDivisionStocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query->where('division_id', auth()->user()->division_id)->orderBy('category_id')->orderBy('created_at'))
            ->columns([
                TextColumn::make('item.name')->numeric()->sortable(),
                TextColumn::make('category.name')->numeric()->sortable(),
                TextColumn::make('current_stock')->numeric()->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
