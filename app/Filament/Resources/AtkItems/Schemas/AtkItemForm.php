<?php

namespace App\Filament\Resources\AtkItems\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class AtkItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make("name")->required(),
            Textarea::make("description")->default(null)->columnSpanFull(),
            TextInput::make("category_id")->required()->numeric(),
            TextInput::make("unit")->required(),
        ]);
    }
}