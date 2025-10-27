<?php

namespace App\Filament\Resources\AtkDivisionStockSettings\Tables;

use App\Models\AtkDivisionStockSetting;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AtkDivisionStockSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (auth()->user()->hasRole('Super Admin')) {
                    AtkDivisionStockSetting::all();
                } else {
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
                    ->label('Max Stock Limit')
                    ->numeric()
                    ->sortable(),
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
                        // Update all stock settings for the current division
                        AtkDivisionStockSetting::where('division_id', auth()->user()->division_id)
                            ->update(['max_limit' => $data['max_limit']]);

                        Notification::make()
                            ->title('Global maximum limit updated successfully')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
