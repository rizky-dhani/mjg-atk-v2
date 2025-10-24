<?php

namespace App\Filament\Resources\AtkCategories\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class AtkCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make("name")->required(),
            Textarea::make("description")->default(null)->columnSpanFull(),
        ]);
    }
}