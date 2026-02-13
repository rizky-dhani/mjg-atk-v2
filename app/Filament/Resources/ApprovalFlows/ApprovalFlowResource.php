<?php

namespace App\Filament\Resources\ApprovalFlows;

use App\Filament\Actions\DuplicateAction;
use App\Filament\Resources\ApprovalFlows\Pages\ManageApprovalFlows;
use App\Filament\Resources\ApprovalFlows\Pages\ViewApprovalFlow;
use App\Models\ApprovalFlow;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\File;

class ApprovalFlowResource extends Resource
{
    protected static ?string $model = ApprovalFlow::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::NumberedList;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.group.approval_management');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->default(null)
                    ->maxLength(6535)
                    ->columnSpanFull(),
                Grid::make(2)
                    ->schema([
                        Select::make('model_type')
                            ->options(self::getModelOptions())
                            ->required()
                            ->searchable()
                            ->helperText('Select the model type that this approval flow applies to'),
                        Select::make('division_id')
                            ->label('Division')
                            ->options(fn () => \App\Models\UserDivision::pluck('name', 'id'))
                            ->nullable()
                            ->searchable()
                            ->helperText('Leave empty for global flow (applies to all divisions)'),
                    ]),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('model_type')
                    ->searchable(),
                TextColumn::make('division.name')
                    ->label('Division')
                    ->searchable()
                    ->placeholder('Global (All Divisions)'),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->successNotificationTitle('Approval flow updated'),
                DeleteAction::make()
                    ->successNotificationTitle('Approval flow deleted'),
                DuplicateAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotificationTitle('Approval flows deleted'),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Approval Flow')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('model_type'),
                                TextEntry::make('division.name')
                                    ->label('Division')
                                    ->placeholder('Global (All Divisions)'),
                                IconEntry::make('is_active')
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->trueColor('success')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->falseColor('danger'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageApprovalFlows::route('/'),
            'view' => ViewApprovalFlow::route('/view/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ApprovalFlowStepsRelationManager::class,
        ];
    }

    protected static function getModelOptions(): array
    {
        $modelPath = app_path('Models');
        $files = File::allFiles($modelPath);
        $options = [];

        foreach ($files as $file) {
            $fileName = $file->getFilename();
            $modelName = basename($fileName, '.php');
            $modelClass = "App\\Models\\{$modelName}";

            // Check if it's a valid model class
            if (class_exists($modelClass) && is_subclass_of($modelClass, \Illuminate\Database\Eloquent\Model::class)) {
                $options[$modelClass] = $modelName;
            }
        }

        return $options;
    }
}
