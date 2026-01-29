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
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
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
                    ->required()
                    ->live(),
                Select::make('division_id')
                    ->label('Division')
                    ->relationship('division', 'name')
                    ->nullable()
                    ->helperText('Leave empty to make this step available to all divisions')
                    ->live(),
                Select::make('user_id')
                    ->label('Specific User')
                    ->options(function ($get) {
                        $roleId = $get('role_id');
                        $divisionId = $get('division_id');

                        $query = \App\Models\User::query();

                        if ($roleId) {
                            $query->whereHas('roles', fn ($q) => $q->where('roles.id', $roleId));
                        }

                        if ($divisionId) {
                            $query->whereHas('divisions', fn ($q) => $q->where('user_divisions.id', $divisionId));
                        }

                        return $query->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Select a specific user to pin to this step. If left empty, any user with the matching role and division can approve.')
                    ->columnSpanFull(),
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
                TextColumn::make('user.name')
                    ->label('Specific User')
                    ->sortable()
                    ->placeholder('Any'),
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
