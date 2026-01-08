<?php

namespace App\Filament\Resources\ApprovalFlows\RelationManagers;

use Filament\Tables\Table;
use App\Models\UserDivision;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Actions\DissociateBulkAction;
use Filament\Resources\RelationManagers\RelationManager;

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
            ->columns(2)
            ->components([
                TextInput::make('step_name')
                    ->label('Step Name')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('step_number')
                    ->required()
                    ->numeric(),
                Select::make('role_id')
                    ->label('Role')
                    ->relationship('role', 'name')
                    ->required(),
                Select::make('division_id')
                    ->label('Division')
                    ->relationship('division', 'name')
                    ->columnSpanFull()
                    ->nullable()
                    ->helperText('Leave empty to make this step available to all divisions'),
                TextInput::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('allow_resubmission')
                    ->label('Allow Resubmission')
                    ->helperText('Allow this step to resubmit the approval after rejection')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('step_name')
                    ->label('Step Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('step_number')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('role.name')
                    ->label('Role')
                    ->sortable(),
                TextColumn::make('division.name')
                    ->label('Division')
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable(),
                IconColumn::make('allow_resubmission')
                    ->label('Resubmission')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->successNotificationTitle('Approval flow step created'),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->successNotificationTitle('Approval flow step updated'),
                DissociateAction::make(),
                DeleteAction::make()
                    ->successNotificationTitle('Approval flow step deleted'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make()
                        ->successNotificationTitle('Approval flow steps deleted'),
                ]),
            ]);
    }
}
