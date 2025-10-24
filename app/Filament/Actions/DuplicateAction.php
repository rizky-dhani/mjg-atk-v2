<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Filament\Support\Icons\Heroicon;

class DuplicateAction
{
    public static function make(
        string $name = 'duplicate',
        string $label = 'Duplicate',
        string $successMessage = 'Record duplicated successfully.',
        array $except = [], // Fields to exclude from duplication
        array $modify = [], // Fields to modify during duplication
        bool $withRelationships = true // Whether to duplicate relationships
    ): Action {
        return Action::make($name)
            ->label($label)
            ->color('warning')
            ->icon(fn () => Heroicon::DocumentDuplicate)
            ->requiresConfirmation()
            ->modalHeading(function (Model $record) use ($label) {
                return "$label {$record->getKey()}?";
            })
            ->modalDescription(function (Model $record) use ($label) {
                $modelName = class_basename($record);
                return "Are you sure you want to duplicate this {$modelName}?";
            })
            ->modalSubmitActionLabel($label)
            ->action(function (Model $record) use ($successMessage, $except, $modify, $withRelationships) {
                // Get the model attributes to duplicate
                $attributes = $record->getAttributes();
                
                // Remove primary key and timestamps if they exist
                $primaryKey = $record->getKeyName();
                unset($attributes[$primaryKey]);
                unset($attributes['created_at']);
                unset($attributes['updated_at']);
                
                // Remove excluded fields
                foreach ($except as $field) {
                    unset($attributes[$field]);
                }
                
                // Apply modifications to specific fields
                foreach ($modify as $field => $value) {
                    if (is_callable($value)) {
                        $attributes[$field] = $value($record->$field, $record);
                    } else {
                        $attributes[$field] = $value;
                    }
                }
                
                // Handle unique field constraints by appending '(Copy)' or a number
                $uniqueFields = static::getUniqueFields($record);
                foreach ($uniqueFields as $field) {
                    if (isset($attributes[$field])) {
                        $originalValue = $attributes[$field];
                        $counter = 1;
                        do {
                            $suffix = $counter === 1 ? ' (Copy)' : " (Copy $counter)";
                            $attributes[$field] = $originalValue . $suffix;
                            $counter++;
                        } while (
                            static::isFieldValueUnique($record, $field, $attributes[$field]) === false
                        );
                    }
                }
                
                // Create the new model instance
                $newInstance = $record->replicate($attributes);
                
                // Save the new instance
                $newInstance->save();
                
                // Duplicate relationships if requested
                if ($withRelationships) {
                    static::duplicateRelationships($record, $newInstance);
                }
                
                // Dispatch an event for the duplication
                $record->fireModelEvent('duplicated', false, [$newInstance]);
                
                return $successMessage;
            });
    }
    
    /**
     * Get unique fields for the model based on unique indexes or by convention
     */
    protected static function getUniqueFields(Model $model): array
    {
        // This method attempts to determine unique fields
        // In Laravel, there's no built-in method to get unique columns from schema
        // So we'll use a common convention and allow for customization
        
        $uniqueFields = [];
        
        // Common fields that are often unique
        $possibleUniqueFields = ['name', 'title', 'slug', 'email'];
        
        foreach ($possibleUniqueFields as $field) {
            if (array_key_exists($field, $model->getAttributes())) {
                $uniqueFields[] = $field;
            }
        }
        
        // Allow models to specify unique fields by implementing a method
        if (method_exists($model, 'getDuplicateUniqueFields')) {
            $uniqueFields = array_merge($uniqueFields, $model->getDuplicateUniqueFields());
        }
        
        // Remove duplicates and return
        return array_unique($uniqueFields);
    }
    
    /**
     * Check if a field value is unique in the database
     */
    protected static function isFieldValueUnique(Model $model, string $field, mixed $value): bool
    {
        return !$model->newQuery()->where($field, $value)->exists();
    }
    
    /**
     * Duplicate model relationships when applicable
     */
    protected static function duplicateRelationships(Model $original, Model $duplicate): void
    {
        // Get all relationships defined on the model
        $methods = get_class_methods($original);
        
        foreach ($methods as $method) {
            // Skip if it's not a relation method
            if (!static::isRelationMethod($original, $method)) {
                continue;
            }
            
            // Get the relation
            try {
                $relation = $original->$method();
                
                // Handle different types of relations
                if (method_exists($relation, 'getRelated')) {
                    $relatedModel = $relation->getRelated();
                    
                    // Only duplicate belongsToMany and hasMany relationships
                    if (str_contains(get_class($relation), 'BelongsToMany')) {
                        // For many-to-many relationships
                        $relatedIds = $relation->pluck($relatedModel->getKeyName())->toArray();
                        if (!empty($relatedIds)) {
                            $duplicateRelation = $duplicate->$method();
                            if ($duplicateRelation) {
                                $duplicateRelation->attach($relatedIds);
                            }
                        }
                    } elseif (str_contains(get_class($relation), 'HasMany') || 
                             str_contains(get_class($relation), 'MorphMany')) {
                        // For one-to-many relationships, duplicate each related record
                        $relatedRecords = $relation->get();
                        foreach ($relatedRecords as $relatedRecord) {
                            // Replicate the related record with the new foreign key
                            $newRelated = $relatedRecord->replicate();
                            
                            // Get fresh relation instance to access foreign key name
                            $freshRelation = $duplicate->$method();
                            if ($freshRelation) {
                                $newRelated->{$freshRelation->getForeignKeyName()} = $duplicate->getKey();
                            }
                            
                            // Save the duplicated related record
                            $newRelated->save();
                            
                            // Also duplicate nested relationships if any
                            static::duplicateRelationships($relatedRecord, $newRelated);
                        }
                    } elseif (str_contains(get_class($relation), 'HasOne') || 
                             str_contains(get_class($relation), 'MorphOne')) {
                        // For one-to-one relationships, duplicate the related record
                        $relatedRecord = $relation->first();
                        if ($relatedRecord) {
                            $newRelated = $relatedRecord->replicate();
                            
                            // Get fresh relation instance to access foreign key name
                            $freshRelation = $duplicate->$method();
                            if ($freshRelation) {
                                $newRelated->{$freshRelation->getForeignKeyName()} = $duplicate->getKey();
                            }
                            
                            // Save the duplicated related record
                            $newRelated->save();
                            
                            // Also duplicate nested relationships if any
                            static::duplicateRelationships($relatedRecord, $newRelated);
                        }
                    }
                }
            } catch (\Exception $e) {
                // Skip relationships that cause errors during duplication
                continue;
            }
        }
    }
    
    /**
     * Determine if a method is a relationship method
     */
    protected static function isRelationMethod(Model $model, string $method): bool
    {
        try {
            $reflection = new \ReflectionMethod($model, $method);
            
            // Method must be public
            if (!$reflection->isPublic()) {
                return false;
            }
            
            // Relationships in Eloquent have no required parameters
            // (They may have optional parameters, but no required ones)
            if ($reflection->getNumberOfRequiredParameters() > 0) {
                return false;
            }
            
            // Try calling the method to see if it returns a relation
            $relation = $model->$method();
            
            // Check if the return value is an object and if it's an instance of a relation class
            return $relation !== null && is_object($relation) && str_contains(get_class($relation), 'Relation');
            
        } catch (\Exception $e) {
            // If calling the method results in an exception,
            // it's not a relationship method
            return false;
        }
    }
}