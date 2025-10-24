# DuplicateAction for FilamentPHP

The DuplicateAction is a generic action that can be used across all your FilamentPHP resources to duplicate records. It handles unique field constraints, relationships, and provides a user-friendly interface.

## Features

- **Generic Implementation**: Can be applied to any Eloquent model resource
- **Unique Field Handling**: Automatically detects and handles unique fields to prevent constraint violations
- **Relationship Duplication**: Optionally duplicates related records (HasMany, BelongsToMany, HasOne, MorphOne)
- **Customizable**: Allows excluding fields, modifying field values, and customizing behavior
- **User Confirmation**: Includes a modal confirmation before duplication
- **Visual Feedback**: Uses appropriate icons and colors for clear UI

## Installation

The DuplicateAction is already set up and available to use. The action class is located at:

`app/Filament/Actions/DuplicateAction.php`

## Usage

### Basic Usage

To add the duplicate action to any table in your resource:

```php
// In your Resource/Tables/YourResourceTable.php file
use App\Filament\Actions\DuplicateAction;

// Add to the actions array
->actions([
    ViewAction::make(),
    EditAction::make(),
    DeleteAction::make(),
    DuplicateAction::make(),
])
```

### Advanced Usage

For more control over the action behavior, you can customize it:

```php
DuplicateAction::make()
    ->name('duplicate')          // Custom action name
    ->label('Duplicate Item')    // Custom label 
    ->successMessage('Item duplicated successfully!') // Custom success message
    ->except(['id', 'created_at', 'updated_at']) // Fields to exclude from duplication
    ->modify([
        'name' => fn ($value) => $value . ' (Copy)', // Modify field value during duplication
        'status' => 'draft', // Set a specific value for a field
    ])
    ->withRelationships(false) // Set to false to not duplicate relationships
```

### Adding to Header Actions

To add the duplicate action to the header of view pages:

```php
// In your Resource/Pages/ViewYourResource.php file
use App\Filament\Actions\DuplicateAction;

protected function getHeaderActions(): array
{
    return [
        EditAction::make(),
        DuplicateAction::make(), // Add this line
    ];
}
```

### Adding to Resource Files Directly

If you prefer to define the table directly in your resource file:

```php
// In your Resource/YourResource.php file
use App\Filament\Actions\DuplicateAction;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            // your columns
        ])
        ->actions([
            ViewAction::make(),
            EditAction::make(),
            DeleteAction::make(),
            DuplicateAction::make(),
        ]);
}
```

## How It Works

1. **Record Duplication**: The action creates a copy of the record using Laravel's `replicate()` method
2. **Primary Key Removal**: Automatically removes the primary key from the duplicated record
3. **Timestamp Handling**: Removes created_at and updated_at timestamps to let Laravel handle them
4. **Unique Field Management**: Detects potential unique field conflicts and appends "(Copy)" or numbered suffixes
5. **Relationship Duplication**: Optionally duplicates relationships depending on the model's relationships

## Customizing Unique Field Detection

By default, the action attempts to detect common unique fields (name, title, slug, email). If your model has other unique fields, you can define a method in your model:

```php
// In your model file
public function getDuplicateUniqueFields(): array
{
    return ['custom_unique_field'];
}
```

## Handling Specific Use Cases

### Excluding Sensitive Fields

To exclude sensitive fields from duplication:

```php
DuplicateAction::make()
    ->except(['api_token', 'password', 'secret_key'])
```

### Modifying Field Values

To change field values during duplication:

```php
DuplicateAction::make()
    ->modify([
        'status' => 'draft', // Set status to 'draft' for duplicated record
        'name' => fn ($currentValue) => $currentValue . ' (Copy)', // Append to name
        'published_at' => null, // Set published date to null
    ])
```

### Skipping Relationships

To prevent duplication of relationships:

```php
DuplicateAction::make()
    ->withRelationships(false)
```

## Supported Relationships

The action handles these relationship types:
- **BelongsToMany** (duplicates the relationships)
- **HasMany** (duplicates related records)
- **HasOne** (duplicates the related record)
- **MorphMany** (duplicates related records)
- **MorphOne** (duplicates the related record)

## Best Practices

1. **Test Thoroughly**: Always test the duplication functionality with your specific models
2. **Consider Unique Constraints**: Ensure your unique fields are properly handled
3. **Performance**: Be mindful when duplicating records with many relationships
4. **Permissions**: Consider adding visibility conditions based on user permissions

That's it! The DuplicateAction is now ready to use across all your Filament resources.