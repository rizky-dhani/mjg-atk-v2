<?php

namespace App\Filament\Resources\ApprovalFlows;

use App\Filament\Resources\ApprovalFlows\Pages\ViewApprovalFlow;
use BackedEnum;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use UnitEnum;
use Filament\Forms;
use Filament\Infolists\Components\IconEntry;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\ApprovalFlow;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\File;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\ApprovalFlows\Pages\ManageApprovalFlows;

class ApprovalFlowResource extends Resource
{
    protected static ?string $model = ApprovalFlow::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::NumberedList;
    protected static string | UnitEnum | null $navigationGroup = 'Approval Management';
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
                Select::make('model_type')
                    ->options(self::getModelOptions())
                    ->required()
                    ->searchable()
                    ->helperText('Select the model type that this approval flow applies to'),
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
                IconColumn::make('is_active')
                    ->boolean(),
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
                ]),
            ]);
    }
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Approval Flow')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('model_type'),
                                IconEntry::make('is_active')
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->trueColor('success')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->falseColor('danger')
                            ])
                    ])
                    ->columnSpanFull()
            ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => ManageApprovalFlows::route('/'),
            'view' => ViewApprovalFlow::route('/view/{record}')
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
