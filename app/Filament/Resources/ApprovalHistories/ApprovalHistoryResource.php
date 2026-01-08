<?php

namespace App\Filament\Resources\ApprovalHistories;

use App\Filament\Resources\ApprovalHistories\ApprovalHistoryResource\Pages;
use App\Filament\Resources\ApprovalHistories\ApprovalHistoryResource\RelationManagers;
use UnitEnum;
use BackedEnum;
use App\Models\ApprovalHistory;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApprovalHistoryResource extends Resource
{
    protected static ?string $model = ApprovalHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Clock;

    protected static string|UnitEnum|null $navigationGroup = 'Approval Management';

    protected static ?string $pluralModelLabel = 'Approval History';

    protected static ?string $modelLabel = 'Approval History';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('approvable_type')
                    ->label('Approvable Type')
                    ->options([
                        'App\Models\AtkStockRequest' => 'Stock Request',
                        'App\Models\AtkStockUsage' => 'Stock Usage',
                    ])
                    ->required(),
                
                Forms\Components\TextInput::make('approvable_id')
                    ->label('Approvable ID')
                    ->required()
                    ->numeric(),
                
                Forms\Components\TextInput::make('document_id')
                    ->label('Document ID')
                    ->maxLength(255),
                
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                
                Forms\Components\Select::make('action')
                    ->options([
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'returned' => 'Returned',
                    ])
                    ->required(),
                
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Rejection Reason')
                    ->rows(3)
                    ->maxLength(65535),
                
                Forms\Components\Textarea::make('notes')
                    ->rows(3)
                    ->maxLength(65535),
                
                Forms\Components\DateTimePicker::make('performed_at')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_id')
                    ->label('Document ID')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('approvable_type')
                    ->label('Document Type')
                    ->formatStateUsing(function ($state) {
                        $types = [
                            'App\Models\AtkStockRequest' => 'Stock Request',
                            'App\Models\AtkStockUsage' => 'Stock Usage',
                        ];
                        return $types[$state] ?? $state;
                    })
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('approvable_id')
                    ->label('Document #')
                    ->url(function ($record) {
                        $model = $record->approvable;
                        if ($model) {
                            $resourceClass = match(get_class($model)) {
                                'App\Models\AtkStockRequest' => \App\Filament\Resources\AtkStockRequests\AtkStockRequestResource::class,
                                'App\Models\AtkStockUsage' => \App\Filament\Resources\AtkStockUsages\AtkStockUsageResource::class,
                                default => null
                            };
                            
                            if ($resourceClass) {
                                return $resourceClass::getUrl('view', ['record' => $model]);
                            }
                        }
                        return null;
                    }, true)
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Approver')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        'submitted' => 'info',
                        'returned' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(function ($state) {
                        return ucfirst($state);
                    }),
                
                Tables\Columns\TextColumn::make('performed_at')
                    ->label('Date & Time')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'returned' => 'Returned',
                    ]),
                
                Tables\Filters\SelectFilter::make('approvable_type')
                    ->label('Document Type')
                    ->options([
                        'App\Models\AtkStockRequest' => 'Stock Request',
                        'App\Models\AtkStockUsage' => 'Stock Usage',
                    ]),
                
                Tables\Filters\Filter::make('performed_at')
                    ->form([
                        Forms\Components\DatePicker::make('performed_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('performed_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['performed_from'], function (Builder $query, $date): Builder {
                                return $query->whereDate('performed_at', '>=', $date);
                            })
                            ->when($data['performed_until'], function (Builder $query, $date): Builder {
                                return $query->whereDate('performed_at', '<=', $date);
                            });
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->successNotificationTitle('Approval History updated'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->successNotificationTitle('Approval Histories deleted'),
                ]),
            ])
            ->defaultSort('performed_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApprovalHistories::route('/'),
        ];
    }
}