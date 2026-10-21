<?php

namespace App\Filament\Resources\Attributes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
class AttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('store_id')
                    ->relationship('store', 'name')
                    ->required(),
                TextInput::make('code')
                    ->required()
                    ->maxLength(64)
                    ->regex('/^[a-z0-9_]+$/')
                    ->helperText('Lowercase letters, numbers, and underscores only.'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
