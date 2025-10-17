<?php

namespace App\Filament\Actions;

use Filament\Actions\Action as BaseAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\File;

class GenerateModelPermissionsAction
{
    public static function make(): BaseAction
    {
        return BaseAction::make('generate_model_permissions')
            ->label('Generate Model Permissions')
            ->icon('heroicon-o-cog-6-tooth')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Generate Model Permissions')
            ->modalDescription('This will create ViewAny, View, Create, Edit, and Delete permissions for all models.')
            ->modalSubmitActionLabel('Generate Permissions')
            ->action(function (): void {
                self::generatePermissions();
            });
    }

    public static function generatePermissions(): void
    {
        // Dynamically get all PHP files from the app/Models directory
        $modelPath = app_path('Models');
        $files = File::glob($modelPath . '/*.php');
        
        $models = [];
        foreach ($files as $file) {
            $fileName = basename($file, '.php');
            // Skip files that are not models (e.g., interfaces, traits)
            if ($fileName !== 'Model') { // Assuming there's a base Model class
                $models[] = $fileName;
            }
        }

        $generatedCount = 0;
        $existingCount = 0;

        foreach ($models as $model) {
            $modelPermissions = [
                'viewAny' => 'view-any ' . Str::kebab($model),
                'view' => 'view ' . Str::kebab($model),
                'create' => 'create ' . Str::kebab($model),
                'edit' => 'edit ' . Str::kebab($model),
                'delete' => 'delete ' . Str::kebab($model),
            ];

            foreach ($modelPermissions as $type => $permissionName) {
                if (!Permission::where('name', $permissionName)->exists()) {
                    Permission::create(['name' => $permissionName]);
                    $generatedCount++;
                } else {
                    $existingCount++;
                }
            }
        }

        Notification::make()
            ->title('Permissions Generated')
            ->body("Successfully created {$generatedCount} new permissions. {$existingCount} permissions already existed.")
            ->success()
            ->send();
    }
}
