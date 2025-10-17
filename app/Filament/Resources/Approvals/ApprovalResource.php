<?php

namespace App\Filament\Resources\Approvals;

use App\Filament\Resources\Approvals\Pages\ManageApprovals;
use App\Models\Approval;
use BackedEnum;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApprovalResource extends Resource
{
    protected static ?string $model = Approval::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::CheckCircle;
    protected static string | UnitEnum | null $navigationGroup = 'Approval Management';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('approvable_type')
                    ->required(),
                TextInput::make('approvable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('flow_id')
                    ->required()
                    ->numeric(),
                TextInput::make('current_step')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('approvable_type')
                    ->searchable(),
                TextColumn::make('approvable_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('flow_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('current_step')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageApprovals::route('/'),
        ];
    }
}
