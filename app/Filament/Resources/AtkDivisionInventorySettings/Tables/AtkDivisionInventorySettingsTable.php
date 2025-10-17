<?php

namespace App\Filament\Resources\AtkDivisionInventorySettings\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Models\AtkDivisionInventorySetting;

class AtkDivisionInventorySettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function(Builder $query){
                if(auth()->user()->hasRole('Super Admin')){
                    AtkDivisionInventorySetting::all();
                }else{
                    $query->where('division_id', auth()->user()->division_id);

                }
            })
            ->columns([
                TextColumn::make('item.name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('item.category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('max_limit')
                    ->label('Max')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('current_stock')
                    ->label('Current')
                    ->getStateUsing(function ($record) {
                        $stock = AtkDivisionInventorySetting::where('division_id', $record->division_id)
                            ->where('item_id', $record->item_id)
                            ->first();
                        return $stock ? $stock->current_stock : 0;
                    })
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_status')
                    ->label('Stock Status')
                    ->getStateUsing(function ($record) {
                        $stock = AtkDivisionInventorySetting::where('division_id', $record->division_id)
                            ->where('item_id', $record->item_id)
                            ->first();
                        
                        if (!$stock) {
                            return 'No stock record';
                        }
                        
                        if ($stock->current_stock > $record->max_limit) {
                            return 'Over limit';
                        } elseif ($stock->current_stock == $record->max_limit) {
                            return 'At limit';
                        } elseif ($stock->current_stock < $record->max_limit) {
                            return 'Within limit';
                        } else {
                            return 'Empty';
                        }
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Over limit' => 'danger',
                        'At limit' => 'warning',
                        'Within limit' => 'success',
                        'Empty' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('set_max_limit')
                        ->label('Set Max')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->form([
                            TextInput::make('max_limit')
                                ->label('Maximum Limit')
                                ->numeric()
                                ->required()
                                ->minValue(0),
                        ])
                        ->action(function (array $data, $records) {
                            foreach ($records as $record) {
                                $record->update(['max_limit' => $data['max_limit']]);
                            }
                            
                            Notification::make()
                                ->title('Maximum limit updated successfully for selected items')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                Action::make('set_global_max_limit')
                    ->label('Set Global Max')
                    ->icon('heroicon-o-globe-alt')
                    ->form([
                        TextInput::make('max_limit')
                            ->label('Maximum Limit')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                    ])
                    ->action(function (array $data) {
                        // Update all inventory settings for the current division
                        AtkDivisionInventorySetting::where('division_id', auth()->user()->division_id)
                            ->update(['max_limit' => $data['max_limit']]);
                        
                        Notification::make()
                            ->title('Global maximum limit updated successfully')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
