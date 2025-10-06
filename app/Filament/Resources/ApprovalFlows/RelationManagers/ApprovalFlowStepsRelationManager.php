<?php

namespace App\Filament\Resources\ApprovalFlows\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApprovalFlowStepsRelationManager extends RelationManager
{
    protected static string $relationship = 'approvalFlowSteps';
    public function isReadOnly(): bool
    {
        return false;
    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('step_number')
                    ->required()
                    ->numeric(),
                TextInput::make('role_id')
                    ->required()
                    ->numeric(),
                Select::make('division_id')
                    ->relationship('division', 'name'),
                TextInput::make('description')
                    ->default(null),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('step_number')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('role_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('division_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
